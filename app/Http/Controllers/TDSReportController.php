<?php

namespace App\Http\Controllers;

use App\Helpers\ConstantHelper;
use App\Models\ExpenseHeader;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\CostCenterOrgLocations;
use App\Models\ErpStore;
use App\Models\Tax;
use App\Models\Organization;
use Carbon\Carbon;
use App\Models\Vendor;
use App\Models\Voucher;

class TDSReportController extends Controller
{
    public function index(Request $request, $page = null)
    {
        // dd($request->all());
        $fy = Helper::getFinancialYear(date('Y-m-d'));
        $startDate = date('Y-m-d', strtotime($fy['start_date']));
        $endDate = date('Y-m-d', strtotime($fy['end_date']));

        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
        }
        if ($request->organization_filter)
            $organization_id = $request->organization_filter;
        else
            $organization_id = Helper::getAuthenticatedUser()->organization_id;
        $vendor_id = null;
        $location_id = $request->location_id;
        $cost_center_id = $request->location_id;
        if ($request->vendor_filter)
            $vendor_id = $request->vendor_filter;

        $startDate = date('d-m-Y', strtotime($startDate));
        $endDate = date('d-m-Y', strtotime($endDate));
        $range = $startDate . ' to ' . $endDate;
        $mappings = Helper::getAuthenticatedUser()->access_rights_org;
        $vendors = Vendor::where('organization_id', $organization_id)->get();
        $fy = self::formatWithOrdinal($startDate) . ' to ' . self::formatWithOrdinal($endDate);
        $vouchers = Voucher::where('organization_id', $organization_id)
            ->where('reference_service', ConstantHelper::EXPENSE_ADVISE_SERVICE_ALIAS)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->whereBetween('document_date',[$startDate,$endDate])
            ->when($location_id, function ($query) use ($location_id) {
                    $query->where('location', $location_id);
                })
            ->when($cost_center_id, function ($query,$cost_center_id) {
               $query->whereHas('items', function ($query) use ($cost_center_id) {
                        $query->where('cost_center_id', $cost_center_id);
                    });
                })
            ->pluck('reference_doc_id');
            $records = ExpenseHeader::with([
                'items.hsn.tax', // Eager load tax data
                'vendor',
            ])
            ->where('organization_id', $organization_id)
            ->whereIn('id', $vouchers)
            
            ->when(request('tax_filter'), function($query) {
                $query->whereHas('items.hsn.tax', function($query) {
                    $query->where('id', request('tax')); // Assuming 'tax_group' is the field you want to filter by
                });
            })
            ->latest()
            ->get();
        
        $taxTypes = Tax::all();
        $cost_centers = CostCenterOrgLocations::with(['costCenter' => function ($query) {
            $query->withDefaultGroupCompanyOrg()->where('status', 'active');
        }])
        ->get()
        ->filter(function ($item) {
            return $item->costCenter !== null;
        })
        ->map(function ($item) {
            return [
                'id' => $item->costCenter->id,
                'name' => $item->costCenter->name,
                'location' => $item->costCenter->locations,
            ];
        })
        ->toArray();
        $locations = ErpStore::where('status','active')->get();

        return view('tds.index', compact('fy', 'mappings', 'organization_id', 'range', 'vendors', 'vendor_id','records','taxTypes','cost_centers','locations','cost_center_id', 'location_id'));
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
}
