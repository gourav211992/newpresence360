<?php

namespace App\Http\Controllers\Finance;

use App\Helpers\ConstantHelper;
use App\Http\Controllers\Controller;
use App\Models\ErpGstInvoiceType;
use App\Models\Finance\GstrCompiledData;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use App\Models\OrganizationGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\finance\GstrDetailExport;
use App\Helpers\GstrHelper;
use App\Helpers\Helper;

class GstrController extends Controller
{

    public function index(Request $request){
        $pageLengths = ConstantHelper::PAGE_LENGTHS;
        $length = $request->length ? $request->length : ConstantHelper::PAGE_LENGTH_10;

        $startDate = Carbon::now()->startOfMonth(); // Start of the current month
        $endDate = Carbon::now()->endOfMonth(); 

        // Check if there's an applied date filter
        if ($request->has('date_range') && $request->date_range != '') {
            $dates = explode(' to ', $request->date_range);
            $startDate = $dates[0] ? Carbon::parse($dates[0])->startOfDay() : null;
            $endDate = isset($dates[1]) ? Carbon::parse($dates[1])->startOfDay():  Carbon::parse($dates[0])->startOfDay();
        }

        $connection = config('database.connections.mysql.database');

        $organizationId = Helper::getAuthenticatedUser()->organization_id;
        $organization = Organization::where('id', $organizationId)->first();
        $gstin = $organization->gst_number ?? env('EINVOICE_GSTIN', '');

        $gstrInvoiceTypes = ErpGstInvoiceType::on('mysql_master') // Set connection for master
                ->where(function ($query) use ($request) {
                    if($request->search){
                        $query->where('erp_gst_invoice_types.name', 'like', '%' . $request->search . '%');
                    }

                    $this->filter($request,$query);
                })
                ->select(
                    'erp_gst_invoice_types.id',
                    'erp_gst_invoice_types.name as type',
                    DB::raw("COALESCE(SUM(erp_gstr_compiled_data.taxable_amt)) as taxable_amt"),
                    DB::raw("COALESCE(SUM(erp_gstr_compiled_data.rate)) as gst_rate"),
                    DB::raw("COALESCE(SUM(erp_gstr_compiled_data.sgst)) as sgst"),
                    DB::raw("COALESCE(SUM(erp_gstr_compiled_data.cgst)) as cgst"),
                    DB::raw("COALESCE(SUM(erp_gstr_compiled_data.igst)) as igst"),
                    DB::raw("COALESCE(SUM(erp_gstr_compiled_data.cess)) as cess"),
                    // DB::raw("COALESCE(SUM(erp_gstr_compiled_data.taxable_amt)) as tax_amt"),
                    DB::raw("COALESCE(SUM(erp_gstr_compiled_data.invoice_amt)) as invoice_amt"),
                    DB::raw("COUNT(erp_gstr_compiled_data.id) as invoice_count")
                )
                ->leftJoin("{$connection}.erp_gstr_compiled_data", "erp_gstr_compiled_data.invoice_type_id", '=', 'erp_gst_invoice_types.id')
                ->whereBetween("erp_gstr_compiled_data.invoice_date", [$startDate, $endDate])
                ->where("erp_gstr_compiled_data.supplier_gstin", $gstin)
                ->groupBy('erp_gst_invoice_types.id')
                ->paginate($length);

        $masterData = self::masterData();

        $types = ErpGstInvoiceType::on('mysql_master')
        ->join("{$connection}.erp_gstr_compiled_data", 'erp_gstr_compiled_data.invoice_type_id', '=', 'erp_gst_invoice_types.id')
        ->where("erp_gstr_compiled_data.supplier_gstin", $gstin)
        ->whereBetween("erp_gstr_compiled_data.invoice_date", [$startDate, $endDate])
        ->select('erp_gst_invoice_types.id', 'erp_gst_invoice_types.name')
        ->where(function($q) use($request){
            if($request->search){
                $q->where('erp_gst_invoice_types.name', 'like', '%' . $request->search . '%');
            }
        })
        ->groupBy('erp_gst_invoice_types.id')
        ->get();

        return view('finance.gstr.index',[
            'pageLengths' => $pageLengths,
            'gstrInvoiceTypes' => $gstrInvoiceTypes,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'groups' => $masterData['groups'],
            'organizationData' => $masterData['organizations'],
            'companies' => $masterData['companies'],
            'types' => $types,
        ]);
    }

