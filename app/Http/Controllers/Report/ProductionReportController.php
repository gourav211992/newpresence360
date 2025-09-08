<?php

namespace App\Http\Controllers\Report;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\Organization;
use App\Helpers\ConstantHelper;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\View\BomVsConsumption;
use App\Models\View\ProductionTracking;
use Barryvdh\DomPDF\Facade\Pdf;
class ProductionReportController extends Controller
{
        public function bomVsActualReport(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        $organization = Organization::find($organizationId);

        $groupId   = $organization?->group_id ?? null;
        $companyId = $organization?->company_id ?? null;

        if ($request->ajax()) {
            $query = BomVsConsumption::query()
                ->where('group_id', $groupId)
                ->where('company_id', $companyId)
                ->where('organization_id', $organizationId)
                ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
                ->orderByDesc('bom_consumption_id'); 
                 if ($request->filled('date_range')) {
                    $dates = explode(' to ', $request->date_range);
                   
                    if (count($dates) === 2) {
                        $startDate = \Carbon\Carbon::parse($dates[0])->startOfDay()->format('Y-m-d');
                        $endDate   = \Carbon\Carbon::parse($dates[1])->endOfDay()->format('Y-m-d');

                        $query->whereDate('pslip_document_date', '>=', $startDate)
                            ->whereDate('pslip_document_date', '<=', $endDate);
                    }
                }
             
                if ($request->filled('so_document_number')) {
                    $so = explode('-', $request->so_document_number);
                    $so_number=isset($so[1])?$so[1]:$request->so_document_number;
                    $query->where('so_document_number', 'like', '%' . $so_number . '%');
                }

                if ($request->filled('mo_document_number')) {
                    $mo = explode('-', $request->mo_document_number);
                    $mo_number=isset($mo[1])?$mo[1]:'';
                    $query->where('mo_document_number', 'like', '%' . $mo_number . '%');
                }

                if ($request->filled('item_code')) {
                    $query->where('pslip_item_code', 'like', '%' . $request->item_code . '%');
                }
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('attributes', function ($row) {
                    if (!$row->attributes) {
                        return '-';
                    }
                    $badges = explode(',', $row->attributes);
                    return collect($badges)->map(function ($attr) {
                        return '<span class="badge bg-primary me-1">' . trim($attr) . '</span>';
                    })->implode(' ');
                })
                ->editColumn('cons_attributes', function ($row) {
                    if (!$row->cons_attributes) {
                        return '-';
                    }
                    $badges = explode(',', $row->cons_attributes);
                    return collect($badges)->map(function ($attr) {
                        return '<span class="badge bg-primary me-1">' . trim($attr) . '</span>';
                    })->implode(' ');
                })
                ->rawColumns(['attributes','cons_attributes'])
                ->make(true);
        }

        return view('bomVsActualReport');
    }

