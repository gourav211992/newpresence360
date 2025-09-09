<?php
namespace App\Http\Controllers;

use App\Exports\InventoryReportExport;
use Auth;
use PDF;
use DB;
use View;
use Session;
use DataTables;
use Carbon\Carbon;
use Validator;

use Illuminate\Http\Request;

use App\Models\Item;
use App\Models\Vendor;
use App\Models\Address;
use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\ErpAddress;
use App\Models\Organization;
use App\Models\PurchaseOrder;
use App\Models\MrnItemLocation;

use App\Models\StockLedger;
use App\Models\StockLedgerItemAttribute;

use App\Helpers\Helper;
use App\Helpers\ConstantHelper;
use App\Jobs\SendEmailJob;
use App\Models\ErpAttribute;
use App\Models\AttributeGroup;
use App\Models\AuthUser;
use App\Models\Category;
use App\Models\ErpAttributeGroup;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Services\MrnService;
use Maatwebsite\Excel\Facades\Excel;
use DateTime;

class InventoryReportController extends Controller
{
    protected $mrnService;

    public function __construct(MrnService $mrnService)
    {
        $this->mrnService = $mrnService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Helper::getAuthenticatedUser();

        $categories = Category::where('parent_id', null)->get();
        $sub_categories = Category::where('parent_id', '!=',null)->get();
        $items = Item::orderBy('id', 'ASC')
            ->withDefaultGroupCompanyOrg()
            ->get();
        $erpStores = ErpStore::withDefaultGroupCompanyOrg()
            ->orderBy('id', 'DESC')
            ->get();
        $attributeGroups = ErpAttributeGroup::orderBy('id', 'DESC')
            ->get();
        $users = AuthUser::where('organization_id', Helper::getAuthenticatedUser()->organization_id)
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
        $subStoreLocType = ConstantHelper::ERP_SUB_STORE_LOCATION_TYPES;
        // return $records;
        return view('procurement.inventory-report.report',
            compact(
                'user',
                'items',
                'erpStores',
                'attributeGroups',
                'categories',
                'sub_categories',
                'users',
                'subStoreLocType'
            )
        );
    }