    public function json(Request $request){
        $organizationId = Helper::getAuthenticatedUser()->organization_id;
        $organization = Organization::where('id', $organizationId)->first();
        $supplierGstin = $organization->gst_number ?? env('EINVOICE_GSTIN', '');
        
        $startDate = Carbon::now()->startOfMonth(); // Start of the current month
        $endDate = Carbon::now()->endOfMonth(); 

        // Check if there's an applied date filter
        if ($request->has('date_range') && $request->date_range != '') {
            $dates = explode(' to ', $request->date_range);
            $startDate = $dates[0] ? Carbon::parse($dates[0])->startOfDay() : null;
            $endDate = isset($dates[1]) ? Carbon::parse($dates[1])->startOfDay():  Carbon::parse($dates[0])->startOfDay();
        }

        $financialPeriod = date('mY'); 
        // $financialPeriod = self::currentFinancialYear(); 

        // Fetch all invoice types
        $gstrInvoiceTypes = ErpGstInvoiceType::on('mysql_master')->where(function($q) use($request){
            if($request->search){
                $q->where('name', 'like', '%' . $request->search . '%');
            }
        })->get();

        // Initialize the response array
        $arr = [
            "gstin" => $supplierGstin,
            "fp" => $financialPeriod,
            "version" => "GST3.2.1",
            "hash" => "hash"
        ];

        // Define the invoice types that should be grouped under "ecoma"
        $ecomaTypes = ['ecoab2b', 'ecoab2c', 'ecoaurp2b', 'ecoaurp2c'];
        $ecomTypes = ['ecob2b', 'ecob2c', 'ecourp2b', 'ecourp2c'];

        // Loop through each invoice type and process data
        foreach ($gstrInvoiceTypes as $invoiceType) {
            $invoiceTypeName = strtolower($invoiceType->name);
            
            $gstrCompiledData = GstrCompiledData::where(function ($query) use ($request) {
                    $this->filter($request,$query);
                })
                ->where('invoice_type_id', $invoiceType->id)
                ->where('supplier_gstin', $supplierGstin)
                ->whereBetween('erp_gstr_compiled_data.invoice_date', [$startDate, $endDate])
                ->get();

                
            if ($gstrCompiledData->isEmpty()) {
                continue; // Skip if no data for this type
            }

            if (in_array($invoiceTypeName, $ecomaTypes)) {
                $arr['ecoma'] = array_merge($arr['ecoma'] ?? [], GstrHelper::prepareData($gstrCompiledData, $invoiceTypeName));
            }elseif (in_array($invoiceTypeName, $ecomTypes)) {
                $arr['ecom'] = array_merge($arr['ecom'] ?? [], GstrHelper::prepareData($gstrCompiledData, $invoiceTypeName));
            }else{
                $arr[$invoiceTypeName] = GstrHelper::prepareData($gstrCompiledData, $invoiceTypeName);
            }
            
        }

        // Convert JSON to a pretty format
        $jsonContent = json_encode($arr, JSON_PRETTY_PRINT);
        $fileName = $supplierGstin.'_gstr1.json';

        return response($jsonContent)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
    }

