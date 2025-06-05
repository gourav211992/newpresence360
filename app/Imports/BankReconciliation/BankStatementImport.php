<?php

namespace App\Imports\BankReconciliation;

use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Models\BankDetail;
use App\Models\BankReconciliation\BankStatement;
use App\Models\BankReconciliation\FailedBankStatement;
use App\Models\ItemDetail;
use App\Models\Ledger;
use App\Models\PaymentVoucher;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Str;

class BankStatementImport implements ToModel, WithValidation, WithHeadingRow, SkipsEmptyRows, SkipsOnFailure
{
    use Importable;
    protected $bank;
    protected $ledger;
    protected $user;
    protected $batchUid;
    protected $successfulRows = 0;
    protected $failedRows = 0;
    protected $failures = [];

    public function __construct($id)
    {
        $this->bank = BankDetail::findOrFail($id);
        $this->ledger = Ledger::find($this->bank->ledger_id);
        $this->user = Helper::getAuthenticatedUser();;
        $this->batchUid = (string) Str::uuid();
        FailedBankStatement::where('created_by', $this->user->id)
        ->where('created_by_type', $this->user->authenticable_type)
        ->delete();
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $date = $this->isValidDateFormat($row['date']);
        if (!$date) {
            $this->failedRows++;
            $this->failures[] = [
                'data' => $row,
                'errors' => "Invalid date format: {$row['date']}"
            ];
            $this->addFailedStatement("Invalid date format: {$row['date']}", $row);
            return null;
        }

        // Duplicate check
        $duplicate = BankStatement::where('ref_no', $row['chqref_no'])
            ->where('ledger_id', $this->bank->ledger_id)
            ->whereDate('date', $date)
            ->where('account_number', $this->bank->account_number)
            ->exists();

        if ($duplicate) {
            $this->failedRows++;
            $this->failures[] = [
                'data' => $row,
                'errors' => "The Ref No: {$row['chqref_no']} already exists for the selected account."
            ];


            $errors = "The Ref No: {$row['chqref_no']} already exists for the selected account.";
            $this->addFailedStatement($errors,$row);
            return null;
        }

        // Check if the row is valid. If valid, we count it as a successful row
        $this->successfulRows++;
        
        $statement = new BankStatement();
        $statement->group_id = $this->ledger->group_id;
        $statement->company_id = $this->ledger->company_id;
        $statement->organization_id = $this->ledger->organization_id;
        $statement->ledger_id = $this->bank->ledger_id;
        $statement->ledger_group_id = $this->bank->ledger_group_id;
        $statement->bank_id = $this->bank->bank_id;
        $statement->account_id = $this->bank->id;
        $statement->account_number = $this->bank->account_number;
        $statement->uid = $this->batchUid;
        $statement->narration =  $row['narration'];
        $statement->date = $date;
        $statement->ref_no = $row['chqref_no'];
        $statement->debit_amt = $row['debit_amount'];
        $statement->credit_amt = $row['credit_amount'];
        $statement->balance = $row['balance'];
        $statement->created_by = $this->user->id;
        $statement->created_by_type = $this->user->authenticable_type;
        $statement->save();
        $this->attemptMatch($statement);

        return $statement;
    }

    protected function attemptMatch(BankStatement $statement)
    {
        $match = ItemDetail::join('erp_vouchers','erp_vouchers.id','=','erp_item_details.voucher_id')
                ->join('erp_payment_vouchers','erp_payment_vouchers.id','=','erp_vouchers.reference_doc_id')
                ->where('erp_payment_vouchers.bank_id', $statement->bank_id)
                ->where('erp_payment_vouchers.account_id', $statement->account_id)
                ->where('erp_payment_vouchers.accountNo', $statement->account_number)
                ->where('erp_payment_vouchers.reference_no', $statement->ref_no)
                ->where('erp_item_details.ledger_id', $statement->ledger_id)
                ->where('erp_item_details.ledger_parent_id', $statement->ledger_group_id)
                ->where('erp_vouchers.group_id', $statement->group_id)
                ->where('erp_vouchers.company_id', $statement->company_id)
                ->where('erp_vouchers.organization_id', $statement->organization_id)
                ->where('erp_item_details.debit_amt_org', $statement->credit_amt)
                ->where('erp_item_details.credit_amt_org', $statement->debit_amt)
                ->whereDate('erp_vouchers.document_date', $statement->date)
                ->select('erp_item_details.id')
                ->first();

        if ($match) {
            $statement->matched = true;
            $match->statement_uid = $statement->uid;
            $statement->save();
            $match->save();
        }
    }

