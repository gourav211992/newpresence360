<?php

namespace App\Http\Controllers;

use App\Exports\CashflowExport;
use App\Helpers\ConstantHelper;
use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\CashflowScheduler;
use App\Helpers\Helper;
use App\Models\PaymentVoucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use App\Models\Organization;
use App\Models\Address;
use PDF;
use App\Models\Currency;
use App\Models\AuthUser;
use Maatwebsite\Excel\Facades\Excel;

class CashflowReportController extends Controller
{
    public function index(Request $request,$page=null)
    {
        $fy = Helper::getFinancialYear(date('Y-m-d'));
        $startDate = date('Y-m-d', strtotime($fy['start_date']));
        $endDate = date('Y-m-d', strtotime($fy['end_date']));

        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
        }
        if ($request->organization)
            $organization_id = $request->organization;
        else
            $organization_id = Helper::getAuthenticatedUser()->organization_id;


        $payment_made = Voucher::where('reference_service', ConstantHelper::PAYMENTS_SERVICE_ALIAS)
            ->where('organization_id', $organization_id)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->whereBetween('document_date', [$startDate, $endDate])
            ->with('items.ledger') // assuming each item has a ledger relation
            ->get()
            ->flatMap(function ($voucher) {

                return $voucher->items->where('debit_amt_org', '>', 0)->map(function ($item) use ($voucher) {
                    $pay = PaymentVoucher::find($voucher->reference_doc_id);
                    return (object)[
                        'voucher_id'    => $voucher->id,
                        'voucher_no' => $voucher->voucher_no,
                        'document_date' => $voucher->document_date,
                        'amount' => $item->debit_amt_org,
                        'ledger_name'   => optional($item->ledger)->name,
                        'payment_mode' => $pay?->payment_type,
                        'bank_name' => $pay?->bank?->bank_name != "" ? $pay?->bank?->bank_name : "-"
                    ];
                });
            })->values()->all();


        $payment_made_t = Voucher::where('reference_service', ConstantHelper::PAYMENTS_SERVICE_ALIAS)
            ->where('organization_id', $organization_id)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->whereBetween('document_date', [$startDate, $endDate])
            ->with('items.ledger') // assuming each item has a ledger relation
            ->get()
            ->flatMap(function ($voucher) {

                return $voucher->items->where('debit_amt_org', '>', 0);
            })->sum('debit_amt_org');


        $opening_payment_made =  Voucher::where('reference_service', ConstantHelper::PAYMENTS_SERVICE_ALIAS)
            ->where('organization_id', $organization_id)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->where('document_date', '<', $startDate)
            ->with('items') // we just need items, ledger is not needed for sum
            ->get()
            ->flatMap(function ($voucher) {
                return $voucher->items->where('debit_amt_org', '>', 0);
            })
            ->sum('debit_amt_org');



        $payment_received = Voucher::where('reference_service', ConstantHelper::RECEIPTS_SERVICE_ALIAS)
            ->where('organization_id', $organization_id)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->whereBetween('document_date', [$startDate, $endDate])
            ->with('items.ledger') // assuming each item has a ledger relation
            ->get()
            ->flatMap(function ($voucher) {
                return $voucher->items->where('credit_amt_org', '>', 0)->map(function ($item) use ($voucher) {
                    $pay = PaymentVoucher::find($voucher->reference_doc_id);
                    return (object) [
                        'voucher_id'    => $voucher->id,
                        'voucher_no' => $voucher->voucher_no,
                        'document_date' => $voucher->document_date,
                        'amount' => $item->credit_amt_org,
                        'ledger_name'   => optional($item->ledger)->name,
                        'payment_mode' => $pay?->payment_type,
                        'bank_name' => $pay?->bank?->bank_name != "" ? $pay?->bank?->bank_name : "-"
                    ];
                });
            })->values()->all();

        $payment_received_t = Voucher::where('reference_service', ConstantHelper::RECEIPTS_SERVICE_ALIAS)
            ->where('organization_id', $organization_id)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->whereBetween('document_date', [$startDate, $endDate])
            ->with('items.ledger') // assuming each item has a ledger relation
            ->get()
            ->flatMap(function ($voucher) {

                return $voucher->items->where('credit_amt_org', '>', 0);
            })->sum('credit_amt_org');

