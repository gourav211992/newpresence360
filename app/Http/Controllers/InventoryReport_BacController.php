<?php
namespace App\Http\Controllers;

use Auth;
use PDF;
use DB;
use View;
use Session;
use DataTables;
use Carbon\Carbon;

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
use App\Helpers\TaxHelper;
use App\Helpers\NumberHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\InventoryHelper;
use App\Models\ErpAttribute;
use App\Models\AttributeGroup;
use App\Models\ErpAttributeGroup;
use App\Models\PoItem;
use App\Models\ErpStore;
use App\Models\Category;
use App\Services\MrnService;
use Illuminate\Http\Exceptions\HttpResponseException;


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
            ->get();
        $erpStores = ErpStore::where('organization_id', $user->organization_id)
            ->orderBy('id', 'DESC')
            ->get();
        $attributeGroups = ErpAttributeGroup::orderBy('id', 'DESC')
            ->get();
        // return $records;        
        return view('procurement.inventory-report.report',
            compact(
                'user',
                'items',
                'erpStores',
                'attributeGroups',
                'categories', 
                'sub_categories', 
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
        $store = $request->query('store_id');
        $rack = $request->query('rack_id');
        $shelf = $request->query('shelf_id');
        $bin = $request->query('bin_id');
        $storeCheck = $request->query('store_check');
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
            ->where('organization_id', $user->organization_id)
            ->whereNull('utilized_id');

            $query->with(['attributes' => function($query) use ($attributesCheck){
                $query->with(['attributeName', 'attributeValue']);
            },
            'item', 'item.category', 'item.subCategory'
        ]);

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
        if ($rackCheck) { $query->groupBy(['rack_id']); }
        if ($shelfCheck) { $query->groupBy(['shelf_id']); }
        if ($binCheck) { $query->groupBy(['bin_id']); }

        if ($store) { $query->where('store_id', $store)->groupBy(['store_id']); }
        if ($rack) { $query->where('rack_id', $rack)->groupBy(['rack_id']); }
        if ($shelf) { $query->where('shelf_id', $shelf)->groupBy(['shelf_id']); }
        if ($bin) { $query->where('bin_id', $bin)->groupBy(['bin_id']); }

        // dd([$attrGroup, $attrValue]);

        // $atCount = 0;
        // if ($attrGroup || $attrValue) {
        //     $query->whereHas('attributes', function ($q1) use ($attrGroup, $attrValue, &$atCount) {
        

        //         foreach ($attrValue as $key => $value) {   
        //             if(!$value) continue;

        //             if($atCount == 0) {
        //                 $q1->where(function ($q2) use ($attrGroup, $value, $key) {
        //                     $q2->where('attribute_name', $attrGroup[$key])
        //                         ->where('attribute_value', $value);
        //                 });
        //             }    
        //             else {
        //                 $q1->orWhere(function ($q2) use ($attrGroup, $value, $key) {
        //                     $q2->where('attribute_name', $attrGroup[$key])
        //                         ->where('attribute_value', $value);
        //                 });
                        
        //             }    
        //             $atCount ++;      
        //     }


        //     }, '>=', $atCount);

        // }

        // dd($atCount);

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
       

        if($attributesCheck) {
            $query->select(
                'stock_ledger.*',
                DB::raw('SUM(CASE WHEN document_status = "approved" THEN receipt_qty ELSE 0 END) as confirmed_stock'),
                DB::raw('SUM(CASE WHEN document_status != "approved" THEN receipt_qty ELSE 0 END) as unconfirmed_stock')
            );
            
            if ($attrGroup && $attrValue) {

                $query->where(function ($q1) use ($attrGroup, $attrValue) {
                    foreach ($attrValue as $key => $value) {
                        if (!$value) continue;
                
                        // Ensure all attribute_name and attribute_value pairs match collectively
                        $q1->whereExists(function ($subQuery) use ($attrGroup, $key, $value) {
                            $subQuery->select(DB::raw(1))
                                ->from('stock_ledger_item_attributes')
                                ->whereColumn('stock_ledger.id', 'stock_ledger_item_attributes.stock_ledger_id')
                                ->where('stock_ledger_item_attributes.attribute_name', $attrGroup[$key])
                                ->where('stock_ledger_item_attributes.attribute_value', $value);
                        });
                    }
                });

                // $query->where(function ($q1) use ($attrGroup, $attrValue) {
                //     $atCount = 0;
            
                //     foreach ($attrValue as $key => $value) {
                //         if (!$value) continue;
            
                //         if ($atCount == 0) {
                //             // First attribute filter
                //             $q1->where(function ($q2) use ($attrGroup, $value, $key) {
                //                 $q2->where('stock_ledger_item_attributes.attribute_name', $attrGroup[$key])
                //                     ->where('stock_ledger_item_attributes.attribute_value', $value);
                //             });
                //         } else {
                //             // Additional attribute filters
                //             $q1->where(function ($q2) use ($attrGroup, $value, $key) {
                //                 $q2->where('stock_ledger_item_attributes.attribute_name', $attrGroup[$key])
                //                     ->where('stock_ledger_item_attributes.attribute_value', $value);
                //             });
                //         }
            
                //         $atCount++;
                //     }
                // });
            }
            
            $query->groupBy(
                // 'stock_ledger_item_attributes.attribute_value',
                // 'stock_ledger_item_attributes.attribute_name',
                // 'stock_ledger.item_id',
                'stock_ledger.sku_code'
            );
        }
        else {
            $query->select(
                'stock_ledger.*',
                // 'stock_ledger_item_attributes.attribute_name',
                // 'stock_ledger_item_attributes.attribute_value',
                DB::raw('SUM(CASE WHEN document_status = "approved" THEN receipt_qty ELSE 0 END) as confirmed_stock'),
                DB::raw('SUM(CASE WHEN document_status != "approved" THEN receipt_qty ELSE 0 END) as unconfirmed_stock')
            );

            $query->groupBy(
                // 'stock_ledger_item_attributes.attribute_value',
                // 'stock_ledger_item_attributes.attribute_name',
                // 'stock_ledger.item_name',
                // 'stock_ledger.item_code',
                'stock_ledger.item_id'
            );
        }
                
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

        
        // Group by item ID
      
        // $query->groupBy('stock_ledger.item_id','stock_ledger_item_attributes.attribute_name', 'stock_ledger_item_attributes.attribute_value');
        
        // Attributes Check
        // if($attributesCheck) {
        //     $query->groupBy('id');
        // }
        
        // dd($query->toSql());

        // Fetch the results
        $inventory_reports = $query->get();

        return response()->json($inventory_reports);
    }
    public function getAttributeValues(Request $request)
    {
        // dd('hi');
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

    # On change item attribute
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

}