    public function downloadBomVsActualWithOutfile(Request $request)
    {
            $fileName = 'bomVsActual_export_' . time() . '.csv';
            $localFilePath = storage_path("app/$fileName");
            $user = Helper::getAuthenticatedUser();
            $organizationId = $user->organization_id;
            $organization = Organization::find($organizationId);

            $groupId   = $organization?->group_id ?? null;
            $companyId = $organization?->company_id ?? null;

        $query = DB::table('erp_bom_vs_consumptions_view')
            ->select([
                'pslip_book_code',
                'pslip_document_number',
                'pslip_document_date',
                'mo_document_number',
                'mo_document_date',
                'so_document_number',
                'so_document_date',
                'store_name',
                'sub_store_name',
                'pslip_item_code',
                'pslip_item_name',
                'attributes',
                'consumed_item_code',
                'consumed_item_name',
                'cons_attributes',
                'uom_code',
                'required_qty',
                'consumption_qty',

                // Calculated fields
                DB::raw('(required_qty * rate) as required_total'),
                DB::raw('(consumption_qty * rate) as consumed_total'),
                DB::raw('(required_qty - consumption_qty) as remaining_qty'),
                DB::raw('((required_qty * rate) - (consumption_qty * rate)) as remaining_total'),
                DB::raw('CASE
                            WHEN required_qty > 0
                            THEN ROUND(((required_qty - consumption_qty) / required_qty) * 100, 2)
                            ELSE 0
                        END as remaining_qty_percentage'),
                DB::raw('CASE
                            WHEN (required_qty * rate) > 0
                            THEN ROUND((((required_qty - consumption_qty) * rate) / (required_qty * rate)) * 100, 2)
                            ELSE 0
                        END as remaining_total_percentage'),
            ])
            ->where('group_id', $groupId)
            ->where('company_id', $companyId)
            ->where('organization_id', $organizationId)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED);
                if ($request->filled('date_range')) {
                    $dates = explode(' to ', $request->date_range);

                    if (count($dates) === 2) {
                        $startDate = \Carbon\Carbon::parse($dates[0])->startOfDay()->format('Y-m-d');
                        $endDate   = \Carbon\Carbon::parse($dates[1])->endOfDay()->format('Y-m-d');

                        $query->whereDate('pslip_document_date', '>=', $startDate)
                            ->whereDate('pslip_document_date', '<=', $endDate);
                    }
                }
        

                if ($request->filled('so_number')) {
                    $so = explode('-', $request->so_number);
                  
                    $so_number=isset($so[1])?$so[1]:$request->so_number;
                    $query->where('so_document_number', 'like', '%' . $so_number . '%');
                }

                if ($request->filled('mo_number')) {
                    $mo = explode('-', $request->mo_number);
                    $mo_number=isset($mo[1])?$mo[1]:'';
                    $query->where('mo_document_number', 'like', '%' . $mo_number . '%');
                }

                if ($request->filled('item')) {
                   
                    $query->where('pslip_item_code', 'like', '%' . $request->item . '%');
                }
            $results=$query->get();


            $handle = fopen($localFilePath, 'w');

            // Header row
            fputcsv($handle, [
                'Series',
                'Document Number',
                'Document Date',
                'MO Number',
                'MO Date',
                'SO Number',
                'SO Date',
                'Store Name',
                'Sub Store Name',
                'Product Code',
                'Product Name',
                'Attributes',
                'Item Code',
                'Item Name',
                'Attributes',
                'UOM Code',
                'Planned Qty',
                'Planned Cost',
                'Actual Qty',
                'Actual Cost',
                'Variance Qty',
                'Variance Cost',
                'Variance Qty %',
                'Variance Cost %',
            ]);

            // Data rows
            foreach ($results as $row) {
                fputcsv($handle, [
                    $row->pslip_book_code,
                    $row->pslip_document_number,
                    $row->pslip_document_date,
                    $row->mo_document_number,
                    $row->mo_document_date,
                    $row->so_document_number,
                    $row->so_document_date,
                    $row->store_name,
                    $row->sub_store_name,
                    $row->pslip_item_code,
                    $row->pslip_item_name,
                    $row->attributes,
                    $row->consumed_item_code,
                    $row->consumed_item_name,
                    $row->cons_attributes,
                    $row->uom_code,
                    $row->required_qty,
                    $row->required_total,
                    $row->consumption_qty,
                    $row->consumed_total,
                    $row->remaining_qty,
                    $row->remaining_total,
                    $row->remaining_qty_percentage . '%',
                    $row->remaining_total_percentage . '%',
                ]);
            }

            fclose($handle);


        return response()->download($localFilePath)->deleteFileAfterSend(true);
    }

    public function productionTrackingReport(Request $request)
    {
        $user = Helper::getAuthenticatedUser(); 
        $organizationId = $user->organization_id;
        $organization   = Organization::find($organizationId);
        $groupId        = $organization?->group_id ?? null;
        $companyId      = $organization?->company_id ?? null;

        if ($request->ajax()) {
           
        $query = ProductionTracking::query()
                ->where('group_id', $groupId)
                ->where('company_id', $companyId)
                ->where('organization_id', $organizationId)
                ->where('main_so_item', 1)
                ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
                ->orderByDesc('id'); 
                 if ($request->filled('date_range')) {
                    $dates = explode(' to ', $request->date_range);
                   
                    if (count($dates) === 2) {
                        $startDate = \Carbon\Carbon::parse($dates[0])->startOfDay()->format('Y-m-d');
                        $endDate   = \Carbon\Carbon::parse($dates[1])->endOfDay()->format('Y-m-d');

                        $query->whereDate('pwo_document_date', '>=', $startDate)
                            ->whereDate('pwo_document_date', '<=', $endDate);
                    }
                }
             
                if ($request->filled('so_number')) {
                    $so = explode('-', $request->so_number);
                 
                    $so_number=isset($so[1])?$so[1]:$request->so_number;
                    $query->where('so_document_number', 'like', '%' . $so_number . '%');
                }

                if ($request->filled('pwo_number')) {
                    $pwo = explode('-', $request->pwo_number);
                    $pwo_number=isset($pwo[1])?$pwo[1]:'';
                    $query->where('pwo_document_number', 'like', '%' . $pwo_number . '%');
                }

                if ($request->filled('item_code')) {
                      
                    $query->where('item_code', 'like', '%' . $request->item_code . '%');
                }
            
                return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('attributes', function ($row) {
                    if (!$row->attributes) {
                        return '-';
                    }
                    $badges = explode(',', $row->attributes);
                    return collect($badges)->map(function ($attr) {
                        return '<span class="badge bg-primary me-1">' . trim($attr) . '</span>';
                    })->implode(' ');
                })
                ->editColumn('pwo_document_number', function ($row) {
                    return $row->pwo_book_code . '-' . $row->pwo_document_number;
                })
                ->editColumn('so_document_number', function ($row) {
                    return $row->so_book_code 
                        ? $row->so_book_code . '-' . $row->so_document_number
                        : '-';
                })
                ->rawColumns(['attributes', 'pwo_document_number', 'so_document_number'])
                ->make(true);
        }

        return view('reports.productionTrackingReport');

    }
    public function downloadProductionTrackingWithOutfile(Request $request)
    {
        $fileName = 'productionTracking_export_' . time() . '.csv';
        $localFilePath = storage_path("app/$fileName");

        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        $organization   = Organization::find($organizationId);

        $groupId   = $organization?->group_id ?? null;
        $companyId = $organization?->company_id ?? null;

        $query = ProductionTracking::where('group_id', $groupId)
            ->where('company_id', $companyId)
            ->where('organization_id', $organizationId)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->where('main_so_item', 1);
                if ($request->filled('date_range')) {
                    $dates = explode(' to ', $request->date_range);

                    if (count($dates) === 2) {
                        $startDate = \Carbon\Carbon::parse($dates[0])->startOfDay()->format('Y-m-d');
                        $endDate   = \Carbon\Carbon::parse($dates[1])->endOfDay()->format('Y-m-d');

                        $query->whereDate('pwo_document_date', '>=', $startDate)
                            ->whereDate('pwo_document_date', '<=', $endDate);
                    }
                }
        

                if ($request->filled('so_number')) {
                    $so = explode('-', $request->so_number);
                  
                    $so_number=isset($so[1])?$so[1]:$request->so_number;
                    $query->where('so_document_number', 'like', '%' . $so_number . '%');
                }

                if ($request->filled('pwo_number')) {
                    $pwo = explode('-', $request->pwo_number);
                    $pwo_number=isset($pwo[1])?$pwo[1]:'';
                    $query->where('pwo_document_number', 'like', '%' . $pwo_number . '%');
                }

                if ($request->filled('item')) {
                      
                    $query->where('item_code', 'like', '%' . $request->item . '%');
                }
            $result = $query->get();

        $handle = fopen($localFilePath, 'w');

        fputcsv($handle, [
            'PWO Date',
            'PWO Number',
            'Product Code',
            'Product Name',
            'Attributes',
            'UOM',
            'SO Qty',
            'PWO Qty',
            'Produced Qty',
            'Completion %',
            'Customer Name',
            'SO Number',
            'SO Date',
        ]);

       
        foreach ($result as $row) {
            fputcsv($handle, [
                date('d-m-Y', strtotime($row->pwo_document_date)),
                $row->pwo_book_code . '-' . $row->pwo_document_number,
                $row->item_code,
                $row->item_name,
                $row->attributes ?: '-',
                $row->uom_code,
                $row->so_order_qty,
                $row->qty,
                $row->pslip_qty,
                $row->completion_percent . '%',
                $row->customer_name,
                $row->so_book_code 
                    ? $row->so_book_code . '-' . $row->so_document_number 
                    : '-',
                date('d-m-Y', strtotime($row->so_document_date)),
                ]);
        }

        fclose($handle);

        return response()->download($localFilePath)->deleteFileAfterSend(true);
    }

    public function productionTrackingDetails(Request $request, $id)
    {
        $user = Helper::getAuthenticatedUser(); 
        $organizationId = $user->organization_id;
        $organization = Organization::find($organizationId);
        $groupId   = $organization?->group_id ?? null;
        $companyId = $organization?->company_id ?? null;
      
        $details = ProductionTracking::query()
                ->where('group_id', $groupId)
                ->where('company_id', $companyId)
                ->where('organization_id', $organizationId)
                ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
                ->where('id', $id)
                ->first();

        if ($request->ajax()||$request->pdf) {
                $query = DB::table('erp_pwo_so_mapping as a')
                    ->Join('erp_mo_products as mp', 'mp.pwo_mapping_id', '=', 'a.id')
                    ->Join('erp_mfg_orders as b', 'b.id', '=', 'mp.mo_id')
                    ->leftJoin('erp_production_slips as c', 'c.mo_id', '=', 'b.id')
                    ->leftJoin('erp_pslip_items as d', 'd.pslip_id', '=', 'c.id')
                    ->leftJoin('erp_sub_stores as f', 'f.id', '=', 'b.sub_store_id')
                    ->leftJoin('erp_stations as e', 'e.id', '=', 'b.station_id')
                    ->select([
                        'a.id as mapping_id',
                        'a.mo_product_qty',
                        'a.qty as pwo_qty',

                        'b.book_code',
                        'b.document_number',
                        'b.document_date',

                        DB::raw("CASE WHEN b.is_last_station = 1 THEN 'FINAL' ELSE 'WIP' END as type"),

                        'c.book_code as pslip_book_code',
                        'c.document_number as pslip_document_number',
                        'c.document_date as pslip_document_date',
                        'd.qty',
                        'd.accepted_qty',
                        'd.subprime_qty',
                        'd.rejected_qty',
                        'd.item_code',
                        'd.item_name',
                        'e.name as station_name',
                        'f.name as sub_store_name',
                        'f.code as sub_store_code'
                    ])->where('b.group_id', $groupId)
                    ->where('a.pwo_id', $details->pwo_id)
                    ->where('a.so_item_id', $details->so_item_id)
                    ->where('b.company_id', $companyId)
                    ->where('b.organization_id', $organizationId)
                    ->orderByDesc('a.so_item_id');
        }
        if($request->pdf){
            $get=$query->get();
            $imagePath = public_path('assets/css/midc-logo.jpg');
            $title = 'Production Report';
            $organizationAddress = Address::with(['city', 'state', 'country'])
                    ->where('addressable_id', $user->organization_id)
                    ->where('addressable_type', Organization::class)
                    ->first();
            $data=[
                'title' => $title,
                'imagePath' => $imagePath,
                'user' => $user,
                'organization' => $organization,
                'organizationAddress' => $organizationAddress
            ];
           $pdf = PDF::loadView('reports.pdf.productionTrackingDetails',$data,compact('details','get'));

        $pdf->setOption('isHtml5ParserEnabled', true);
        return $pdf->stream(str_replace(' ', '', $title) . '-' . date('Y-m-d') . '.pdf');

        }

        if ($request->ajax()) {
                return DataTables::of($query)
                ->addIndexColumn() 
                ->filterColumn('document_date', function($query, $keyword) {
                        // Match both Y-m-d and d-m-Y
                        $query->where(function($q) use ($keyword) {
                            $q->whereRaw("DATE_FORMAT(b.document_date, '%Y-%m-%d') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(b.document_date, '%d-%m-%Y') like ?", ["%{$keyword}%"]);
                        });
                    })
                ->filterColumn('pslip_document_date', function($query, $keyword) {
                        $query->where(function($q) use ($keyword) {
                            $q->whereRaw("DATE_FORMAT(c.document_date, '%Y-%m-%d') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(c.document_date, '%d-%m-%Y') like ?", ["%{$keyword}%"]);
                        });
                    })
                ->make(true);
        }

        return view('reports.productionDetails', compact('details'));
    }
}
