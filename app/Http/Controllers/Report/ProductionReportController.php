<?php

namespace App\Http\Controllers\Report;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Helpers\ConstantHelper;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\View\BomVsConsumption;
use App\Models\View\ProductionTracking;

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

                if ($request->filled('consumed_item_code')) {
                      
                    $query->where('consumed_item_code', 'like', '%' . $request->consumed_item_code . '%');
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

        $results = DB::table('erp_bom_vs_consumptions_view')
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
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->get();


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
             
                if ($request->filled('so_document_number')) {
                    $so = explode('-', $request->so_document_number);
                    $so_number=isset($so[1])?$so[1]:$request->so_document_number;
                    $query->where('so_document_number', 'like', '%' . $so_number . '%');
                }

                if ($request->filled('pwo_document_number')) {
                    $pwo = explode('-', $request->pwo_document_number);
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
            ->get();

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

       
        foreach ($query as $row) {
            fputcsv($handle, [
                $row->pwo_document_date,
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
                $row->so_document_date,
                ]);
        }

        fclose($handle);

        return response()->download($localFilePath)->deleteFileAfterSend(true);
    }

    public function productionTrackingDetails($id)
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

        return view('reports.productionDetails', compact('details'));
    }
}