    // Report Filter
    public function getReportFilter(Request $request)
    {
        // dd($request->all());
        $user = Helper::getAuthenticatedUser();

        $period = $request->query('period');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $itemId = $request->query('item');
        $categoryId = $request->query('category');
        $subCategoryId = $request->query('subCategory');
        $mCategoryId = $request->query('m_category');
        $mSubCategoryId = $request->query('m_subCategory');
        $store = $request->query('location_id');
        $stockType = $request->query('stock_type');
        $subStore = $request->query('store_id');
        $station = $request->query('station_id');
        $rack = $request->query('rack_id');
        $shelf = $request->query('shelf_id');
        $bin = $request->query('bin_id');
        $storeCheck = $request->query('store_check');
        $subLocationCheck = $request->query('sub_location_check');
        $stationCheck = $request->query('station_check');
        $stockTypeCheck = $request->query('stock_type_check');
        $rackCheck = $request->query('rack_check');
        $shelfCheck = $request->query('shelf_check');
        $binCheck = $request->query('bin_check');
        $attributesCheck = $request->query('attributes_check');
        $tenDaysCheck = $request->query('ten_days_check');
        $fifteenDaysCheck = $request->query('fifteen_days_check');
        $twentyDaysCheck = $request->query('twenty_days_check');
        $attrGroup = $request->query('attribute_name');
        $attrValue = $request->query('attribute_value');
        $status = $request->query('status');
        $day1Check = $request->query('day1_check');
        $day2Check = $request->query('day2_check');
        $day3Check = $request->query('day3_check');
        $day4Check = $request->query('day4_check');
        $day5Check = $request->query('day5_check');
        if(!empty($attrGroup)) array_filter($attrGroup);
        if(!empty($attrValue)) array_filter($attrValue);

        $query = StockLedger::query()
            ->withDefaultGroupCompanyOrg()
            ->whereNull('utilized_id')
            ->where('transaction_type', 'receipt');

        $query->with(['item', 'item.category', 'item.subCategory', 'location', 'store', 'station', 'wipStation', 'inventoryUom']);

        // Item filters
        $query->whereHas('item', function($q) use ($itemId, $categoryId, $subCategoryId, $mCategoryId, $mSubCategoryId) {
            if ($itemId) {
                $q->where('id', $itemId);
            }
            if ($categoryId) {
                $q->where('category_id', $categoryId);
            }
            if ($subCategoryId) {
                $q->where('subcategory_id', $subCategoryId);
            }
            if ($mCategoryId) {
                $q->where('category_id', $mCategoryId);
            }
            if ($mSubCategoryId) {
                $q->where('subcategory_id', $mSubCategoryId);
            }
        });

        // Add filters for stores, racks, bins, etc.
        if ($storeCheck) { $query->groupBy(['store_id']); }
        if ($subLocationCheck) { $query->groupBy(['sub_store_id']); }
        if ($stockTypeCheck) { $query->groupBy(['stock_type']); }
        if ($stationCheck) { $query->groupBy(['station_id']); }

        if ($store) { $query->where('store_id', $store)->groupBy(['store_id']); }
        if ($subStore) { $query->where('sub_store_id', $subStore)->groupBy(['sub_store_id']); }
        if ($stockType) { $query->where('stock_type', $stockType)->groupBy(['stock_type']); }
        if ($station) { $query->where('stock_type', $station)->groupBy(['station_id']); }
        // Attribute filtering
        if (!empty($attrGroup) && !empty($attrValue)) {
            foreach ($attrGroup as $key => $group) {
                if (!empty($attrValue[$key])) {
                    $query->where(function ($subQuery) use ($group, $attrValue, $key) {
                        $subQuery->whereJsonContains('item_attributes', [
                            'attr_name' => $group,
                            'attr_value' => $attrValue[$key]
                        ]);
                    });
                }
            }
        }

        // Date filters
        if (($startDate && $endDate) || $period) {
            if (!$startDate || !$endDate) {
                switch ($period) {
                    case 'this-month':
                        $startDate = Carbon::now()->startOfMonth();
           $endDate = Carbon::now()->endOfMonth();
                        break;
                    case 'last-month':
                        $startDate = Carbon::now()->subMonth()->startOfMonth();
                        $endDate = Carbon::now()->subMonth()->endOfMonth();
                        break;
                    case 'this-year':
                        $startDate = Carbon::now()->startOfYear();
                        $endDate = Carbon::now()->endOfYear();
                        break;
                }
            }
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        $query->select('stock_ledger.*')
            ->selectRaw('SUM(CASE WHEN document_status IN (?, ?, ?) THEN (receipt_qty - reserved_qty) ELSE 0 END) as confirmed_stock',
                ['approved', 'approval_not_required', 'posted']
            )
            ->selectRaw('SUM(CASE WHEN document_status NOT IN (?, ?, ?) THEN receipt_qty ELSE 0 END) as unconfirmed_stock',
                ['approved', 'approval_not_required', 'posted']
            )
            ->selectRaw('SUM(CASE WHEN document_status IN (?, ?, ?) THEN putaway_pending_qty ELSE 0 END) as putaway_pending_qty',
                ['approved', 'approval_not_required', 'posted']
            )
            ->selectRaw('SUM(CASE WHEN document_status IN (?, ?, ?) THEN reserved_qty ELSE 0 END) as reserved_qty',
                ['approved', 'approval_not_required', 'posted']
            )
            ->selectRaw('SUM(CASE WHEN document_status IN (?, ?, ?) THEN org_currency_cost ELSE 0 END) as confirmed_stock_value',
                ['approved', 'approval_not_required', 'posted']
            )
            ->selectRaw('SUM(CASE WHEN document_status NOT IN (?, ?, ?) THEN org_currency_cost ELSE 0 END) as unconfirmed_stock_value',
                ['approved', 'approval_not_required', 'posted']
            )
            ->groupBy(['inventory_uom_id']);
        $now = Carbon::now();
        if ($day1Check) {
            $tenDaysAgo = $now->copy()->subDays($day1Check)->format('Y-m-d');
            $query->addSelect(DB::raw("SUM(CASE WHEN created_at >= '$tenDaysAgo' THEN receipt_qty ELSE 0 END) as confirmed_stock_day1_days"));
        }

        if ($day2Check) {
            $fifteenDaysAgo = $now->copy()->subDays($day2Check)->format('Y-m-d');
            $fifteenDaysAgo2 = $now->copy()->subDays( ($day1Check+1))->format('Y-m-d');
            $query->addSelect(DB::raw("SUM(CASE WHEN created_at >= '$fifteenDaysAgo' and created_at <= '$fifteenDaysAgo2'  THEN receipt_qty ELSE 0 END) as confirmed_stock_day2_days"));
        }

        if ($day3Check) {
            $twentyDaysAgo = $now->copy()->subDays($day3Check)->format('Y-m-d');
            $twentyDaysAgo2 = $now->copy()->subDays(($day2Check+1))->format('Y-m-d');
            $query->addSelect(DB::raw("SUM(CASE WHEN created_at >= '$twentyDaysAgo' and created_at <= '$twentyDaysAgo2' THEN receipt_qty ELSE 0 END) as confirmed_stock_day3_days"));
        }

        if ($day4Check) {
            $fifteenDaysAgo = $now->copy()->subDays($day4Check)->format('Y-m-d');
            $fifteenDaysAgo2 = $now->copy()->subDays( ($day3Check+1))->format('Y-m-d');
            $query->addSelect(DB::raw("SUM(CASE WHEN created_at >= '$fifteenDaysAgo' and created_at <= '$fifteenDaysAgo2'  THEN receipt_qty ELSE 0 END) as confirmed_stock_day4_days"));
        }

        if ($day5Check) {
            $twentyDaysAgo = $now->copy()->subDays($day5Check)->format('Y-m-d');
            $twentyDaysAgo2 = $now->copy()->subDays(($day4Check+1))->format('Y-m-d');
            $query->addSelect(DB::raw("SUM(CASE WHEN created_at >= '$twentyDaysAgo' and created_at <= '$twentyDaysAgo2' THEN receipt_qty ELSE 0 END) as confirmed_stock_day5_days"));
            $query->addSelect(DB::raw("SUM(CASE WHEN created_at < '$twentyDaysAgo' THEN receipt_qty ELSE 0 END) as confirmed_stock_more_than_day5_days"));
        }

        // Attributes Check
        $query->groupBy('item_id');

        if($attributesCheck) {
            // Group by item ID
            $query->groupBy('item_attributes');
        }

        // Fetch the results
        $inventory_reports = $query->get();
        // $inventory_reports->each(function ($item) {
        //     $item->item_attributes = $item -> item_attributes_array();
        // });

        return response()->json($inventory_reports);
    }
    public function getAttributeValues(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $attributeValues = array();
        $attributeGroup = ErpAttributeGroup::find($request->attribute_name);
        if($attributeGroup){
            // Fetch attributeValues
            $attributeValues = ErpAttribute::where('attribute_group_id', $attributeGroup->id)
                ->pluck('value', 'id');
        }
        // Return data as JSON
        return response()->json([
            'attributeValues' => $attributeValues
        ]);
    }

    public function getItemAttributes(Request $request)
    {
        $attributeGroups = AttributeGroup::with('attributes')->where('status', ConstantHelper::ACTIVE)->get();
        $item = Item::find($request->item_id);
        $selectedAttr = [];
        $html = view('procurement.inventory-report.partials.comp-attribute',compact('item','attributeGroups','selectedAttr'))->render();
        $hiddenHtml = '';
        foreach ($item->itemAttributes as $attribute) {
                $selected = '';
                foreach ($attribute->attributes() as $value){
                    if (in_array($value->id, $selectedAttr)){
                        $selected = $value->id;
                    }
                }
            $hiddenHtml .= "<input type='hidden' name='[attr_group_id][$attribute->attribute_group_id][attr_name]' value=$selected>";
        }
        return response()->json(['data' => ['html' => $html, 'hiddenHtml' => $hiddenHtml], 'status' => 200, 'message' => 'fetched.']);
    }

    public function detailedReports(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $users = AuthUser::where('organization_id', Helper::getAuthenticatedUser()->organization_id)
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
        $query = StockLedger::query()
                ->withDefaultGroupCompanyOrg()
                ->with(['book', 'item', 'location', 'store', 'so', 'station', 'wipStation', 'inventoryUom']);
        $items = Item::orderBy('id', 'ASC')
                ->withDefaultGroupCompanyOrg()
                ->get();
        $erpStores = ErpStore::withDefaultGroupCompanyOrg()
            ->orderBy('id', 'DESC')
            ->get();
        $attributeGroups = ErpAttributeGroup::orderBy('id', 'DESC')
            ->get();

        $bookTypes = StockLedger::distinct()->pluck('book_type');

        $hasFilters = false;
        $selectedItem = '';
        $statusCss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST;

        // Conditionally apply filters based on the request parameters
        if($request->has('item') && !empty($request->item)) {
            $query->where('item_id', $request->item);
            $selectedData = Item::where('id', $request->item)->first();
            $selectedItem = $selectedData->item_name;
            $hasFilters = true;
        }

        if ($request->has('doc_no') && !empty($request->doc_no)) {
            $query->where('document_number', 'like', '%' . $request->doc_no . '%');
            $hasFilters = true;
        }

        if($request->has('store_id') && !empty($request->store_id)) {
            $query->where('store_id', $request->store_id);
            $hasFilters = true;
        }

        if($request->has('sub_store_id') && !empty($request->sub_store_id)) {
            $query->where('sub_store_id', $request->sub_store_id);
            $hasFilters = true;
        }

        if($request->has('station_id') && !empty($request->station_id)) {
            $query->where('station_id', $request->station_id);
            $hasFilters = true;
        }

        if($request->has('stock_type') && !empty($request->stock_type)) {
            $query->where('stock_type', $request->stock_type);
            $hasFilters = true;
        }

        if($request->has('bin_id') && !empty($request->bin_id)) {
            $query->where('bin_id', $request->bin_id);
            $hasFilters = true;
        }

        if($request->has('shelf_id') && !empty($request->shelf_id)) {
            $query->where('shelf_id', $request->shelf_id);
            $hasFilters = true;
        }

        if ($request->has('type_of_stock_id') && !empty($request->type_of_stock_id)) {
            if($request->type_of_stock_id == 'confirmed_stock')
            {
                $query->whereIn('document_status', ['approved', 'approval_not_required', 'posted']);
            }
            else
            {
                $query->whereNotIn('document_status', ['approved', 'approval_not_required', 'posted']);
            }
        }

        $attrGroup = $request->query('attribute_name');
        $attrValue = $request->query('attribute_value');

        if(!empty($attrGroup)) array_filter($attrGroup);
        if(!empty($attrValue)) array_filter($attrValue);

        if (!empty($attrGroup) && !empty($attrValue)) {
            foreach ($attrGroup as $key => $group) {
                if (!empty($attrValue[$key])) {
                    $query->where(function ($subQuery) use ($group, $attrValue, $key) {
                        $subQuery->whereJsonContains('item_attributes', [
                            'attr_name' => $group,
                            'attr_value' => $attrValue[$key]
                        ]);
                    });
                }
                $hasFilters = true;
            }
        }

        if($request->has('item_attributes') && !empty($request->item_attributes))
        {
            $attributeGroup = $request->item_attributes;
            // Check if the value is a JSON string and decode it if needed
            if (is_string($attributeGroup)) {
                $attributeGroup = json_decode($attributeGroup, true); // Convert JSON string to an associative array
            }
            if (!empty($attributeGroup)) {
                foreach ($attributeGroup as $key => $group) {
                    // Ensure index exists and handle type consistency
                    $query->whereJsonContains('item_attributes', [
                        'attr_name' => (string)$group['attr_name'],
                        'attr_value' => (string)$group['attr_value']
                    ]);
                }
                $hasFilters = true;
            }
        }

        $query->selectRaw('*,
            SUM(CASE WHEN transaction_type = "receipt" THEN receipt_qty ELSE 0 END) as receipt_qty,
            SUM(CASE WHEN transaction_type = "issue" THEN issue_qty ELSE 0 END) as issue_qty,
            SUM(CASE WHEN transaction_type = "receipt" THEN org_currency_cost ELSE 0 END) as receipt_org_currency_cost,
            SUM(CASE WHEN transaction_type = "issue" THEN org_currency_cost ELSE 0 END) as issue_org_currency_cost')
            ->groupBy(['document_header_id', 'document_detail_id', 'book_type', 'transaction_type', 'lot_number', 'stock_type', 'inventory_uom_id']);

        if (!$hasFilters) {
            $records = [];
        } else {
            $records = $query->get();
            // $records->each(function ($item) {
            //     $item->item_attributes = $item -> item_attributes_array();
            // });
            $records = $records->toArray();
        }
        $subStoreLocType = ConstantHelper::ERP_SUB_STORE_LOCATION_TYPES;
        return view('procurement.inventory-report.detailed_report', [
            'user' => $user,
            'records' => $records,
            'items' => $items,
            'selectedItem' => $selectedItem,
            'erpStores' => $erpStores,
            'attributeGroups' => $attributeGroups,
            'bookTypes' => $bookTypes,
            'statusCss' => $statusCss,
            'users' => $users,
            'subStoreLocType' => $subStoreLocType
        ]);
    }

    public function detailedReportFilter(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $users = AuthUser::where('organization_id', Helper::getAuthenticatedUser()->organization_id)
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
        $query = StockLedger::query()
                ->withDefaultGroupCompanyOrg()
                ->with(['book', 'item', 'location', 'store', 'so', 'station', 'wipStation', 'inventoryUom']);
        $bookTypes = StockLedger::distinct()->pluck('book_type');
        $items = Item::orderBy('id', 'ASC')
                ->withDefaultGroupCompanyOrg()
                ->get();
        $erpStores = ErpStore::withDefaultGroupCompanyOrg()
            ->orderBy('id', 'DESC')
            ->get();
        $attributeGroups = ErpAttributeGroup::orderBy('id', 'DESC')
            ->get();

        // Check if the request contains any valid filter keys
        $hasFilters = false;
        $selectedItem = '';
        $statusCss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST;

        // If 'item' is present, apply the filter
        if ($request->has('item') && !empty($request->item)) {
            $query->where('item_id', $request->item);
            $hasFilters = true;
        }

        if ($request->has('doc_no') && !empty($request->doc_no)) {
            $query->where('document_number', 'like', '%' . $request->doc_no . '%');
            $hasFilters = true;
        }

        if ($request->has('store_id') && !empty($request->store_id)) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->has('sub_store_id') && !empty($request->sub_store_id)) {
            $query->where('sub_store_id', $request->sub_store_id);
        }

        if ($request->has('stock_type') && !empty($request->stock_type)) {
            $query->where('stock_type', $request->stock_type);
        }

        if($request->has('station_id') && !empty($request->station_id)) {
            $query->where('station_id', $request->station_id);
            $hasFilters = true;
        }

        if ($request->has('type_of_stock_id') && !empty($request->type_of_stock_id)) {
            if($request->type_of_stock_id == 'confirmed_stock')
            {
                $query->whereIn('document_status', ['approved', 'approval_not_required', 'posted']);
            }
            else
            {
                $query->whereNotIn('document_status', ['approved', 'approval_not_required', 'posted']);
            }
        }
        // Handle attribute filters
        $attrGroup = $request->query('attribute_name');
        $attrValue = $request->query('attribute_value');

        if (!empty($attrGroup)) array_filter($attrGroup);
        if (!empty($attrValue)) array_filter($attrValue);
        if (!empty($attrGroup) && !empty($attrValue)) {
            foreach ($attrGroup as $key => $group) {
                if (!empty($attrValue[$key])) {
                    $query->where(function ($subQuery) use ($group, $attrValue, $key) {
                        $subQuery->whereJsonContains('item_attributes', [
                            'attr_name' => $group,
                            'attr_value' => $attrValue[$key]
                        ]);
                    });
                }
            }
        }

        $query->selectRaw('*,
            SUM(CASE WHEN transaction_type = "receipt" THEN receipt_qty ELSE 0 END) as receipt_qty,
            SUM(CASE WHEN transaction_type = "issue" THEN issue_qty ELSE 0 END) as issue_qty,
            SUM(CASE WHEN transaction_type = "receipt" THEN org_currency_cost ELSE 0 END) as receipt_org_currency_cost,
            SUM(CASE WHEN transaction_type = "issue" THEN org_currency_cost ELSE 0 END) as issue_org_currency_cost')
            ->groupBy(['document_header_id', 'document_detail_id', 'book_type', 'transaction_type', 'lot_number', 'stock_type', 'inventory_uom_id']);

        // If no valid filters were applied, return an empty JSON response
        if (!$hasFilters) {
            return response()->json([]);
        }
        // Fetch the filtered records and return as JSON
        $records = $query->get();
        // $records->each(function ($item) {
        //     $item->item_attributes = $item -> item_attributes_array();
        // });
        // return response()->json($records);
        if ($request->ajax()) {
            return response()->json($records);
        }
        $subStoreLocType = ConstantHelper::ERP_SUB_STORE_LOCATION_TYPES;
        // Otherwise, return the full HTML page
        return view('procurement.inventory-report.detailed_report', [
            'user' => $user,
            'records' => $records,
            'items' => $items,
            'erpStores' => $erpStores,
            'attributeGroups' => $attributeGroups,
            'bookTypes' => $bookTypes,
            'selectedItem' => $selectedItem,
            'statusCss' => $statusCss,
            'users' => $users,
            'subStoreLocType' => $subStoreLocType
        ]);
    }

    public function summaryReport(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $users = AuthUser::where('organization_id', Helper::getAuthenticatedUser()->organization_id)
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
        $query = StockLedger::query()
                ->withDefaultGroupCompanyOrg()
                ->with(['book', 'item', 'location', 'store', 'so', 'station', 'wipStation', 'inventoryUom']);
        $items = Item::orderBy('id', 'ASC')
                ->withDefaultGroupCompanyOrg()
                ->get();
        $erpStores = ErpStore::withDefaultGroupCompanyOrg()
            ->orderBy('id', 'DESC')
            ->get();
        $attributeGroups = ErpAttributeGroup::orderBy('id', 'DESC')
            ->get();

        $bookTypes = StockLedger::distinct()->pluck('book_type');

        $statusCss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST;

        // Conditionally apply filters based on the request parameters
        if($request->has('item') && !empty($request->item)) {
            $query->where('item_id', $request->item);
        }

        if ($request->has('doc_no') && !empty($request->doc_no)) {
            $query->where('document_number', 'like', '%' . $request->doc_no . '%');
        }

        if($request->has('store_id') && !empty($request->store_id)) {
            $query->where('store_id', $request->store_id);
        }

        if($request->has('sub_store_id') && !empty($request->sub_store_id)) {
            $query->where('sub_store_id', $request->sub_store_id);
        }

        if($request->has('station_id') && !empty($request->station_id)) {
            $query->where('station_id', $request->station_id);
            $hasFilters = true;
        }

        if($request->has('stock_type') && !empty($request->stock_type)) {
            $query->where('stock_type', $request->stock_type);
        }

        if($request->has('book_type_id') && !empty($request->book_type_id)) {
            $query->where('book_type', $request->book_type_id);
        }

        if ($request->has('type_of_stock_id') && !empty($request->type_of_stock_id)) {
            if($request->type_of_stock_id == 'confirmed_stock')
            {
                $query->whereIn('document_status', ['approved', 'approval_not_required', 'posted']);
            }
            else
            {
                $query->whereNotIn('document_status', ['approved', 'approval_not_required', 'posted']);
            }
        }


        $attrGroup = $request->query('attribute_name');
        $attrValue = $request->query('attribute_value');

        if(!empty($attrGroup)) array_filter($attrGroup);
        if(!empty($attrValue)) array_filter($attrValue);

        if (!empty($attrGroup) && !empty($attrValue)) {
            foreach ($attrGroup as $key => $group) {
                if (!empty($attrValue[$key])) {
                    $query->where(function ($subQuery) use ($group, $attrValue, $key) {
                        $subQuery->whereJsonContains('item_attributes', [
                            'attr_name' => $group,
                            'attr_value' => $attrValue[$key]
                        ]);
                    });
                }
            }
        }

        if($request->has('item_attributes') && !empty($request->item_attributes))
        {
            $attributeGroup = $request->item_attributes;
            if (is_string($attributeGroup)) {
                $attributeGroup = json_decode($attributeGroup, true);
            }
            if (!empty($attributeGroup)) {
                foreach ($attributeGroup as $key => $group) {
                    $query->whereJsonContains('item_attributes', [
                        'attr_name' => (string)$group['attr_name'],
                        'attr_value' => (string)$group['attr_value']
                    ]);
                }
            }
        }

        $query->selectRaw('*,
            SUM(CASE WHEN transaction_type = "receipt" THEN receipt_qty ELSE 0 END) as receipt_qty,
            SUM(CASE WHEN transaction_type = "issue" THEN issue_qty ELSE 0 END) as issue_qty,
            SUM(CASE WHEN transaction_type = "receipt" THEN org_currency_cost ELSE 0 END) as receipt_org_currency_cost,
            SUM(CASE WHEN transaction_type = "issue" THEN org_currency_cost ELSE 0 END) as issue_org_currency_cost')
            ->groupBy(['document_header_id', 'document_detail_id', 'book_type', 'transaction_type', 'lot_number', 'stock_type', 'inventory_uom_id']);

        $records = $query->get()->toArray();
        $subStoreLocType = ConstantHelper::ERP_SUB_STORE_LOCATION_TYPES;

        return view('procurement.inventory-report.summary_report', [
            'user' => $user,
            'records' => $records,
            'items' => $items,
            'erpStores' => $erpStores,
            'attributeGroups' => $attributeGroups,
            'bookTypes' => $bookTypes,
            'statusCss' => $statusCss,
            'users' => $users,
            'subStoreLocType' => $subStoreLocType
        ]);
    }

    public function summaryReportFilter(Request $request)
    {
        // dd($request->all());
        $user = Helper::getAuthenticatedUser();
        $users = AuthUser::where('organization_id', Helper::getAuthenticatedUser()->organization_id)
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
        $query = StockLedger::query()
                ->withDefaultGroupCompanyOrg()
                ->with(['book', 'item', 'location', 'store', 'so', 'station', 'wipStation', 'inventoryUom']);
        $items = Item::orderBy('id', 'ASC')
                ->withDefaultGroupCompanyOrg()
                ->get();
        $erpStores = ErpStore::withDefaultGroupCompanyOrg()
            ->orderBy('id', 'DESC')
            ->get();
        $attributeGroups = ErpAttributeGroup::orderBy('id', 'DESC')
            ->get();

        $bookTypes = StockLedger::distinct()->pluck('book_type');

        $statusCss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST;

        if ($request->has('item') && !empty($request->item)) {
            $query->where('item_id', $request->item);
        }

        if ($request->has('doc_no') && !empty($request->doc_no)) {
            $query->where('document_number', 'like', '%' . $request->doc_no . '%');
        }

        if ($request->has('store_id') && !empty($request->store_id)) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->has('sub_store_id') && !empty($request->sub_store_id)) {
            $query->where('sub_store_id', $request->sub_store_id);
        }

        if($request->has('station_id') && !empty($request->station_id)) {
            $query->where('station_id', $request->station_id);
            $hasFilters = true;
        }

        if($request->has('book_type_id') && !empty($request->book_type_id)) {
            $query->where('book_type', $request->book_type_id);
        }

        if ($request->has('stock_type') && !empty($request->stock_type)) {
            $query->where('stock_type', $request->stock_type);
        }

        if ($request->has('type_of_stock_id') && !empty($request->type_of_stock_id)) {
            if($request->type_of_stock_id == 'confirmed_stock')
            {
                $query->whereIn('document_status', ['approved', 'approval_not_required', 'posted']);
            }
            else
            {
                $query->whereNotIn('document_status', ['approved', 'approval_not_required', 'posted']);
            }
        }
        // Handle attribute filters
        $attrGroup = $request->query('attribute_name');
        $attrValue = $request->query('attribute_value');

        if (!empty($attrGroup)) array_filter($attrGroup);
        if (!empty($attrValue)) array_filter($attrValue);
        if (!empty($attrGroup) && !empty($attrValue)) {
            foreach ($attrGroup as $key => $group) {
                if (!empty($attrValue[$key])) {
                    $query->where(function ($subQuery) use ($group, $attrValue, $key) {
                        $subQuery->whereJsonContains('item_attributes', [
                            'attr_name' => $group,
                            'attr_value' => $attrValue[$key]
                        ]);
                    });
                }
            }
        }

        $query->selectRaw('*,
            SUM(CASE WHEN transaction_type = "receipt" THEN receipt_qty ELSE 0 END) as receipt_qty,
            SUM(CASE WHEN transaction_type = "issue" THEN issue_qty ELSE 0 END) as issue_qty,
            SUM(CASE WHEN transaction_type = "receipt" THEN org_currency_cost ELSE 0 END) as receipt_org_currency_cost,
            SUM(CASE WHEN transaction_type = "issue" THEN org_currency_cost ELSE 0 END) as issue_org_currency_cost')
            ->groupBy(['document_header_id', 'document_detail_id', 'book_type', 'transaction_type', 'lot_number', 'stock_type', 'inventory_uom_id']);

        $records = $query->get();
        // $records->each(function ($item) {
        //     $item->item_attributes = $item -> item_attributes_array();
        // });
        // return response()->json($records);
        if ($request->ajax()) {
            return response()->json($records);
        }
        $subStoreLocType = ConstantHelper::ERP_SUB_STORE_LOCATION_TYPES;
        // Otherwise, return the full HTML page
        return view('procurement.inventory-report.summary_report', [
            'user' => $user,
            'records' => $records,
            'items' => $items,
            'erpStores' => $erpStores,
            'attributeGroups' => $attributeGroups,
            'bookTypes' => $bookTypes,
            'statusCss' => $statusCss,
            'users' => $users,
            'subStoreLocType' => $subStoreLocType
        ]);
    }

    public function rearrangeInventoryReportData($data)
    {
        $closingBalance = [];
        $totalData = [];
        foreach ($data as $key => $item) {
            if (isset($item[8]) && $item[8] == "Closing Balance") {
                $closingBalance[] = $item;
                unset($data[$key]);
            }

            if (isset($item[11]) && strpos($item[11], "Total:") === 0) {
                $totalData[] = $item;
                unset($data[$key]);
            }
        }

        if (!empty($totalData)) {
            array_splice($data, count($data), 0, $totalData);
        }

        if (!empty($closingBalance)) {
            array_splice($data, count($data) - 1, 0, $closingBalance);
        }

        return $data;
    }

    public function addScheduler(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email_to' => ['required', 'array', 'min:1'],
                'email_to.*' => ['required', 'email'],
                'email_cc' => ['nullable', 'array'],
                'email_cc.*' => ['required_with:email_cc', 'email'],
            ],
            [
                'email_to.required' => 'At least one recipient email is required.',
                'email_to.array' => 'The recipient emails must be provided as an array.',
                'email_to.min' => 'At least one email address must be specified in email_to.',
                'email_to.*.required' => 'Each email address in email_to is required.',
                'email_to.*.email' => 'Each email in email_to must be a valid email address.',

                'email_cc.array' => 'The CC emails must be provided as an array.',
                'email_cc.*.required_with' => 'Each email in email_cc is required if CC is present.',
                'email_cc.*.email' => 'Each email in email_cc must be a valid email address.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator -> messages() -> first()
                ], 422);
            }
            $headers = $request->input('displayedHeaders');
            $originalData = $request->input('displayedData');
            $data = $this->rearrangeInventoryReportData($originalData);
            $filters_json = $request->input('filter_json');
            $reportType = $request->input('report_type');
            $itemName = '';
            $storeName = '';
            $subStoreName = '';
            $stockType = '';
            $startDate = new DateTime($request->input('start_date'));
            $endDate = new DateTime($request->input('end_date'));
            $formattedstartDate = $startDate->format('d-m-y');
            $formattedendDate = $endDate->format('d-m-y');
            if ($request->filled('store_id'))
            {
                $storeData = ErpStore::find($request->input('store_id'));
                $storeName = optional($storeData)->store_name;
            }
            if ($request->filled('sub_store_id'))
            {
                $subStoreData = ErpSubStore::find($request->input('sub_store_id'));
                $subStoreName = optional($subStoreData)->name;
            }
            if (!empty($filters_json['item'])) {
                $itemData = Item::find($filters_json['item']);
                $itemName = $itemData->item_name;
            }

            if (!empty($filters_json['store_id'])) {
                $storeData = ErpStore::find($filters_json['store_id']);
                $storeName = $storeData->store_name;
            }

            if (!empty($filters_json['sub_store_id'])) {
                $subStoreData = ErpSubStore::find($filters_json['sub_store_id']);
                $subStoreName = $subStoreData->name;
            }

            if (!empty($filters_json['type_of_stock_id'])) {
                $stockType = $filters_json['type_of_stock_id'];
            }

            $blankSpaces = count($headers) - 1;
            $centerPosition = (int)floor($blankSpaces / 2);
            $filters = [
                'Filters',
                'Item: ' . $itemName,
                'Store: ' . $storeName,
                'Sub Store: ' . $subStoreName,
                'Stock Type: ' . $stockType,
            ];

            if($reportType == 'report')
            {
                $fileName = 'inventory_report.xlsx';
                $filePath = storage_path('app/public/inventory-report/' . $fileName);
                $directoryPath = storage_path('app/public/inventory-report');
                $customHeader = array_merge(
                    array_fill(0, $centerPosition, ''),
                    ['Inventory Report' ],
                    array_fill(0, $blankSpaces - $centerPosition, '')
                );
            }
            else if($reportType == 'detailed')
            {
                $fileName = 'detailed_report.xlsx';
                $filePath = storage_path('app/public/detailed-report/' . $fileName);
                $directoryPath = storage_path('app/public/detailed-report');
                $customHeader = array_merge(
                    array_fill(0, $centerPosition, ''),
                    ['Detailed Report(From '.$formattedstartDate.' to '.$formattedendDate.')' ],
                    array_fill(0, $blankSpaces - $centerPosition, '')
                );
            }
            else if($reportType == 'summary')
            {
                $fileName = 'summary_report.xlsx';
                $filePath = storage_path('app/public/summary-report/' . $fileName);
                $directoryPath = storage_path('app/public/summary-report');
                $customHeader = array_merge(
                    array_fill(0, $centerPosition, ''),
                    ['Summarized Report(From '.$formattedstartDate.' to '.$formattedendDate.')' ],
                    array_fill(0, $blankSpaces - $centerPosition, '')
                );
            }

            $remainingSpaces = $blankSpaces - count($filters) + 1;
            $filterHeader = array_merge($filters, array_fill(0, $remainingSpaces, ''));

            $excelData = Excel::raw(new InventoryReportExport($customHeader, $filterHeader, $headers, $data), \Maatwebsite\Excel\Excel::XLSX);

            if (!file_exists($directoryPath)) {
                mkdir($directoryPath, 0755, true);
            }
            file_put_contents($filePath, $excelData);
            if (!file_exists($filePath)) {
                throw new \Exception('File does not exist at path: ' . $filePath);
            }
            $email_to = $request->email_to ?? [];
            $email_cc = $request->email_cc ?? [];

            foreach($email_to as $email)
            {
                $user = AuthUser::where('email', $email)
                ->where('organization_id', Helper::getAuthenticatedUser()->organization_id)
                ->where('status', ConstantHelper::ACTIVE)
                ->get();

                if ($user->isEmpty()) {
                    $user = new AuthUser();
                    $user->email = $email;
                }
                if($reportType == 'report')
                {
                    $title = "Inventory Report Generated";
                    $heading = "Inventory Report";
                }
                else if($reportType == 'detailed')
                {
                    $title = "Detailed Report Generated";
                    $heading = "Detailed Report";
                }
                else if($reportType == 'summary')
                {
                    $title = "Summarized Report Generated";
                    $heading = "Summarized Report";
                }

                $remarks = $request->remarks ?? null;
                $mail_from = '';
                $mail_from_name = '';
                $cc = implode(', ', $email_cc);
                $bcc = null;
                $attachment = $filePath ?? null;
                $description = <<<HTML
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); font-family: Arial, sans-serif; line-height: 1.6;">
                    <tr>
                        <td>
                            <h2 style="color: #2c3e50; font-size: 24px; margin-bottom: 20px;">{$heading}</h2>
                            <p style="font-size: 16px; color: #555; margin-bottom: 20px;">
                                Dear <strong style="color: #2c3e50;">user</strong>,
                            </p>

                            <p style="font-size: 15px; color: #333; margin-bottom: 20px;">
                                We hope this email finds you well. Please find your inventory report attached below.
                            </p>
                            <p style="font-size: 15px; color: #333; margin-bottom: 30px;">
                                <strong>Remark:</strong> {$remarks}
                            </p>
                            <p style="font-size: 14px; color: #777;">
                                If you have any questions or need further assistance, feel free to reach out to us.
                            </p>
                        </td>
                    </tr>
                </table>
                HTML;
                self::sendMail($user,$title,$description,$cc,$bcc, $attachment,$mail_from,$mail_from_name);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'emails sent successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }


    }
    public function sendMail($receiver, $title, $description, $cc= null, $bcc= null, $attachment, $mail_from=null, $mail_from_name=null)
    {
        if (!$receiver || !isset($receiver->email)) {
            return "Error: Receiver details are missing or invalid.";
        }

        dispatch(new SendEmailJob($receiver, $mail_from, $mail_from_name,$title,$description,$cc,$bcc, $attachment));
        return response() -> json([
            'status' => 'success',
            'message' => 'Email request sent succesfully',
        ]);

    }

    public function getSingleItemData(Request $request)
    {
        $items = Item::where('id', $request->item_id)
            ->withDefaultGroupCompanyOrg()
            ->get();
        return $items ? ['name' => isset($items[0]) ? $items[0]->item_name: null] : response()->json(['error' => 'Not found'], 404);
    }
}