        $opening_payment_received =  Voucher::where('reference_service', ConstantHelper::RECEIPTS_SERVICE_ALIAS)
            ->where('organization_id', $organization_id)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->where('document_date', '<', $startDate)
            ->with('items') // we just need items, ledger is not needed for sum
            ->get()
            ->flatMap(function ($voucher) {
                return $voucher->items->where('credit_amt_org', '>', 0);
            })
            ->sum('credit_amt_org');
        $opening = $opening_payment_received - $opening_payment_made;
        $closing = ($opening + $payment_received_t) - $payment_made_t;
        $fy = self::formatWithOrdinal($startDate) . ' to ' . self::formatWithOrdinal($endDate);
        $scheduler = CashflowScheduler::where('organization_id',$organization_id)->latest()->first();
        $users =  Helper::getOrgWiseUserAndEmployees($organization_id);
        if($page==="print"){
        $createdBy= Helper::getAuthenticatedUser()->auth_user_id;
        $fileName = 'Cashflow Statment' . date('Y-m-d') . '.pdf';
        return self::print($startDate,$endDate,$organization_id,$createdBy)->stream($fileName);
        
        }

        else{
            $mappings = Helper::getAuthenticatedUser()->access_rights_org;
        
            $startDate = date('d-m-Y', strtotime($startDate));
        $endDate = date('d-m-Y', strtotime($endDate));
        $range = $startDate . ' to ' . $endDate;
        return view('cashflow.index', compact('scheduler','users','opening', 'payment_received', 'payment_made', 'payment_made_t', 'payment_received_t', 'closing', 'fy', 'mappings', 'organization_id', 'range'));
        }
    }
    public static function print($startDate,$endDate,$organization_id,$createdBy)
    {
        $payment_made = Voucher::where('reference_service', ConstantHelper::PAYMENTS_SERVICE_ALIAS)
            ->where('organization_id', $organization_id)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->whereBetween('document_date', [$startDate, $endDate])
            ->with('items.ledger') // assuming each item has a ledger relation
            ->get()
            ->flatMap(function ($voucher) {

                return $voucher->items->where('debit_amt_org', '>', 0)->map(function ($item) use ($voucher) {
                    $pay = PaymentVoucher::find($voucher->reference_doc_id);
                    return (object)[
                        'voucher_id'    => $voucher->id,
                        'voucher_no' => $voucher->voucher_no,
                        'document_date' => $voucher->document_date,
                        'amount' => $item->debit_amt_org,
                        'ledger_name'   => optional($item->ledger)->name,
                        'payment_mode' => $pay?->payment_type,
                        'bank_name' => $pay?->bank?->bank_name != "" ? $pay?->bank?->bank_name : "-"
                    ];
                });
            })->values()->all();
    
        $payment_made_t = Voucher::where('reference_service', ConstantHelper::PAYMENTS_SERVICE_ALIAS)
            ->where('organization_id', $organization_id)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->whereBetween('document_date', [$startDate, $endDate])
            ->with('items.ledger') // assuming each item has a ledger relation
            ->get()
            ->flatMap(function ($voucher) {

                return $voucher->items->where('debit_amt_org', '>', 0);
            })->sum('debit_amt_org');


        $opening_payment_made =  Voucher::where('reference_service', ConstantHelper::PAYMENTS_SERVICE_ALIAS)
            ->where('organization_id', $organization_id)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->where('document_date', '<', $startDate)
            ->with('items') // we just need items, ledger is not needed for sum
            ->get()
            ->flatMap(function ($voucher) {
                return $voucher->items->where('debit_amt_org', '>', 0);
            })
            ->sum('debit_amt_org');



        $payment_received = Voucher::where('reference_service', ConstantHelper::RECEIPTS_SERVICE_ALIAS)
            ->where('organization_id', $organization_id)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->whereBetween('document_date', [$startDate, $endDate])
            ->with('items.ledger') // assuming each item has a ledger relation
            ->get()
            ->flatMap(function ($voucher) {
                return $voucher->items->where('credit_amt_org', '>', 0)->map(function ($item) use ($voucher) {
                    $pay = PaymentVoucher::find($voucher->reference_doc_id);
                    return (object) [
                        'voucher_id'    => $voucher->id,
                        'voucher_no' => $voucher->voucher_no,
                        'document_date' => $voucher->document_date,
                        'amount' => $item->credit_amt_org,
                        'ledger_name'   => optional($item->ledger)->name,
                        'payment_mode' => $pay?->payment_type,
                        'bank_name' => $pay?->bank?->bank_name != "" ? $pay?->bank?->bank_name : "-"
                    ];
                });
            })->values()->all();

        $payment_received_t = Voucher::where('reference_service', ConstantHelper::RECEIPTS_SERVICE_ALIAS)
            ->where('organization_id', $organization_id)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->whereBetween('document_date', [$startDate, $endDate])
            ->with('items.ledger') // assuming each item has a ledger relation
            ->get()
            ->flatMap(function ($voucher) {

                return $voucher->items->where('credit_amt_org', '>', 0);
            })->sum('credit_amt_org');

        $opening_payment_received =  Voucher::where('reference_service', ConstantHelper::RECEIPTS_SERVICE_ALIAS)
            ->where('organization_id', $organization_id)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->where('document_date', '<', $startDate)
            ->with('items') // we just need items, ledger is not needed for sum
            ->get()
            ->flatMap(function ($voucher) {
                return $voucher->items->where('credit_amt_org', '>', 0);
            })
            ->sum('credit_amt_org');
        $opening = $opening_payment_received - $opening_payment_made;
        $closing = ($opening + $payment_received_t) - $payment_made_t;
        if($startDate==$endDate)
        $fy = self::formatWithOrdinal($startDate);
        else
        $fy = self::formatWithOrdinal($startDate) . ' to ' . self::formatWithOrdinal($endDate);

        $companies = Helper::getAuthenticatedUser()->access_rights_org;
        $startDate = date('d-m-Y', strtotime($startDate));
        $endDate = date('d-m-Y', strtotime($endDate));
        $orgLogo = Helper::getOrganizationLogo($organization_id);
        $organization = Organization::find($organization_id);
        //  $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
        $created_by = AuthUser::find($createdBy)->name;
        $currency = Currency::find($organization?->currency_id)?->name;
        $in_words = Helper::numberToWords(abs($closing)); 
            $pdf = PDF::loadView('pdf.cashflow', [
            'created_by' => $created_by,
            'opening' => $opening,
            'in_words' => $in_words,
            'payment_received' => $payment_received,
            'payment_made' => $payment_made,
            'payment_made_t' => $payment_made_t,
            'payment_received_t' => $payment_received_t,
            'closing' => $closing,
            'fy' => $fy,
            'companies' => $companies,
            'organization_id' => $organization_id,
            'range' => $fy,
            'currency' => $currency,
            'orgLogo' => $orgLogo,
            'organization' => $organization,
            'organizationAddress' => $organizationAddress
        ]);
        
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf; 
    }
    static function formatWithOrdinal($date)
    {
        $date = Carbon::parse($date);
        $day = $date->day;
        $suffix = match (true) {
            $day % 10 === 1 && $day !== 11 => 'st',
            $day % 10 === 2 && $day !== 12 => 'nd',
            $day % 10 === 3 && $day !== 13 => 'rd',
            default => 'th',
        };

        return $day . $suffix . ' ' . $date->format('F Y');
    }
    public function addScheduler(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'to' => 'required|array',
            'cc' => 'nullable|array',
            'type' => 'required|string',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);
        $toIds = $validatedData['to'];

        foreach ($toIds as $toId) {
            CashflowScheduler::updateOrCreate(
                [
                    'toable_id' => $toId,
                ],
                [
                    'type' => $validatedData['type'],
                    'date' => $validatedData['date'],
                    'cc' => json_encode($validatedData['cc']),
                    'remarks' => $validatedData['remarks'],
                    'organization_id' => Helper::getAuthenticatedUser()->organization_id,
                    'created_by' => Helper::getAuthenticatedUser()->auth_user_id,
                ]
            );
        }

        return Response::json(['success' => 'Scheduler Added Successfully!']);
    }

    public function export(Request $request)
    {
        // dd($request->all());
        // Decode JSON arrays back into objects/arrays
        // $payment_made = json_decode($request->payment_made);
        // $payment_received = json_decode($request->payment_received);
        $data = $request->all();
        $data['organization_id'] = Helper::getAuthenticatedUser()->organization_id;
        $data['createdBy'] = Helper::getAuthenticatedUser()->name;
        $organization = Organization::find($data['organization_id']);
        $data['currency'] = Currency::find($organization?->currency_id)?->name;
        $data['in_words'] = Helper::numberToWords(abs($data['closing']));

        return Excel::download(
            new CashflowExport(
                $data
            ),
            'cashflow-statement.xlsx'
        );
    }
    
}
