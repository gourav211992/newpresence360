<?php

namespace App\Http\Controllers\Bank;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Imports\BankReconciliation\BankStatementImport;
use App\Models\BankDetail;
use App\Models\BankReconciliation\BankStatement;
use App\Models\ItemDetail;
use App\Models\Organization;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException as ValidationValidationException;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class StatementController extends Controller
{
    public function upload(Request $request, $id){
       return view('bank-reconciliation.statement.upload',[
        'id' => $id
       ]);
    }

    public function save(Request $request, $id){
        $validator = Validator::make($request->all(),[
                'bank_file' => 'required|file|mimes:csv',
            ],[
                'bank_file.required' => 'CSV file is required'
            
            ]);

        if ($validator->fails()) {
            throw new ValidationValidationException($validator);
        }

        try {
            $import = new BankStatementImport($id);  // Instantiate first
            Excel::import($import, $request->file('bank_file'));  // Then use it

            // After import, capture the successful and failed rows
            $data['successfulRows'] = $import->getSuccessfulRowsCount();
            $data['failedRows'] = $import->getFailedRowsCount();
            // $data['failures'] = $import->getFailures();
            $data['failures'] = array_values($import->getFailures());

            return [
                "data" => $data,
                "message" => "Your Statement has been uploaded successfully."
            ];
        } catch (\Exception $e) {
            throw new ApiGenericException('The system was unable to read the statement from the uploaded file. Please correct the file and upload again.');
        }
    }

    public function matchEntries(Request $request, $id){
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;

        $authUser = Helper::getAuthenticatedUser();
        $organizationId = $authUser->organization_id;

        // Default date range
        $fyear = Helper::getFinancialYear(date('Y-m-d'));
        $startDate = $fyear['start_date'];
        $endDate = $fyear['end_date'];
        // $openingStartDate = Carbon::parse($startDate)->subYear()->startOfYear()->format('Y-m-d');
        // $openingEndDate = Carbon::parse($startDate)->subYear()->endOfYear()->format('Y-m-d');

        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $startDate = Carbon::parse($dates[0])->format('Y-m-d');
            $endDate = Carbon::parse($dates[1])->format('Y-m-d');

            // $fiscalYearStartMonth = 4; // April
            // $startMonth = Carbon::parse($startDate)->month;
            // $startYear = Carbon::parse($startDate)->year;
            // if ($startMonth < $fiscalYearStartMonth) {
            //     $openingStartDate = Carbon::create($startYear - 1, 4, 1)->format('Y-m-d');
            // } else {
            //     $openingStartDate = Carbon::create($startYear, 4, 1)->format('Y-m-d');
            // }

            // $openingEndDate = Carbon::parse($startDate)->subDay()->format('Y-m-d');
        }

        $bank = BankDetail::with([
            'ledger' => function($q){
                $q->select('id','name');
            },
            'bankInfo' => function($q){
                $q->select('id','bank_name');
            }
        ])->find($id);

        // $totalDebit = BankStatement::where('account_id', $id)
        //     ->where('matched', 1)
        //     ->whereDate('date', '>=', $startDate)
        //     ->whereDate('date', '<=', $endDate)
        //     ->when($request->has('search') && $request->search != '', function($query) use ($request) {
        //         $search = $request->search;
        //         self::statementFilter($query,$search);
        //     })
        //     ->sum('debit_amt');

        // $totalCredit = BankStatement::where('account_id', $id)
        //     ->where('matched', 1)
        //     ->whereDate('date', '>=', $startDate)
        //     ->whereDate('date', '<=', $endDate)
        //     ->when($request->has('search') && $request->search != '', function($query) use ($request) {
        //         $search = $request->search;
        //         self::statementFilter($query,$search);
        //     })
        //     ->sum('credit_amt');

        // // Opening balance before startDate
        // $openingBalance = BankStatement::where('account_id', $id)
        //     ->where('matched', 1)
        //     ->whereBetween('date', [$openingStartDate, $openingEndDate])
        //     ->when($request->has('search') && $request->search != '', function($query) use ($request) {
        //         $search = $request->search;
        //         self::statementFilter($query,$search);
        //     })
        //     ->orderByDesc('date')
        //     ->value('balance'); // You can also calculate balance by summing manually if needed

        // // Fallback if null
        // $openingBalance = $openingBalance ?? 0;

        // // Closing = opening + (totalDebit - totalCredit)
        // $closingBalance = $openingBalance + ($totalDebit - $totalCredit);


        // $statements = BankStatement::select('id','debit_amt','balance','account_number','date','credit_amt','ref_no', 'narration')
        // ->where('account_id',$id)
        // ->where('matched',1)
        // ->whereDate('date', '>=', $startDate)
        // ->whereDate('date', '<=', $endDate)
        // ->when($request->has('search') && $request->search != '', function($query) use ($request) {
        //     $search = $request->search;
        //     self::statementFilter($query,$search);
        // })
        // ->paginate($length);

        $vouchers = Voucher::join('erp_item_details','erp_item_details.voucher_id','=','erp_vouchers.id')
                    ->join('erp_books','erp_books.id','=','erp_vouchers.book_id')
                    ->leftJoin('erp_bank_statements','erp_bank_statements.uid','=','erp_item_details.statement_uid')
                    ->where(function($query){
                        $query->whereNotNull('erp_item_details.statement_uid')
                        ->orWhereNotNull('erp_item_details.bank_date');
                    })
                    ->where('erp_item_details.organization_id', $organizationId)
                    ->whereBetween('erp_vouchers.document_date', [$startDate, $endDate])
                    ->whereIn('erp_vouchers.approvalStatus',['approved','approval_not_required'])
                    ->whereIn('erp_vouchers.reference_service',['receipts','payments'])
                    ->where('erp_item_details.ledger_parent_id',$bank->ledger_group_id)
                    ->where('erp_item_details.ledger_id', $bank->ledger_id)
                    ->when($request->has('search') && $request->search != '', function($query) use ($request) {
                        $search = $request->search;
                        self::voucherFilter($query,$search);
                    })
                    ->select(
                        'erp_vouchers.voucher_no',
                        'erp_vouchers.voucher_name',
                        'erp_vouchers.document_date',
                        'erp_item_details.credit_amt_org',
                        'erp_item_details.debit_amt_org',
                        'erp_books.book_code',
                        'erp_bank_statements.date',
                        'erp_bank_statements.account_number',
                        'erp_bank_statements.ref_no',
                    )
                    ->paginate($length);
        $dateRange = \Carbon\Carbon::parse($startDate)->format('d-m-Y') . " to " . \Carbon\Carbon::parse($endDate)->format('d-m-Y');
        return view('bank-reconciliation.statement.match-entries',[
            'bank' => $bank,
            'dateRange' => $dateRange,
            'vouchers' => $vouchers,
            // 'statements' => $statements,
            // 'totalDebit' => $totalDebit,
            // 'totalCredit' => $totalCredit,
            // 'openingBalance' => $openingBalance,
            // 'closingBalance' => $closingBalance,
        ]);
    }

    public function notMatchEntries(Request $request, $id){
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;

        $authUser = Helper::getAuthenticatedUser();
        $organizationId = $authUser->organization_id;

        // Default date range
        $fyear = Helper::getFinancialYear(date('Y-m-d'));
        $startDate = $fyear['start_date'];
        $endDate = $fyear['end_date'];

        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
        }

        $bank = BankDetail::with([
            'ledger' => function($q){
                $q->select('id','name');
            },
            'bankInfo' => function($q){
                $q->select('id','bank_name');
            }
        ])->find($id);

        $vouchers = Voucher::join('erp_item_details','erp_item_details.voucher_id','=','erp_vouchers.id')
                    ->join('erp_books','erp_books.id','=','erp_vouchers.book_id')
                    ->whereNull('erp_item_details.statement_uid')
                    ->whereNull('erp_item_details.bank_date')
                    ->where('erp_item_details.organization_id', $organizationId)
                    ->whereBetween('erp_vouchers.document_date', [$startDate, $endDate])
                    ->whereIn('erp_vouchers.approvalStatus',['approved','approval_not_required'])
                    ->whereIn('erp_vouchers.reference_service',['receipts','payments'])
                    ->where('erp_item_details.ledger_parent_id',$bank->ledger_group_id)
                    ->where('erp_item_details.ledger_id', $bank->ledger_id)
                    ->when($request->has('search') && $request->search != '', function($query) use ($request) {
                        $search = $request->search;
                        self::voucherFilter($query,$search);
                    })
                    ->select(
                        'erp_vouchers.voucher_no',
                        'erp_vouchers.voucher_name',
                        'erp_vouchers.document_date',
                        'erp_item_details.credit_amt_org',
                        'erp_item_details.debit_amt_org',
                        'erp_books.book_code'
                    )
                    ->paginate($length);
                    // dd($vouchers->toArray(),$startDate, $endDate);

        $dateRange = \Carbon\Carbon::parse($startDate)->format('d-m-Y') . " to " . \Carbon\Carbon::parse($endDate)->format('d-m-Y');
        return view('bank-reconciliation.statement.not-match-entries',[
            'bank' => $bank,
            'vouchers' => $vouchers,
            'dateRange' => $dateRange
        ]);
    }

    // private function statementFilter($query,$search){
    //     $query->where(function($q) use ($search) {
    //         $q->where('account_number', 'like', '%' . $search . '%')
    //             ->orWhere('narration', 'like', '%' . $search . '%')
    //             ->orWhere('ref_no', 'like', '%' . $search . '%');
    //     });
    //     return $query;
    // }

    private function voucherFilter($query,$search){
        $query->where(function($q) use ($search) {
            $q->where('voucher_no', 'like', '%' . $search . '%')
                ->orWhere('voucher_name', 'like', '%' . $search . '%');
        });
        return $query;
    }

}