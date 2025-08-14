<?php

namespace App\Http\Controllers\Bank;

use App\Exceptions\ApiGenericException;
use App\Exports\bank\StatementExport;
use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Imports\BankReconciliation\BankStatementImport;
use App\Models\BankDetail;
use App\Models\BankReconciliation\BankStatement;
use App\Models\BankReconciliation\FailedBankStatement;
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
        $bank = BankDetail::findOrFail($id);
        if($request->has('batch_uid')){
            $batchUid = $request->get('batch_uid');
    
            $successCount = BankStatement::where('uid', $batchUid)->count();
            $failureCount = FailedBankStatement::where('uid', $batchUid)->count();
            $statements = BankStatement::where('uid', $batchUid)->paginate(10);
            
            if ($request->get('type') === 'failed-statement') {
                $statements = FailedBankStatement::where('uid', $batchUid)->paginate(10);
            }

            return view('bank-reconciliation.statement.view',[
                    'statements' => $statements,
                    'bank' => $bank,
                    'failureCount' => $failureCount,
                    'successCount' => $successCount,
            ]);
        }

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
            $data['batchId'] = $import->getBatchId();
            // $data['failures'] = array_values($import->getFailures());

            return [
                "data" => $data,
                "message" => "Your Statement has been uploaded successfully."
            ];
        } catch (\Exception $e) {
            // dd($e->getMessage());
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

        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $startDate = Carbon::parse($dates[0])->format('Y-m-d');
            $endDate = Carbon::parse($dates[1])->format('Y-m-d');
        }

        $bank = BankDetail::with([
            'ledger' => function($q){
                $q->select('id','name');
            },
            'bankInfo' => function($q){
                $q->select('id','bank_name');
            }
        ])->find($id);
        
        $partyItemSubquery = $this->partyItemSubquery($startDate, $endDate, $organizationId);

        $vouchers = Voucher::join('erp_item_details','erp_item_details.voucher_id','=','erp_vouchers.id')
                    ->join('erp_books','erp_books.id','=','erp_vouchers.book_id')
                    ->leftJoin('erp_bank_statements','erp_bank_statements.uid','=','erp_item_details.statement_uid')
                    ->leftJoinSub($partyItemSubquery, 'party_details', function($join) {
                        $join->on('erp_vouchers.id', '=', 'party_details.voucher_id');
                    })
                    ->whereNotNull('erp_item_details.statement_uid')
                    ->where('erp_vouchers.organization_id', $organizationId)
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
                        'party_details.name as party_name',
                    )
                    ->paginate($length);
        $dateRange = \Carbon\Carbon::parse($startDate)->format('d-m-Y') . " to " . \Carbon\Carbon::parse($endDate)->format('d-m-Y');
        return view('bank-reconciliation.statement.match-entries',[
            'bank' => $bank,
            'dateRange' => $dateRange,
            'vouchers' => $vouchers,
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

        $partyItemSubquery = $this->partyItemSubquery($startDate, $endDate, $organizationId);

        $vouchers = Voucher::join('erp_item_details','erp_item_details.voucher_id','=','erp_vouchers.id')
                    ->join('erp_books','erp_books.id','=','erp_vouchers.book_id')
                    ->leftJoinSub($partyItemSubquery, 'party_details', function($join) {
                        $join->on('erp_vouchers.id', '=', 'party_details.voucher_id');
                    })
                    ->whereNull('erp_item_details.statement_uid')
                    // ->whereNull('erp_item_details.bank_date')
                    ->where('erp_vouchers.organization_id', $organizationId)
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
                        'party_details.name as party_name',
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

    private function voucherFilter($query,$search){
        $query->where(function($q) use ($search) {
            $q->where('voucher_no', 'like', '%' . $search . '%')
                ->orWhere('voucher_name', 'like', '%' . $search . '%');
        });
        return $query;
    }

    private function partyItemSubquery($startDate, $endDate, $organizationId){
        // dd($startDate, $endDate, $organizationId);
        $partyItemSubquery = ItemDetail::select(
                'erp_item_details.ledger_id',
                'erp_item_details.voucher_id',
                'erp_ledgers.name',
            )
            ->join('erp_ledgers','erp_ledgers.id','=','erp_item_details.ledger_id')
            ->join('erp_vouchers','erp_vouchers.id','=','erp_item_details.voucher_id')
            ->where('erp_item_details.entry_type', 'party')
            ->whereBetween('erp_vouchers.document_date', [$startDate, $endDate])
            ->where('erp_vouchers.organization_id', $organizationId)
            ->groupBy('voucher_id');

            return $partyItemSubquery;
    }

    public function export(Request $request, $id){
        if(!$request->type){
            return new ApiGenericException('Type not found.');
        }

        if(!$request->batch_uid){
            return new ApiGenericException('Batch id not found.');
        }

        $account = BankDetail::find($id);
        $statementExport = new StatementExport();
        $fileName = "temp/statement/".$account->bankInfo->bank_name.date('Ymd').".csv";
        $statementExport->export($fileName, $request, $id);
        return redirect($fileName);

    }

}