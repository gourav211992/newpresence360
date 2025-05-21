<?php

namespace App\Imports\BankReconciliation;

use App\Helpers\CommonHelper;
use App\Models\BankDetail;
use App\Models\BankReconciliation\BankStatement;
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
    protected $successfulRows = 0;
    protected $failedRows = 0;
    protected $failures = [];

    public function __construct($id)
    {
        $this->bank = BankDetail::findOrFail($id);
        $this->ledger = Ledger::find($this->bank->ledger_id);
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $date = \Carbon\Carbon::createFromFormat('d-m-Y', $row['date']);

        // Duplicate check
        $duplicate = BankStatement::where('ref_no', $row['chqref_no'])
            ->where('ledger_id', $this->bank->ledger_id)
            ->whereDate('date', $date)
            ->where('account_number', $this->bank->account_number)
            ->exists();

        if ($duplicate) {
            $this->failedRows++;
            $this->failures[] = [
                'chqref_no' => $row['chqref_no'],
                'errors' => ["The Ref No: {$row['chqref_no']} already exists for the selected account."],
            ];
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
        $statement->uid = (string) Str::uuid();
        $statement->narration =  $row['narration'];
        $statement->date = $row['date'] ? \Carbon\Carbon::createFromFormat('d-m-Y', $row['date']) : NULL;
        $statement->ref_no = $row['chqref_no'];
        $statement->debit_amt = $row['debit_amount'];
        $statement->credit_amt = $row['credit_amount'];
        $statement->balance = $row['balance'];
        $statement->save();
        $this->attemptMatch($statement);

        return $statement;
    }

    protected function attemptMatch(BankStatement $statement)
    {
        // $match = PaymentVoucher::where('bank_id', $statement->bank_id)
        //     ->where('ledger_id', $statement->ledger_id)
        //     ->where('account_id', $statement->account_id)
        //     ->where('accountNo', $statement->account_number)
        //     ->where('ledger_group_id', $statement->ledger_group_id)
        //     ->where('group_id', $statement->group_id)
        //     ->where('company_id', $statement->company_id)
        //     ->where('organization_id', $statement->organization_id)
        //     ->where('reference_no', $statement->ref_no)
        //     ->whereDate('document_date', $statement->date)
        //     ->where(function ($query) use ($statement) {
        //         $query->where(function ($q) use ($statement) {
        //             $q->where('document_type', CommonHelper::PAYMENTS)
        //             ->where('amount', $statement->credit_amt);
        //         })->orWhere(function ($q) use ($statement) {
        //             $q->where('document_type', CommonHelper::RECEIPTS)
        //             ->where('amount', $statement->debit_amt);
        //         });
        //     })
        //     ->first();

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
            // dd($match,$statement->toArray());
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
            $row = $failure->row();

            // Avoid double-counting the same row
            if (!isset($this->failures[$row])) {
                $this->failedRows++; // Count each row only once
                $this->failures[$row] = [
                    'row' => $row,
                    'errors' => [],
                ];
            }

            $this->failures[$row]['errors'] = array_merge(
                $this->failures[$row]['errors'],
                $failure->errors()
            );
        }
    }

    // Count of successful rows
    public function getSuccessfulRowsCount()
    {
        return $this->successfulRows;
    }

    // Count of failed rows
    public function getFailedRowsCount()
    {
        return $this->failedRows;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:d-m-Y'],
            'narration' => ['required'],
            'debit_amount' => ['required', 'numeric'],
            'credit_amount' => ['required', 'numeric'],
            'balance' => ['required', 'numeric'],
            'chqref_no' => ['required'],
        ];
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
