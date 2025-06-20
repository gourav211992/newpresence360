<?php

namespace App\Http\Controllers;

use App\Helpers\ConstantHelper;
use App\Models\ExpenseHeader;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Models\CostCenterOrgLocations;
use App\Models\ErpStore;
use App\Models\ExpenseTed;
use App\Models\Tax;
use App\Models\Organization;
use Carbon\Carbon;
use App\Models\Vendor;
use App\Models\Voucher;

class TDSReportController extends Controller
{
    public function index(Request $request, $page = null)
    {
        //$fy = Helper::getFinancialYear(date('Y-m-d'));
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
        }
        $organization_id = null;
        $vendor_id = null;
        $location_id = null;
        $cost_center_id = null;

        if ($request->organization_filter)
            $organization_id = $request->organization_filter;

        if ($request->location_id)
            $location_id = $request->location_id;

        if ($request->cost_center_id)
            $cost_center_id = $request->cost_center_id;

        if ($request->vendor_filter)
            $vendor_id = $request->vendor_filter;



        $mappings = Helper::getAuthenticatedUser()->access_rights_org;

        $expneseTeds = ExpenseTed::where('ted_type', 'Tax')
            ->whereHas('expenseHeader', function ($query) {
                $query->whereNotNull('vendor_id'); // Adjust 'vendor_id' to your actual foreign key column
            })
            ->with([
                'expenseHeader' => function ($query) use ($organization_id, $startDate, $endDate, $vendor_id, $cost_center_id, $location_id) {
                    $query->withDefaultGroupCompanyOrg()
                        ->with([
                            'vendor' => function ($vendorQuery) use ($vendor_id) {
                                $vendorQuery->when($vendor_id, function ($q) use ($vendor_id) {
                                    $q->where('id', $vendor_id);
                                });
                            },
                        ])
                        ->when($organization_id, function ($query) use ($organization_id) {
                            $query->where('organization_id', $organization_id);
                        })
                        ->when($cost_center_id, function ($query) use ($cost_center_id) {
                            $query->where('cost_center_id', $cost_center_id);
                        })
                        ->whereBetween('document_date', [$startDate, $endDate])
                        ->when($location_id, function ($query) use ($location_id) {
                            $query->where('store_id', $location_id);
                        })->whereBetween('document_date', [$startDate, $endDate])
                        ->where('document_status', ConstantHelper::POSTED)
                        ->whereNotNull('vendor_id'); // Adjust 'vendor_id' here as well
                },
                'taxDetail.ledger'
            ])
            ->get();

            $records = $expneseTeds->filter(function ($ted) {
                $td = $ted->taxDetail;

                // Ensure taxDetail and ledger exist
                if (!$td || !$td->ledger) {
                    return false;
                }
                $isTdsGroup = $td->ledger->groups()->contains('name', 'TDS');

                if (!$isTdsGroup) {
                    return false;
                }

                if (request('tax_filter')) {
                    return $td->ledger->tds_section == request('tax_filter');
                }
                return true;
            });


        $vendorIds = $records->pluck('expenseHeader.vendor_id')->filter()->unique()->toArray();

        // Fetch vendors matching these IDs
        $vendors = Vendor::withDefaultGroupCompanyOrg()
            ->whereIn('id', $vendorIds)
            ->get();
        // Get ExpenseTed IDs from $records
        $tedIds = $records->pluck('ted_id')->filter()->unique()->toArray();
        $taxIds = \App\Models\TaxDetail::whereIn('id', $tedIds)
            ->pluck('tax_id')
            ->filter()
            ->unique()
            ->toArray();

        // Fetch Tax records linked with these TED IDs
        $taxTypes = ConstantHelper::getTdsSections();
        $cost_centers = CostCenterOrgLocations::with('costCenter')->get()->map(function ($item) {
            $item->withDefaultGroupCompanyOrg()->where('status', 'active');

            return [
                'id' => $item->costCenter->id,
                'name' => $item->costCenter->name,
                'location' => $item->costCenter->locations,
            ];
        })->toArray();

        $startDate = date('d-m-Y', strtotime($startDate));
        $endDate = date('d-m-Y', strtotime($endDate));
        $range = $startDate . ' to ' . $endDate;
        $fy = self::formatWithOrdinal($startDate) . ' to ' . self::formatWithOrdinal($endDate);
        $locations = InventoryHelper::getAccessibleLocations();
        return view('tds.index', compact('fy', 'mappings', 'organization_id', 'range', 'vendors', 'vendor_id', 'records', 'taxTypes', 'cost_centers', 'locations', 'cost_center_id', 'location_id'));
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