    public function details(Request $request,$id){
        $pageLengths = ConstantHelper::PAGE_LENGTHS;
        $length = $request->length ? $request->length : ConstantHelper::PAGE_LENGTH_10;
        
        $masterData = self::masterData();

        $startDate = Carbon::now()->startOfMonth(); // Start of the current month
        $endDate = Carbon::now()->endOfMonth(); 

        // Check if there's an applied date filter
        if ($request->has('date_range') && $request->date_range != '') {
            $dates = explode(' to ', $request->date_range);
            $startDate = $dates[0] ? Carbon::parse($dates[0])->startOfDay() : null;
            $endDate = isset($dates[1]) ? Carbon::parse($dates[1])->startOfDay():  Carbon::parse($dates[0])->startOfDay();
        }
        
        $type = ErpGstInvoiceType::on('mysql_master')->where('id',$id)->first();

        $gstrData = GstrCompiledData::where(function($query) use($request){
                            $this->filter($request,$query);
                    
                            if($request->has('search')){
                                $query->where('erp_gstr_compiled_data.party_name', 'like', '%' . $request->search . '%')
                                    ->orWhere('erp_gstr_compiled_data.party_gstin', 'like', '%' . $request->search . '%');
                            }

            })
        ->whereBetween('erp_gstr_compiled_data.invoice_date', [$startDate, $endDate])
        ->where('invoice_type_id',$id)
        ->paginate($length);

        return view('finance.gstr.detail',[
            'pageLengths' => $pageLengths,
            'gstrData' => $gstrData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'type' => $type,
            'groups' => $masterData['groups'],
            'organizationData' => $masterData['organizations'],
            'companies' => $masterData['companies'],
        ]);
    }

    private function filter($request,$query){
        if($request->group_id){
            $query->where('erp_gstr_compiled_data.group_id', 'like', '%' . $request->group_id . '%');
        }
        
        if($request->company_id){
            $query->where('erp_gstr_compiled_data.company_id', 'like', '%' . $request->company_id . '%');
        }

        if($request->organization_id){
            $query->where('erp_gstr_compiled_data.organization_id', 'like', '%' . $request->organization_id . '%');
        }

        return $query;

    }

    private function currentFinancialYear(){
        $month = date('n'); // numeric month (1 to 12)
        $year = date('Y');

        if ($month >= 4) {
            // If current month is April (4) or later
            $financialYear = $year . '-' . ($year + 1);
        } else {
            // If current month is Jan-Mar (1-3), we are in previous FY
            $financialYear = ($year - 1) . '-' . $year;
        }
        return $financialYear;
    }

    private function masterData(){
        $groups = OrganizationGroup::select('id','name')->get();
        $organizations = Organization::select('id','name')->get();
        $companies = OrganizationCompany::select('id','name')->get();
        $types = ErpGstInvoiceType::on('mysql_master')->get();

        return [
            'groups' => $groups,
            'organizations' => $organizations,
            'companies' => $companies,
            'types' => $types
        ];
    }

    public function detailCsv(Request $request, $id){
        $organizationId = Helper::getAuthenticatedUser()->organization_id;
        $organization = Organization::where('id', $organizationId)->first();
        $gstin = $organization->gst_number ?? env('EINVOICE_GSTIN', '');

        $gstrExport = new GstrDetailExport();
        if ($id === 'all') {
            $types = ErpGstInvoiceType::on('mysql_master')->where(function($q) use($request){
                        if($request->search){
                            $q->where('erp_gst_invoice_types.name', 'like', '%' . $request->search . '%');
                        }
                    })->get();

            $zipFileName = "temp/finance/gstr1/{$gstin}_all_csvs.zip";
            $zipPath = public_path($zipFileName);
            $zip = new \ZipArchive;

            if (file_exists($zipPath)) {
                unlink($zipPath); // remove old file if exists
            }

            if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                foreach ($types as $type) {
                    $fileName = "temp/finance/gstr1/{$type->name}.csv";
                    $gstrExport->export($fileName, $request, $type->id, $type->name, $gstin);
                    $zip->addFile(public_path($fileName), $type->name . '.csv');
                }

                $zip->close();

                return response()->download($zipPath);
            } else {
                return back()->with('error', 'Could not create ZIP file');
            }

        }else{    
            $type = ErpGstInvoiceType::on('mysql_master')->where('id',$id)->first();
            $fileName = "temp/finance/gstr1/".$gstin.'_'.$type->name.".csv";
            $gstrExport->export($fileName, $request, $id, $type->name, $gstin);
            return redirect($fileName);
        }

    }
}