    // On failure callback
    public function onFailure(...$failures)
    {
        foreach ($failures as $failure) {
            $rowData = $failure->values();

            $this->failedRows++;

            $this->failures[] = [
                'data' => $rowData,
                'errors' => implode(', ', $failure->errors())
            ];

            $errors = implode(', ', $failure->errors());
            $this->addFailedStatement($errors,$rowData);
        }
    }

    protected function addFailedStatement($errors,$rowData)
    {
        $date = $this->isValidDateFormat($rowData['date']);
       
        FailedBankStatement::create([
                'group_id' => $this->ledger->group_id,
                'company_id' => $this->ledger->company_id,
                'organization_id' => $this->ledger->organization_id,
                'ledger_id' => $this->bank->ledger_id,
                'ledger_group_id' => $this->bank->ledger_group_id,
                'bank_id' => $this->bank->bank_id,
                'account_id' => $this->bank->id,
                'account_number' => $this->bank->account_number ?? null,
                'ref_no' => $rowData['chqref_no'] ?? null,
                'narration' => $rowData['narration'] ?? null,
                'date' => $date ? $date : null,
                'debit_amount' => $rowData['debit_amount'] ?? 0,
                'credit_amount' => $rowData['credit_amount'] ?? 0,
                'balance' => $rowData['balance'] ?? 0,
                'uid' => $this->batchUid,
                'errors' => $errors,
                'created_by' => $this->user->id,
                'created_by_type' => $this->user->authenticable_type
            ]);
    }

    // Count of successful rows
    public function getSuccessfulRowsCount()
    {
        return $this->successfulRows;
    }

    public function getBatchId()
    {
        return $this->batchUid;
    }

    // Count of failed rows
    public function getFailedRowsCount()
    {
        return $this->failedRows;
    }

    public function rules(): array
    {
        return [
            'date' => ['required'],
            'narration' => ['required'],
            'debit_amount' => ['required', 'numeric'],
            'credit_amount' => ['required', 'numeric'],
            'balance' => ['required', 'numeric'],
            'chqref_no' => ['required'],
        ];
    }

    protected function isValidDateFormat($value)
    {
        $formats = ['d-m-Y', 'd/m/Y', 'Y-m-d'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            $errors = \DateTime::getLastErrors();
            if ($date && $errors['warning_count'] === 0 && $errors['error_count'] === 0 && $date->format($format) === $value) {
                // Always return Carbon date in Y-m-d
                return \Carbon\Carbon::createFromFormat($format, $value)->format('Y-m-d');
            }
        }
        return false;
    }

    public function customValidationMessages()
    {
        return [
            'date.required' => 'Date is required.',
            'date.date_format' => 'Date must be in DD-MM-YYYY format.',
            'narration.required' => 'Narration cannot be empty.',
            'debit_amount.required' => 'Debit amount is required.',
            'debit_amount.numeric' => 'Debit amount must be numeric.',
            'credit_amount.required' => 'Credit amount is required.',
            'credit_amount.numeric' => 'Credit amount must be numeric.',
            'balance.required' => 'Balance is required.',
            'balance.numeric' => 'Balance must be numeric.',
            'chqref_no.required' => 'Chq/Ref No must be numeric.',
        ];
    }

    /**
     * Get the validation failures
     * 
     * @return array
     */
    public function getFailures()
    {
        return $this->failures;
    }
}
