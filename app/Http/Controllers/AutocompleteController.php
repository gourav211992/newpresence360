<?php

namespace App\Http\Controllers;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\PackingList\Constants as PackingListConstants;
use App\Helpers\ServiceParametersHelper;
use App\Models\AuthUser;
use App\Models\Book;
use App\Models\CashCustomerDetail;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DiscountMaster;
use App\Models\Employee;
use App\Models\ErpBin;
use App\Models\ErpCustomer;
use App\Models\ErpMaterialIssueHeader;
use App\Models\ErpMaterialReturnHeader;
use App\Models\ErpMiItem;
use App\Models\ErpMrItem;
use App\Models\ErpProductionSlip;
use App\Models\ErpProductionWorkOrder;
use App\Models\ErpPsvHeader;
use App\Models\ErpRack;
use App\Models\ErpSaleInvoice;
use App\Models\ErpSaleOrder;
use App\Models\ErpShelf;
use App\Models\ErpSoItem;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\ErpVendor;
use App\Models\ExpenseMaster;
use App\Models\Hsn;
use App\Models\Item;
use App\Models\JobOrder\JobOrder;
use App\Models\LandLease;
use App\Models\LandParcel;
use App\Models\LandPlot;
use App\Models\Ledger;
use App\Models\Group;
use App\Models\MfgOrder;
use App\Models\MoItem;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use App\Models\OrganizationService;
use App\Models\PackingList;
use App\Models\ProductSection;
use App\Models\ProductSectionDetail;
use App\Models\ProductSpecification;
use App\Models\PurchaseIndent;
use App\Models\PurchaseOrder;
use App\Models\Service;
use App\Models\Station;
use App\Models\SubType;
use App\Models\Vendor;
use App\Models\VendorItem;
use App\Models\Department;
use App\Models\Bom;
use App\Models\MrnHeader;
use App\Models\ProductionRoute;
use App\Models\UnitMaster;
use App\Models\HsnMaster;
use App\Models\Overhead;
use App\Models\PiItem;
use App\Models\ErpSubStoreParent;
use App\Models\ItemAttribute;
use App\Models\InspectionChecklist;
use App\Models\Attribute;
use Auth;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Http\Request;

class AutocompleteController extends Controller
{
    public function search(Request $request)
    {
        $term = $request->input('q');
        $type = $request->input('type');
        $id = $request->input('id');
        $categoryId = $request->input('categoryId');
        $results = [];
        $authUser = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $authUser->organization_id)->first();
        $organizationId = $organization ?-> id ?? null;
        $companyId =  $organization?->company_id ?? null;

        try {
            if ($type === 'category') {
                
                $query = Category::where('status', ConstantHelper::ACTIVE)
                    ->withDefaultGroupCompanyOrg()
                    ->doesntHave('subCategories'); 
            
                if ($request->has('category_type')) {
                    $query->where('type', $request->input('category_type'));
                }
            
                $results = $query->when($term, function ($q) use ($term) {
                    return $q->where('name', 'LIKE', "%$term%");
                })
                ->with('hsn','parent', 'inspectionChecklist')
                ->get(['id', 'name', 'cat_initials','sub_cat_initials','hsn_id', 'inspection_checklist_id','parent_id']);
            
                $results = $results->map(function ($category) {
                    $category->hsn_code = $category->hsn ? $category->hsn->code : null;
                    $category->inspection_name = $category->inspectionChecklist ? $category->inspectionChecklist->name : null;
                    return $category;
                });
            
                if ($results->isEmpty()) {
                    $fallbackQuery = Category::where('status', ConstantHelper::ACTIVE)
                        ->withDefaultGroupCompanyOrg()
                        ->doesntHave('subCategories');
            
                    if ($request->has('category_type')) {
                        $fallbackQuery->where('type', $request->input('category_type'));
                    }
            
                    $results = $fallbackQuery->limit(10)
                        ->with('hsn','parent','inspectionChecklist')
                        ->get(['id', 'name', 'cat_initials', 'hsn_id', 'inspection_checklist_id','parent_id']);
            
                    $results = $results->map(function ($category) {
                        $category->hsn_code = $category->hsn ? $category->hsn->code : null;
                        $category->inspection_name = $category->inspectionChecklist ? $category->inspectionChecklist->name : null;
                        return $category;
                    });
                }
            } elseif ($type === 'subcategory') {
                $query = Category::where('status', ConstantHelper::ACTIVE)
                   ->withDefaultGroupCompanyOrg()
                    ->when($request->has('category_type'), function ($q) use ($request) {
                        return $q->where('type', $request->input('category_type'));
                    })
                    ->when($term, function ($q) use ($term) {
                        return $q->where('name', 'LIKE', "%$term%");
                    });

                if ($categoryId) {
                    $query->where('parent_id', $categoryId);
                }
                else{
                    $query->whereNotNull('parent_id');
                }

                $results = $query->get(['id', 'name', 'sub_cat_initials']);

                if ($results->isEmpty()) {
                    $fallbackQuery = Category::where('status', ConstantHelper::ACTIVE)
                        ->when($categoryId, function ($q) use ($categoryId) {
                            return $q->where('parent_id', $categoryId);
                        });

                    if ($request->has('category_type')) {
                        $fallbackQuery->where('type', $request->input('category_type'));
                    }

                    $results = $fallbackQuery->limit(10)->get(['id', 'name', 'sub_cat_initials']);

                }
            }
             elseif ($type === 'hsn') {
                $results = Hsn::where('status', ConstantHelper::ACTIVE)
                ->withDefaultGroupCompanyOrg()
                ->where(function ($query) use ($term) {
                    $query->where('code', 'LIKE', "%$term%")
                          ->orWhere('description', 'LIKE', "%$term%");
                })
                ->get(['id', 'code', 'description']);

                if ($results->isEmpty()) {
                    $results = Hsn::where('status', ConstantHelper::ACTIVE)
                    -> withDefaultGroupCompanyOrg()
                        ->limit(10)
                        ->get(['id', 'code','description']);
                }
            }
            elseif ($type === 'checklist') {
                $results = InspectionChecklist::where('status', ConstantHelper::ACTIVE)
                    ->withDefaultGroupCompanyOrg()
                    ->where(function ($query) use ($term) {
                        $query->where('name', 'LIKE', "%$term%");
                    }) 
                    ->get(['id', 'name']);
            
                 if ($results->isEmpty()) {
                    $results = InspectionChecklist::where('status', ConstantHelper::ACTIVE)
                    ->withDefaultGroupCompanyOrg()
                    ->limit(10)->get(['id', 'name']);
                }
            }
            elseif ($type === 'vendor') {
                $excludeVendorId = $request->has('vendor_id') ? $request->input('vendor_id') : null;
                $query = Vendor::where('status', ConstantHelper::ACTIVE)
                    ->withDefaultGroupCompanyOrg();
                if ($term) {
                    $query->searchByKeywords($term); 
                }
                if ($excludeVendorId) {
                    $query->where('id', '!=', $excludeVendorId);
                }
                $results = $query->get(['id', 'company_name', 'vendor_code']);
            
                if ($results->isEmpty()) {
                    $fallbackQuery = Vendor::where('status', ConstantHelper::ACTIVE)
                        ->withDefaultGroupCompanyOrg();
                    if ($excludeVendorId) {
                        $fallbackQuery->where('id', '!=', $excludeVendorId);
                    }
                    $results = $fallbackQuery->limit(10)->get(['id', 'company_name', 'vendor_code']);
                }
            }            
            elseif ($type === 'customer') {
                $excludeCustomerId = $request->has('customer_id') ? $request->input('customer_id') : null;
                $query = Customer::where('status', ConstantHelper::ACTIVE)
                    ->withDefaultGroupCompanyOrg();
            
                if ($term) {
                    $query->searchByKeywords($term); 
                }
                if ($excludeCustomerId) {
                    $query->where('id', '!=', $excludeCustomerId);
                }
            
                $results = $query->get(['id', 'company_name', 'customer_code']);
            
                if ($results->isEmpty()) {
                    $fallbackQuery = Customer::where('status', ConstantHelper::ACTIVE)
                        ->withDefaultGroupCompanyOrg();
            
                    if ($excludeCustomerId) {
                        $fallbackQuery->where('id', '!=', $excludeCustomerId);
                    }
            
                    $results = $fallbackQuery->limit(10)->get(['id', 'company_name', 'customer_code']);
                }
            }
            elseif ($type === 'item-name') {
                $query = Item::query()
                     ->withDefaultGroupCompanyOrg()
                    ->where('status', ConstantHelper::ACTIVE);
                    
                if ($term) {
                    $query->searchByKeywords($term); 
                }
                $results = $query->get(['id', 'item_code', 'item_name']);
                if ($results->isEmpty()) {
                    $results = Item::query()
                        ->withDefaultGroupCompanyOrg()
                        ->where('status', ConstantHelper::ACTIVE)
                        ->limit(10)
                        ->get(['id', 'item_code', 'item_name']);
                }
            }
            
            elseif ($type === 'customer-name') {
                $query = Customer::query()
                    ->withDefaultGroupCompanyOrg()
                    ->where('status', ConstantHelper::ACTIVE);
                    
                if ($term) {
                    $query->searchByKeywords($term); 
                }
                $results = $query->get(['id', 'customer_code', 'company_name']);

                if ($results->isEmpty()) {
                    $results = Customer::query()
                        ->withDefaultGroupCompanyOrg()
                        ->where('status', ConstantHelper::ACTIVE)
                        ->limit(10)
                        ->get(['id', 'customer_code', 'company_name']);
                }
            }
            elseif ($type === 'vendor-name') {
                $query = Vendor::query()
                    ->withDefaultGroupCompanyOrg()
                    ->where('status', ConstantHelper::ACTIVE);
                    
                if ($term) {
                    $query->searchByKeywords($term); 
                }
                
                $results = $query->get(['id', 'vendor_code', 'company_name']);
                if ($results->isEmpty()) {
                    $results = Vendor::query()
                        ->withDefaultGroupCompanyOrg()
                        ->where('status', ConstantHelper::ACTIVE)
                        ->limit(10)
                        ->get(['id', 'vendor_code', 'company_name']);
                }
            }
            elseif ($type === 'sub_type') {
                $results = SubType::where('status', ConstantHelper::ACTIVE)
                ->where(function ($query) use ($term) {
                    $query->where('name', 'LIKE', "%$term%");
                })
                ->get(['id', 'name']);

                if ($results->isEmpty()) {
                    $results = SubType::where('status', ConstantHelper::ACTIVE)
                        ->limit(10)
                        ->get(['id', 'name']);
                }
            } elseif ($type === 'contraLedger') {
                $query = Ledger::withDefaultGroupCompanyOrg()
                                 ->where('status', 1);
            
                $group = Group::where('name', 'Branch / Divisions')->first();
                if ($group) {
                    $childGroupIds = $group->getAllChildIds();
                    $groupIds = array_merge([$group->id], $childGroupIds);
                    $stringGroupIds = array_map('strval', $groupIds);
            
                   $query->where(function($q2) use ($stringGroupIds) {
                        foreach ($stringGroupIds as $id) {
                            $q2->orWhereJsonContains('ledger_group_id', $id);
                        }
                    });
                } else {
                    $results = collect(); 
                    return $results; 
                }
            
                $results = $query->where(function($query) use ($term) {
                                     $query->where('code', 'LIKE', "%{$term}%")
                                           ->orWhere('name', 'LIKE', "%{$term}%");
                                  })
                                 ->get(['id', 'code', 'name']);
            
                if ($results->isEmpty()) {
                    $results = Ledger::withDefaultGroupCompanyOrg()
                                     ->where('status', 1);
            
                     $group = Group::where('name', 'Branch / Divisions')->first();
                     if ($group) {
                        $childGroupIds = $group->getAllChildIds();
                        $groupIds = array_merge([$group->id], $childGroupIds);
                        $stringGroupIds = array_map('strval', $groupIds);
            
                         $query->where(function($q2) use ($stringGroupIds) {
                            foreach ($stringGroupIds as $id) {
                                $q2->orWhereJsonContains('ledger_group_id', $id);
                            }
                        });
                    }
            
                    $results =   $results->limit(10)
                                         ->get(['id', 'code', 'name']);
                }
            }
                  
            elseif ($type === 'header_item') {
                $type = ['WIP/Semi Finished', 'Finished Goods'];
                $results = Item::withDefaultGroupCompanyOrg()
                    ->whereHas('subTypes', function ($query) use ($type) {
                        $query->whereHas('subType', function ($subTypeQuery) use($type) {
                            $subTypeQuery -> whereIn('name', $type);
                        });
                    })
                    ->searchByKeywords($term)
                    ->where('status', ConstantHelper::ACTIVE)
                    ->with(['itemAttributes:id'])
                    ->with(['uom:id,name'])
                    ->withCount('itemAttributes')
                    ->limit(10)
                    ->get(['id', 'item_name', 'item_code','uom_id']);
            }  elseif ($type === 'pi_comp_item') {
                $subTypeIds = SubType::whereNotIn('name', [ConstantHelper::FINISHED_GOODS])
                ->pluck('id');
                $results = Item::withDefaultGroupCompanyOrg()
                    ->whereHas('subTypes', function ($query) use ($subTypeIds) {
                    $query->whereIn('sub_type_id', $subTypeIds);
                    })
                    ->searchByKeywords($term)
                    ->where('status', ConstantHelper::ACTIVE)
                    ->with([
                    'itemAttributes:id',
                    'uom:id,name'
                    ])
                    ->withCount('itemAttributes')
                    ->limit(10)
                    ->get(['id', 'item_name', 'item_code', 'uom_id']);
            } elseif ($type === 'pr_item') {
                $type = ['WIP/Semi Finished', 'Finished Goods'];
                $results = Item::withDefaultGroupCompanyOrg()
                    ->whereHas('subTypes', function ($query) use ($type) {
                        $query->whereHas('subType', function ($subTypeQuery) use($type) {
                            $subTypeQuery -> whereIn('name', $type);
                        });
                    })
                    ->searchByKeywords($term)
                    ->where('status', ConstantHelper::ACTIVE)
                    ->with(['itemAttributes:id'])
                    ->with(['uom:id,name'])
                    ->withCount('itemAttributes')
                    ->limit(10)
                    ->get(['id', 'item_name', 'item_code','uom_id']);
            } elseif ($type === 'inventory_items') {
                $results = Item::withDefaultGroupCompanyOrg()
                    ->searchByKeywords($term)
                    ->where('status', ConstantHelper::ACTIVE)
                    ->limit(10)
                    ->get(['id', 'item_name', 'item_code', 'uom_id']);
            }elseif ($type === 'comp_item') {
                /*This is for Bom*/
                // $selectedAllItemIds = json_decode($request->input('selectedAllItemIds'), true) ?? [];
                // if(count($selectedAllItemIds)) {
                //     array_unique($selectedAllItemIds);
                // }
                $type = ['Raw Material','WIP/Semi Finished','Traded Item', 'Expense'];
                $results = Item::withDefaultGroupCompanyOrg()
                    ->whereHas('subTypes', function ($query) use ($type) {
                        $query->whereHas('subType', function ($subTypeQuery) use($type) {
                            $subTypeQuery -> whereIn('name', $type);
                        });
                    })
                    ->searchByKeywords($term)
                    ->when($request -> customer_id, function ($custQuery) use($request) {
                        $custQuery-> where(function ($query) use ($request) {
                            $query->whereHas('approvedCustomers', function ($subQuery) use ($request) {
                                $subQuery->where('customer_id', $request->customer_id);
                            })
                            ->orWhereDoesntHave('approvedCustomers');
                        });
                    })
                    ->with(['alternateUOMs.uom', 'specifications'])
                    ->where('status', ConstantHelper::ACTIVE)
                    ->with(['itemAttributes:id'])
                    ->with(['uom:id,name'])
                    ->withCount('itemAttributes')
                    ->limit(10)
                    ->get(['id', 'item_name', 'item_code', 'uom_id']);
            } elseif ($type === 'sale_module_items') {
                $subTypeIds = SubType::whereIn('name', [ConstantHelper::FINISHED_GOODS, ConstantHelper::TRADED_ITEM, ConstantHelper::ASSET,ConstantHelper::WIP_SEMI_FINISHED])
                -> get() -> pluck('id') -> toArray();
                $itemType = ServiceParametersHelper::getBookLevelParameterValue(ServiceParametersHelper::GOODS_SERVICES_PARAM, $request -> header_book_id)['data'];
                if (isset($itemType) && isset($itemType[0])) {
                    $itemType = $itemType[0];
                }
                $results = Item::withDefaultGroupCompanyOrg()
                    ->searchByKeywords($term)
                    -> when($request -> customer_id, function ($custQuery) use($request) {
                        $custQuery-> where(function ($query) use ($request) {
                            $query->whereHas('approvedCustomers', function ($subQuery) use ($request) {
                                $subQuery->where('customer_id', $request->customer_id); // Match the specific customer
                            })
                            ->orWhereDoesntHave('approvedCustomers'); // Include items not linked to any customers
                        });
                    })
                    ->where('type', $itemType)
                    -> where(function ($typeQuery) use($itemType, $subTypeIds) {
                        $typeQuery -> when($itemType == ConstantHelper::GOODS, function ($subQuery) use($subTypeIds) {
                            $subQuery -> whereHas('subTypes', function ($subTypeQuery) use($subTypeIds) {
                                $subTypeQuery -> whereIn('sub_type_id', $subTypeIds);
                            });
                        });
                    })
                    -> with(['alternateUOMs.uom', 'specifications'])
                    ->where('status', ConstantHelper::ACTIVE)
                    ->with(['itemAttributes'])
                    ->with(['uom:id,name'])
                    ->withCount('itemAttributes')
                    ->limit(10)
                    ->get(['id', 'item_name', 'item_code', 'uom_id']);
            } elseif ($type === 'material_issue_items') {
                $results = Item::withDefaultGroupCompanyOrg()
                    ->searchByKeywords($term)
                    -> when($request -> customer_id, function ($custQuery) use($request) {
                        $custQuery-> where(function ($query) use ($request) {
                            $query->whereHas('approvedCustomers', function ($subQuery) use ($request) {
                                $subQuery->where('customer_id', $request->customer_id); // Match the specific customer
                            })
                            ->orWhereDoesntHave('approvedCustomers'); // Include items not linked to any customers
                        });
                    })
                    -> whereIn('type', [ConstantHelper::GOODS])
                    -> with(['alternateUOMs.uom', 'specifications'])
                    ->where('status', ConstantHelper::ACTIVE)
                    ->with(['itemAttributes'])
                    ->with(['uom:id,name'])
                    ->withCount('itemAttributes')
                    ->limit(10)
                    ->get(['id', 'item_name', 'item_code', 'uom_id']);
            } elseif ($type === 'material_return_items') {
                $results = Item::withDefaultGroupCompanyOrg()
                    ->searchByKeywords($term)
                    -> when($request -> customer_id, function ($custQuery) use($request) {
                        $custQuery-> where(function ($query) use ($request) {
                            $query->whereHas('approvedCustomers', function ($subQuery) use ($request) {
                                $subQuery->where('customer_id', $request->customer_id); // Match the specific customer
                            })
                            ->orWhereDoesntHave('approvedCustomers'); // Include items not linked to any customers
                        });
                    })
                    -> whereIn('type', [ConstantHelper::GOODS])
                    -> with(['alternateUOMs.uom', 'specifications'])
                    ->where('status', ConstantHelper::ACTIVE)
                    ->with(['itemAttributes'])
                    ->with(['uom:id,name'])
                    ->withCount('itemAttributes')
                    ->limit(10)
                    ->get(['id', 'item_name', 'item_code', 'uom_id']);
            } elseif ($type === 'rate_contract_items') {
                $results = Item::withDefaultGroupCompanyOrg()
                    ->searchByKeywords($term)
                    -> when($request -> customer_id, function ($custQuery) use($request) {
                        $custQuery-> where(function ($query) use ($request) {
                            $query->whereHas('approvedCustomers', function ($subQuery) use ($request) {
                                $subQuery->where('customer_id', $request->customer_id); // Match the specific customer
                            })
                            ->orWhereDoesntHave('approvedCustomers'); // Include items not linked to any customers
                        });
                    })
                    -> whereIn('type', [ConstantHelper::GOODS])
                    -> with(['alternateUOMs.uom', 'specifications'])
                    ->where('status', ConstantHelper::ACTIVE)
                    ->with(['itemAttributes'])
                    ->with(['uom:id,name'])
                    ->withCount('itemAttributes')
                    ->limit(10)
                    ->get(['id', 'item_name', 'item_code', 'uom_id']);
            } elseif ($type === 'work_order_items') {

                // $itemType = ServiceParametersHelper::getBookLevelParameterValue(ServiceParametersHelper::GOODS_SERVICES_PARAM, $request -> header_book_id)['data'];
                $results = Item::withDefaultGroupCompanyOrg()
                    ->searchByKeywords($term)
                    -> when($request -> customer_id, function ($custQuery) use($request) {
                        $custQuery-> where(function ($query) use ($request) {
                            $query->whereHas('approvedCustomers', function ($subQuery) use ($request) {
                                $subQuery->where('customer_id', $request->customer_id); // Match the specific customer
                            })
                            ->orWhereDoesntHave('approvedCustomers'); // Include items not linked to any customers
                        });
                    })
                    -> whereIn('type', [ConstantHelper::GOODS])
                    -> with(['alternateUOMs.uom', 'specifications'])
                    ->where('status', ConstantHelper::ACTIVE)
                    ->with(['itemAttributes'])
                    ->with(['uom:id,name'])
                    ->withCount('itemAttributes')
                    ->limit(10)
                    ->get(['id', 'item_name', 'item_code', 'uom_id']);
            } elseif ($type === 'sales_module_discount') {
                $results = DiscountMaster::withDefaultGroupCompanyOrg()
                    ->where(function ($query) use ($term) {
                    $query->where('name', 'LIKE', "%{$term}%")
                          ->orWhere('alias', 'LIKE', "%{$term}%");
                    })
                    -> when($request -> selected_discount_ids, function ($discountQuery) use($request) {
                        $discountQuery -> whereNotIn('id', $request -> selected_discount_ids);
                    })
                    -> where('is_sale', 1)
                    -> where('status', ConstantHelper::ACTIVE)
                    ->limit(10)
                    ->get(['id', 'name', 'alias', 'percentage']);
            }  elseif ($type === 'overhead_master') {
                $selectedIds = $request->ids;
                $selectedIds = json_decode($selectedIds, TRUE) ?? [];
                $selectedIds = array_map('intval', $selectedIds);
                $results = Overhead::withDefaultGroupCompanyOrg()
                    ->where(function ($query) use ($term) {
                        if($term) {
                            $query->where('name', 'LIKE', "%{$term}%");
                        }
                    })
                    ->when(count($selectedIds), function ($overheadQuery) use($selectedIds) {
                        $overheadQuery->whereNotIn('id', $selectedIds);
                    })
                    -> where('status', ConstantHelper::ACTIVE)
                    ->limit(10)
                    ->get(['id', 'name', 'perc']);
            } elseif ($type === 'sales_module_expense') {
                $results = ExpenseMaster::withDefaultGroupCompanyOrg()
                    ->where(function ($query) use ($term) {
                    $query->where('name', 'LIKE', "%{$term}%")
                          ->orWhere('alias', 'LIKE', "%{$term}%");
                    })
                    -> where('is_sale', 1)
                    -> where('status', ConstantHelper::ACTIVE)
                    ->limit(10)
                    ->get(['id', 'name', 'alias', 'percentage']);
            }  elseif ($type === 'po_module_discount') {
                $ids = json_decode($request->ids, TRUE) ?? [];
                $ids = array_map('intval', $ids);
                $results = DiscountMaster::withDefaultGroupCompanyOrg()
                    ->where(function($q) use ($ids) {
                        if(count($ids)) {
                            $q->whereNotIn('id', $ids);
                        }
                    })
                    ->where(function ($query) use ($term) {
                        $query->where('name', 'LIKE', "%{$term}%")
                          ->orWhere('alias', 'LIKE', "%{$term}%");
                    })
                    -> where('is_purchase', 1)
                    -> where('status', ConstantHelper::ACTIVE)
                    ->limit(10)
                    ->get(['id', 'name', 'alias', 'percentage']);
            } elseif ($type === 'po_module_expense') {
                $ids = json_decode($request->ids, TRUE) ?? [];
                $ids = array_map('intval', $ids);
                $results = ExpenseMaster::withDefaultGroupCompanyOrg()
                    ->where(function($q) use ($ids) {
                        if(count($ids)) {
                            $q->whereNotIn('id', $ids);
                        }
                    })
                    ->where(function ($query) use ($term) {
                        $query->where('name', 'LIKE', "%{$term}%")
                          ->orWhere('alias', 'LIKE', "%{$term}%");
                    })
                    -> where('is_purchase', 1)
                    -> where('status', ConstantHelper::ACTIVE)
                    ->limit(10)
                    ->get(['id', 'name', 'alias', 'percentage']);
            } elseif ($type === 'po_item_list') {
                /*This for the PO*/
                // $selectedAllItemIds = json_decode($request->input('selectedAllItemIds'), true) ?? [];
                // // dd($selectedAllItemIds);
                // if(count($selectedAllItemIds)) {
                //     array_unique($selectedAllItemIds);
                // }
                $results = Item::withDefaultGroupCompanyOrg()
                    ->searchByKeywords($term)
                    // ->whereNotIn('id', $selectedAllItemIds) // Uncomment if needed
                    ->where('status', ConstantHelper::ACTIVE)
                    ->with(['uom:id,name'])
                    ->with(['hsn:id,code'])
                    ->with(['alternateUOMs.uom'])
                    ->withCount('itemAttributes')
                    ->limit(10)
                    ->get(['id', 'item_name', 'item_code', 'uom_id','hsn_id']);
            } elseif ($type === 'service_item_list') {
                /*This for the Service Based Items*/
                $selectedAllItemIds = json_decode($request->input('selectedAllItemIds'), true) ?? [];
                if(count($selectedAllItemIds)) {
                    array_unique($selectedAllItemIds);
                }
                $results = Item::withDefaultGroupCompanyOrg()
                    ->where('type', 'Service')
                    ->searchByKeywords($term)
                    ->where('status', ConstantHelper::ACTIVE)
                    ->with(['uom:id,name'])
                    ->with(['hsn:id,code'])
                    ->with(['alternateUOMs.uom'])
                    ->withCount('itemAttributes')
                    ->limit(10)
                    ->get(['id', 'item_name', 'item_code', 'uom_id','hsn_id']);
            } elseif ($type === 'goods_item_list') {
                /*This for the Service Based Items*/
                $selectedAllItemIds = json_decode($request->input('selectedAllItemIds'), true) ?? [];
                if(count($selectedAllItemIds)) {
                    array_unique($selectedAllItemIds);
                }
                $results = Item::withDefaultGroupCompanyOrg()
                    ->where('group_id',$organization->group_id)
                    ->where('type', 'Goods')
                    ->searchByKeywords($term)
                    ->where('status', ConstantHelper::ACTIVE)
                    ->with(['uom:id,name'])
                    ->with(['hsn:id,code'])
                    ->with(['alternateUOMs.uom'])
                    ->withCount('itemAttributes')
                    ->limit(10)
                    ->get(['id', 'item_name', 'item_code', 'uom_id','hsn_id']);
            } elseif ($type === 'ledger' || $type === 'ladger') {
                $results = Ledger::withDefaultGroupCompanyOrg()
                                 ->where(function($query) use ($term) {
                                     $query->where('code', 'LIKE', "%{$term}%")
                                           ->orWhere('name', 'LIKE', "%{$term}%");
                                 })
                                 ->where('status', 1)
                                 ->get(['id', 'code', 'name']);

                if ($results->isEmpty()) {
                    $results = Ledger::where('status', 1)
                                     ->limit(10)
                                     ->get(['id', 'code', 'name']);
                }
            }elseif ($type === 'ledgerGroup') {
                    $results = Group::where('status', 1)
                                    ->limit(10)
                                    ->get(['id', 'name']);
            }elseif ($type === 'book') {
                $serviceAlias = ConstantHelper::BOM_SERVICE_ALIAS;
                $subQuery = Helper::getBookSeries($serviceAlias)->get();
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                    ->get(['id', 'book_name', 'book_code']);

                if ($results->isEmpty()) {
                    $results = $subQuery
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            } elseif ($type === 'book_sq') {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $subQuery = Helper::getBookSeries(ConstantHelper::SQ_SERVICE_ALIAS);
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                    ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('id', $applicableBookIds);
                    })
                    ->get(['id', 'book_name', 'book_code']);

                if ($results->isEmpty()) {
                    $results = $subQuery
                        ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                            $applicableQuery -> whereIn('id', $applicableBookIds);
                        })
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            } elseif ($type === 'book_so') {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);

                $subQuery = Helper::getBookSeries(ConstantHelper::SO_SERVICE_ALIAS);
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                    $applicableQuery -> whereIn('id', $applicableBookIds);
                })
                    ->get(['id', 'book_name', 'book_code']);

                if ($results->isEmpty()) {
                    $results = $subQuery
                    ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('id', $applicableBookIds);
                    })
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            } elseif ($type === 'book_plist') {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);

                $subQuery = Helper::getBookSeries(PackingListConstants::SERVICE_ALIAS);
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                    $applicableQuery -> whereIn('id', $applicableBookIds);
                })
                    ->get(['id', 'book_name', 'book_code']);

                if ($results->isEmpty()) {
                    $results = $subQuery
                    ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('id', $applicableBookIds);
                    })
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            } elseif ($type === 'book_din') {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $subQuery = Helper::getBookSeries(ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS);
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                    ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('id', $applicableBookIds);
                    })
                    ->get(['id', 'book_name', 'book_code']);

                if ($results->isEmpty()) {
                    $results = $subQuery
                        ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                            $applicableQuery -> whereIn('id', $applicableBookIds);
                        })
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            } elseif ($type === 'book_land_lease') {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $subQuery = Helper::getBookSeries(ConstantHelper::LAND_LEASE);
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                    ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('id', $applicableBookIds);
                    })
                    ->get(['id', 'book_name', 'book_code']);

                if ($results->isEmpty()) {
                    $results = $subQuery
                        ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                            $applicableQuery -> whereIn('id', $applicableBookIds);
                        })
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            } elseif ($type === 'book_pi') {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                if($request->module_type == 'supplier-invoice') {
                    $pi = ConstantHelper::PO_SERVICE_ALIAS;
                } else {
                    $pi = ConstantHelper::PI_SERVICE_ALIAS;
                }
                $subQuery = Helper::getBookSeries($pi);
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                    ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('id', $applicableBookIds);
                    })
                    ->get(['id', 'book_name', 'book_code']);

                if ($results->isEmpty()) {
                    $results = $subQuery
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            } elseif ($type === 'book_pwo') {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $subQuery = Helper::getBookSeries(ConstantHelper::PWO_SERVICE_ALIAS);
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                    ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('id', $applicableBookIds);
                    })
                    ->get(['id', 'book_name', 'book_code']);

                if ($results->isEmpty()) {
                    $results = $subQuery
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            }  elseif ($type === 'book_mo') {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $subQuery = Helper::getBookSeries(ConstantHelper::MO_SERVICE_ALIAS);
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                    ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('id', $applicableBookIds);
                    })
                    ->get(['id', 'book_name', 'book_code']);

                if ($results->isEmpty()) {
                    $results = $subQuery
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            }  elseif ($type === 'book_jo') {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $subQuery = Helper::getBookSeries(ConstantHelper::JO_SERVICE_ALIAS);
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                    ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('id', $applicableBookIds);
                    })
                    ->get(['id', 'book_name', 'book_code']);

                if ($results->isEmpty()) {
                    $results = $subQuery
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            }  elseif ($type === 'book_mi') {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $subQuery = Helper::getBookSeries(ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME);
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                    ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('id', $applicableBookIds);
                    })
                    ->get(['id', 'book_name', 'book_code']);

                if ($results->isEmpty()) {
                    $results = $subQuery
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            } elseif ($type === 'book_bom') {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request->header_book_id);
                $pi = ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS;
                $subQuery = Helper::getBookSeries($pi);
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                    // ->when($request->header_book_id, function ($applicableQuery) use($applicableBookIds) {
                    //     $applicableQuery -> whereIn('id', $applicableBookIds);
                    // })
                    ->get(['id', 'book_name', 'book_code']);
                if ($results->isEmpty()) {
                    $results = $subQuery
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            } elseif ($type === 'book_po') {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $subQuery = Helper::getBookSeries(ConstantHelper::PO_SERVICE_ALIAS);
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                    $applicableQuery -> whereIn('id', $applicableBookIds);
                })
                    ->get(['id', 'book_name', 'book_code']);

                if ($results->isEmpty()) {
                    $results = $subQuery
                    ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('id', $applicableBookIds);
                    })
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            } elseif ($type === 'book_mrn') {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $subQuery = Helper::getBookSeries(ConstantHelper::MRN_SERVICE_ALIAS);
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                    $applicableQuery -> whereIn('id', $applicableBookIds);
                })
                    ->get(['id', 'book_name', 'book_code']);

                if ($results->isEmpty()) {
                    $results = $subQuery
                    ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('id', $applicableBookIds);
                    })
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            } elseif ($type === 'book_si') {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $subQuery = Helper::getBookSeries(ConstantHelper::SI_SERVICE_ALIAS);
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                    $applicableQuery -> whereIn('id', $applicableBookIds);
                })
                    ->get(['id', 'book_name', 'book_code']);

                if ($results->isEmpty()) {
                    $results = $subQuery
                    ->when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('id', $applicableBookIds);
                    })
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            } elseif ($type === 'vendor_list') {
                $itemId = $request->item_id;
                $vendorIds = VendorItem::where('item_id', $itemId)->pluck('vendor_id')->toArray();
                $subQuery = Vendor::withDefaultGroupCompanyOrg()
                            ->where(function($query) use ($vendorIds) {
                                if (count($vendorIds)) {
                                    $query->whereIn('id', $vendorIds);
                                }
                            })
                            ->where('status', ConstantHelper::ACTIVE)
                            ->with(['currency:id,short_name', 'paymentTerms:id,name']);

                $results = $subQuery->where('company_name', 'LIKE', "%$term%")
                    ->get(['id', 'company_name', 'vendor_code','currency_id','payment_terms_id']);

                // Map the results to include currency and payment terms
                $results = $results->map(function ($vendor) {
                    return [
                        'id' => $vendor->id,
                        'company_name' => $vendor->company_name,
                        'vendor_code' => $vendor->vendor_code,
                        'currency_id' => $vendor->currency->id ?? null,
                        'currency_name' => $vendor->currency->short_name ?? null,
                        'payment_terms_id' => $vendor->paymentTerms->id ?? null,
                        'payment_terms_name' => $vendor->paymentTerms->name ?? null,
                    ];
                });

                if ($results->isEmpty()) {
                    $results = $subQuery
                        ->limit(10)
                        ->get(['id', 'company_name', 'vendor_code']);

                    // Map fallback results
                    $results = $results->map(function ($vendor) {
                        return [
                            'id' => $vendor->id,
                            'company_name' => $vendor->company_name,
                            'vendor_code' => $vendor->vendor_code,
                            'currency_id' => $vendor->currency->id ?? null,
                            'currency_name' => $vendor->currency->name ?? null,
                            'payment_terms_id' => $vendor->paymentTerms->id ?? null,
                            'payment_terms_name' => $vendor->paymentTerms->name ?? null,
                        ];
                    });
                }
            } elseif ($type === 'product_section') {
                $subQuery = ProductSection::withDefaultGroupCompanyOrg()
                ->where('status', ConstantHelper::ACTIVE);

                $results = $subQuery->where('name', 'LIKE', "%$term%")
                    ->get(['id','name']);
                if ($results->isEmpty()) {
                    $results = $subQuery->limit(10)
                        ->get(['id','name']);
                }
            } elseif ($type === 'product_sub_section') {
                $subQuery = ProductSectionDetail::where('section_id', $id)
                            ->with(['station:id,name']);
                $results = $subQuery->where('name', 'LIKE', "%$term%")
                    ->get(['id','name','station_id']);
                if ($results->isEmpty()) {
                    $results = $subQuery->limit(10)
                        ->get(['id','name','station_id']);
                }
            } elseif ($type === 'station') {
                $production_route_id = $request->production_route_id;
                $selectedIds = json_decode($request->selectedIds,true) ?? [];
                $productionStationIds = [];
                $productionRoute = ProductionRoute::find($production_route_id);

                if($productionRoute) {
                    $productionStationIds = $productionRoute->details()->where('consumption','yes')->pluck('station_id')->toArray();
                }
                $subQuery = Station::withDefaultGroupCompanyOrg()
                        ->where('status', ConstantHelper::ACTIVE)
                        ->whereIn('id', $productionStationIds)
                        ->when(!empty($selectedIds), function ($query) use ($selectedIds) {
                            $query->whereNotIn('id', $selectedIds);
                        });

                        // ->where(function($query) use($productionStationIds) {
                        //     if(count($productionStationIds)) {
                        //         $query->whereIn('id',$productionStationIds);
                        //     }
                        // });

                $results = $subQuery->where('name', 'LIKE', "%$term%")
                    ->get(['id', 'name']);
                if ($results->isEmpty()) {
                    $results = $subQuery->limit(10)
                        ->get(['id','name']);

                }
            } else if ($type === 'customer' || $type === 'customer_list') {
                $results = Customer::withDefaultGroupCompanyOrg() -> with(['payment_terms', 'currency', 'compliances'])
                -> when($term, function ($termQuery) use($term) { 
                    $termQuery -> where('company_name', 'LIKE', '%'.$term.'%') -> orWhere('customer_code', 'LIKE', '%'.$term.'%');
                })->where('status', ConstantHelper::ACTIVE)
                ->limit(10)
                ->get(['id', 'customer_type', 'email', 'mobile', 'customer_code', 'company_name', 'currency_id', 'payment_terms_id','display_name']);
            } else if ($type === 'location') {
                $results = InventoryHelper::getAccessibleLocations();
            } else if ($type === 'sub_store') {
                $storeId = $request->store_id ?? 0;
                $results = InventoryHelper::getAccesibleSubLocations($storeId ?? 0);
                if ($results->isEmpty()) {
                    $results = InventoryHelper::getAccesibleSubLocations($storeId ?? 0);
                }
            } elseif ($type === 'specification') {
                $results = ProductSpecification::withDefaultGroupCompanyOrg()->where('name', 'LIKE', "%$term%")
                    ->where('status', ConstantHelper::ACTIVE)
                    ->get(['id', 'name', 'description']);
                if ($results->isEmpty()) {
                    $results = ProductSpecification::withDefaultGroupCompanyOrg()->where('status', ConstantHelper::ACTIVE)
                        ->limit(10)
                        ->get(['id', 'name', 'description']);
                }
            } else if ($type === "sale_order_document_qt") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id??0);
                $results = ErpSaleOrder::where('document_number', 'LIKE', "%$term%")
                    -> where('document_type', ConstantHelper::SQ_SERVICE_ALIAS)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = ErpSaleOrder::where('document_type', ConstantHelper::SQ_SERVICE_ALIAS)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number']);
                }
            } else if ($type === "sale_order_document_mo") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id??0);
                $so_ids = MoItem::whereHas('header', function ($query) {
                    $query->withDefaultGroupCompanyOrg();
                })->pluck('so_id')->unique()->toArray();
                $results = ErpSaleOrder::query()
                    ->whereIn(
                        'id', $so_ids)
                    ->when($term, function ($query) use ($term) {
                        if (preg_match('/^(.*?)\s*\((.*?)\)$/', $term, $matches)) {
                            $bookCode = trim($matches[1]);
                            $documentNumber = trim($matches[2]);

                            $query->where('book_code', $bookCode)
                                ->where('document_number', $documentNumber);
                        } else {
                            $query->where(function ($q) use ($term) {
                                $q->where('document_number', 'LIKE', "%$term%")
                                ->orWhere('book_code', 'LIKE', "%$term%");
                            });
                        }
                    })
                    ->get(['id', 'book_code', 'document_number']);


                if ($results->isEmpty()) {
                    $results = ErpSaleOrder::query()
                        ->whereIn('id', $so_ids)
                        ->get(['id', 'book_code', 'document_number']);



                }
            } else if ($type === "sale_order_document_pi") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id??0);
                $so_ids = PiItem::whereHas('header', function ($query) {
                    $query->withDefaultGroupCompanyOrg();
                })->pluck('so_id')->unique()->toArray();
                $results = ErpSaleOrder::query()
                    ->whereIn(
                        'id', $so_ids)
                    ->when($term, function ($query) use ($term) {
                        if (preg_match('/^(.*?)\s*\((.*?)\)$/', $term, $matches)) {
                            $bookCode = trim($matches[1]);
                            $documentNumber = trim($matches[2]);

                            $query->where('book_code', $bookCode)
                                ->where('document_number', $documentNumber);
                        } else {
                            $query->where(function ($q) use ($term) {
                                $q->where('document_number', 'LIKE', "%$term%")
                                ->orWhere('book_code', 'LIKE', "%$term%");
                            });
                        }
                    })
                    ->get(['id', 'book_code', 'document_number']);


                if ($results->isEmpty()) {
                    $results = ErpSaleOrder::query()
                        ->whereIn('id', $so_ids)
                        ->get(['id', 'book_code', 'document_number']);



                }
            } else if ($type === 'packing_list_so') {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $results = ErpSaleOrder::select('id', 'book_code', 'document_number', 'customer_code', 'document_date') -> when($term, function ($termQuery) use($term) {
                    $termQuery -> where('document_number', 'LIKE', "%$term%") -> orWhere('book_code', 'LIKE', "%$term%");
                })-> where('document_type', ConstantHelper::SO_SERVICE_ALIAS)-> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    }) -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED]) -> orderByDesc('id')
                    ->limit(10) -> get();
            } else if ($type === 'packing_list_so_items') {
                $results = ErpSoItem::select('id', 'item_code', 'item_name') -> when($term, function ($termQuery) use($term) {
                    $termQuery -> where('item_code', 'LIKE', "%$term%") -> orWhere('item_name', 'LIKE', "%$term%");
                })-> where('sale_order_id', $request -> sale_order_id) -> orderByDesc('id') ->limit(10) -> get();
            } else if ($type === "sale_order_document_qt_pi") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $results = ErpSaleOrder::where('document_number', 'LIKE', "%$term%")
                    -> where('document_type', ConstantHelper::SO_SERVICE_ALIAS)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = ErpSaleOrder::where('document_type', ConstantHelper::SQ_SERVICE_ALIAS)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number']);
                }
            } else if ($type === "book_mi") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $results = ErpMaterialIssueHeader::where('document_number', 'LIKE', "%$term%")
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = ErpMaterialIssueHeader::withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number']);
                }
            } else if ($type === "vendor_mi") {
                // $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $results = Vendor::where('company_name', 'LIKE', "%$term%")
                    -> withDefaultGroupCompanyOrg()->get();
                if ($results->isEmpty()) {
                    $results = Vendor::withDefaultGroupCompanyOrg()
                    ->get();
                }
            } else if ($type === "requester_mi") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $users = ErpMaterialIssueHeader::whereHas('items',function($query) use ($request){
                    $query->whereIn('id',json_decode($request->mi_item_ids));
                })->whereNotNull('user_id')->pluck('user_id')->unique();

                $results = AuthUser::where('name', 'LIKE', "%$term%")->whereIn('id', $users)->get(['id','name']);
                if ($results->isEmpty()) {
                    $results = AuthUser::withDefaultGroupCompanyOrg()->whereIn('id', $users)->get(['id','name']);
                }
            }else if ($type === "department_mi") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request->header_book_id);
                $department = ErpMiItem::whereNotNull('department_id')->whereIn('id',json_decode($request?->mi_item_ids))
                    ->pluck('department_id')
                    ->unique()
                    ->toArray();

                $results = Department::whereNotNull('name')
                    ->whereIn('id', $department)
                    ->where('organization_id', $authUser->organization_id)
                    ->where('name', 'LIKE', "%$term%")
                    ->get(['id', 'name'])
                    ->unique('name');

                if ($results->isEmpty()) {
                    $results = Department::whereNotNull('name')
                        ->whereIn('id', $department)
                        ->where('organization_id', $authUser->organization_id)
                        ->get(['id', 'name'])
                        ->unique('name');
                }
            }else if ($type === "department_pi") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request->header_book_id);
                $department = PurchaseIndent::whereNotNull('department_id')
                    ->pluck('department_id')
                    ->unique()
                    ->toArray();

                $results = Department::whereNotNull('name')
                    ->whereIn('id', $department)
                    ->where('organization_id', $authUser->organization_id)
                    ->where('name', 'LIKE', "%$term%")
                    ->get(['id', 'name'])
                    ->unique('name');

                if ($results->isEmpty()) {
                    $results = Department::whereNotNull('name')
                        ->whereIn('id', $department)
                        ->where('organization_id', $authUser->organization_id)
                        ->get(['id', 'name'])
                        ->unique('name');
                }
            }
             else if ($type === "pi_document_qt") {
                if($request->module_type == 'supplier-invoice') {
                    $results = PurchaseOrder::withDefaultGroupCompanyOrg()
                        ->where('document_number', 'LIKE', "%$term%")
                        ->where('type','po')
                        ->get(['id', 'document_number']);
                    if ($results->isEmpty()) {
                        $results = PurchaseOrder::withDefaultGroupCompanyOrg()
                        ->where('type','po')
                        ->limit(10)
                        ->get(['id', 'document_number','book_code']);
                    }
                } else {
                    $results = PurchaseIndent::withDefaultGroupCompanyOrg()
                        ->where('document_number', 'LIKE', "%$term%")
                        ->distinct('document_number')
                        ->get(['id', 'document_number','book_code','book_id']);
                    if ($results->isEmpty()) {
                        $results = PurchaseIndent::withDefaultGroupCompanyOrg()
                        ->distinct('document_number')
                        ->limit(10)
                        ->get(['id', 'document_number','book_code','book_id']);
                    }
                }
            }
            else if ($type === "pi_document") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $results = PurchaseIndent::where('document_number', 'LIKE', "%$term%")
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'book_code' ,'document_number']);
                if ($results->isEmpty()) {
                    $results = PurchaseIndent::limit(10)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                        ->get(['id', 'book_code' , 'document_number']);
                }
            }else if ($type === "jo_document") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $results = JobOrder::where('document_number', 'LIKE', "%$term%")
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'book_code' ,'document_number']);
                if ($results->isEmpty()) {
                    $results = JobOrder::limit(10)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                        ->get(['id', 'book_code' , 'document_number']);
                }
            } else if ($type === "document_book") {
                $serviceAlias = $request->service_alias ?? '';
                $subQuery = Helper::getBookSeries($serviceAlias);
                $results = $subQuery->where('book_name', 'LIKE', "%$term%")
                    ->get(['id', 'book_name', 'book_code']);
                if ($results->isEmpty()) {
                    $results = $subQuery
                        ->limit(10)
                        ->get(['id', 'book_name', 'book_code']);
                }
            }  else if ($type === "pi_so_qt") {
                // $soIds = PiItem::whereHas('pi', function ($pi) {
                //     $pi->withDefaultGroupCompanyOrg()
                //         ->whereIn('document_status', [
                //             ConstantHelper::APPROVAL_NOT_REQUIRED,
                //             ConstantHelper::APPROVED,
                //         ]);
                // })
                // ->whereNotNull('so_id')
                // ->pluck('so_id')
                // ->filter(fn ($ids) => is_array($ids) && !empty($ids))
                // ->flatMap(function ($ids) {
                //     return collect($ids)->filter(function ($id) {
                //         return !is_array($id) && !is_null($id);
                //     });
                // })
                // ->map(fn ($id) => (int) $id)
                // ->unique()
                // ->values()
                // ->toArray();

                $results = ErpSaleOrder::where('document_number', 'LIKE', "%$term%")
                    ->where('document_type', ConstantHelper::SO_SERVICE_ALIAS)
                    ->withDefaultGroupCompanyOrg()
                    // ->when(count($soIds), function ($applicableQuery) use($soIds) {
                    //     $applicableQuery->whereIn('id', $soIds);
                    // })
                    ->whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number','book_code']);
                if ($results->isEmpty()) {
                    $results = ErpSaleOrder::where('document_type', ConstantHelper::SQ_SERVICE_ALIAS)
                    -> withDefaultGroupCompanyOrg()
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number','book_code']);
                }

            } else if ($type === "po_document_qt") {
                $results = PurchaseOrder::withDefaultGroupCompanyOrg()->where('document_number', 'LIKE', "%$term%")
                    ->get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = PurchaseOrder::withDefaultGroupCompanyOrg()->limit(10)
                        ->get(['id', 'document_number']);
                    }
            }  else if ($type === "mrn_document_qt") {
                $results = MrnHeader::withDefaultGroupCompanyOrg()->where('document_number', 'LIKE', "%$term%")
                    ->get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = MrnHeader::withDefaultGroupCompanyOrg()->limit(10)
                        ->get(['id', 'document_number']);
                    }
            }else if ($type === "bom_document_qt") {
                $results = Bom::withDefaultGroupCompanyOrg()
                    ->where('type',ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS)
                    ->where('document_number', 'LIKE', "%$term%")
                    ->get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = Bom::withDefaultGroupCompanyOrg()
                        ->where('type',ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS)
                        ->limit(10)
                        ->get(['id', 'document_number']);
                    }
            } else if ($type === "plist_document") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $results = PackingList::where('document_number', 'LIKE', "%$term%")
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = PackingList::limit(10)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                        ->get(['id', 'document_number']);
                    }
            } else if ($type === "sale_order_document") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id??0);
                $results = ErpSaleOrder::where('document_number', 'LIKE', "%$term%")
                    -> where('document_type', ConstantHelper::SO_SERVICE_ALIAS)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = ErpSaleOrder::limit(10)
                    -> where('document_type', ConstantHelper::SO_SERVICE_ALIAS)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                        ->get(['id', 'document_number']);
                    }
            } else if ($type === "pwo_document") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $results = ErpProductionWorkOrder::where('document_number', 'LIKE', "%$term%")
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = ErpProductionWorkOrder::limit(10)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                        ->get(['id', 'document_number']);
                    }
            }  else if ($type === "mo_document") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $results = MfgOrder::where('document_number', 'LIKE', "%$term%")
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number','book_code']);
                if ($results->isEmpty()) {
                    $results = MfgOrder::limit(10)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                        ->get(['id', 'document_number','book_code']);
                    }
            }  else if ($type === "jo_document") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $results = JobOrder::where('document_number', 'LIKE', "%$term%")
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = JobOrder::limit(10)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                        ->get(['id', 'document_number']);
                    }
            }  else if ($type === "mi_document") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $results = ErpMaterialIssueHeader::whereHas('items',function($query) use ($request){
                    $query->whereIn('id',json_decode($request->mi_item_ids));
                })->where('document_number', 'LIKE', "%$term%")
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'book_code' ,'document_number']);
                if ($results->isEmpty()) {
                    $results = ErpMaterialIssueHeader::limit(10)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                        ->get(['id', 'book_code' , 'document_number']);
                    }
            }  else if ($type === "land_lease_document") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $results = LandLease::where('document_no', 'LIKE', "%$term%")
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('approvalStatus', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_no AS document_number']);
                if ($results->isEmpty()) {
                    $results = LandLease::limit(10)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('approvalStatus', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                        ->get(['id', 'document_no AS document_number']);
                    }
            } else if ($type === 'land_lease_parcel') {
                $results = LandParcel::withDefaultGroupCompanyOrg()->where('name', 'LIKE', "%$term%") -> select('id', 'name') -> get() ;
                if ($results->isEmpty()) {
                    $results = LandParcel::limit(10)
                    -> withDefaultGroupCompanyOrg()
                    ->get(['id', 'name']);
                    }
            } else if ($type === 'land_lease_plots') {
                $results = LandPlot::withDefaultGroupCompanyOrg()->where('plot_name', 'LIKE', "%$term%") -> select('id', 'plot_name') -> get() ;
                if ($results->isEmpty()) {
                    $results = LandPlot::limit(10)
                    -> withDefaultGroupCompanyOrg()
                    ->get(['id', 'plot_name']);
                    }
            } else if ($type === "din_document") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $results = ErpSaleInvoice::where('document_number', 'LIKE', "%$term%")
                    -> where('document_type', ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = ErpSaleInvoice::limit(10)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                        ->get(['id', 'document_number']);
                    }
            } else if ($type === "si_document") {
                $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($request -> header_book_id);
                $results = ErpSaleInvoice::where('document_number', 'LIKE', "%$term%")
                -> withDefaultGroupCompanyOrg()
                -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                    $applicableQuery -> whereIn('book_id', $applicableBookIds);
                })
                -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = ErpSaleInvoice::limit(10)
                    -> withDefaultGroupCompanyOrg()
                    -> when($request -> header_book_id, function ($applicableQuery) use($applicableBookIds) {
                        $applicableQuery -> whereIn('book_id', $applicableBookIds);
                    })
                    -> whereIn('document_status', [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVED])
                    ->get(['id', 'document_number']);
                    }
            } else if ($type === "store") {
                $storeType = $request->store_type ?? '';
                $results = ErpStore::where('organization_id', $authUser -> organization_id)
                    ->where(function($query) use($storeType) {
                        if($storeType) {
                            $query->where('store_location_type', $storeType);
                        }
                    })
                    ->where('store_code', 'LIKE', "%$term%")
                    ->get(['id', 'store_code']);
                if ($results->isEmpty()) {
                    $results = ErpStore::where('organization_id', $authUser -> organization_id)
                            ->where(function($query) use($storeType) {
                                if($storeType) {
                                    $query->where('store_location_type', $storeType);
                                }
                            })
                        ->limit(10)
                        ->get(['id', 'store_code']);
                }
            } else if ($type === "sub_store_list") {
                $storeType = $request->store_type ?? '';

                $results = ErpSubStoreParent::withDefaultGroupCompanyOrg()
                    ->with(['sub_store' => function ($query) use ($term) {
                        $query->where('status', 'active')
                        ->where('name', 'LIKE', "%$term%");
                    }])
                    ->get(['id', 'store_id', 'sub_store_id']);
            
                // Map the results to include sub store
                $results = $results->map(function ($subStore) {
                    return [
                        'id' => $subStore->sub_store_id,
                        'name' => $subStore->sub_store->name ?? null,
                        'code' => $subStore->sub_store->code ?? null,
                    ];
                });

                if ($results->isEmpty()) {
                    $results = ErpSubStoreParent::withDefaultGroupCompanyOrg()
                        ->with(['sub_store' => function ($query) {
                            $query->where('status', 'active');
                        }])
                        ->limit(10)
                        ->get(['id', 'store_id', 'sub_store_id']);
                
                    // Map the results to include sub store
                    $results = $results->map(function ($subStore) {
                        return [
                            'id' => $subStore->sub_store_id,
                            'name' => $subStore->sub_store->name ?? null,
                            'code' => $subStore->sub_store->code ?? null,
                        ];
                    });
                }
            } else if ($type === "store_rack") {
                $results = ErpRack::where('rack_code', 'LIKE', "%$term%")
                    -> where('erp_store_id', $request -> store_id)
                    ->get(['id', 'rack_code']);
                if ($results->isEmpty()) {
                    $results = ErpRack::where('erp_store_id', $request -> store_id)
                        ->limit(10)
                        ->get(['id', 'rack_code']);
                }
            } else if ($type === "rack_shelf") {
                $results = ErpShelf::where('shelf_code', 'LIKE', "%$term%")
                    -> where('erp_rack_id', $request -> rack_id)
                    ->get(['id', 'shelf_code']);
                if ($results->isEmpty()) {
                    $results = ErpShelf::where('erp_rack_id', $request -> rack_id)
                        ->limit(10)
                        ->get(['id', 'shelf_code']);
                }
            } else if ($type === "shelf_bin") {
                $results = ErpBin::where('bin_code', 'LIKE', "%$term%")
                    -> where('erp_shelf_id', $request -> shelf_id)
                    ->get(['id', 'bin_code']);
                if ($results->isEmpty()) {
                    $results = ErpBin::where('erp_shelf_id', $request -> shelf_id)
                        ->limit(10)
                        ->get(['id', 'bin_code']);
                }
            } elseif ($type === 'salesPerson') {
                $results = Employee::where('name', 'LIKE', "%$term%")
                ->where('status', ConstantHelper::ACTIVE)
                ->where('organization_id', $authUser -> organization_id)
                ->get(['id', 'name']);

                if ($results->isEmpty()) {
                    $results = Employee::where('status', 'active')
                    ->where('organization_id', $authUser -> organization_id)
                        ->limit(10)
                        ->get(['id', 'name']);
                }
            } else if ($type === 'org_services') {
                $results = OrganizationService::withDefaultGroupCompanyOrg() -> where(function ($subQuery) use($term) {
                    $subQuery -> where('name', 'LIKE', '%'.$term.'%') -> orWhere('alias', 'LIKE', '%'.$term.'%');
                })  -> get();
                if ($results->isEmpty()) {
                    $results = OrganizationService::withDefaultGroupCompanyOrg()-> limit(10) ->get();
                }
            } else if ($type === 'vendor_company_list') {
                $vendorId = $authUser?->vendor_portal?->id ?? null;
                $orgIds = PurchaseOrder::where('vendor_id', $vendorId)
                ->distinct()
                ->pluck('organization_id')
                ->toArray();
                $results = Organization::where(function ($subQuery) use($term) {
                    $subQuery -> where('name', 'LIKE', '%'.$term.'%') -> orWhere('alias', 'LIKE', '%'.$term.'%');
                })  -> get();
                if ($results->isEmpty()) {
                    $results = Organization::limit(10) ->get();
                }
            }  else if ($type === 'department') {
                $results = Department::where('organization_id', $organizationId)
                ->where('status', ConstantHelper::ACTIVE)
                ->get();

                if ($results->isEmpty()) {
                    $results = Department::limit(10)
                    ->where('status', ConstantHelper::ACTIVE)
                    ->get();
                }
            } else if ($type === 'all_user_list') {
                $results = AuthUser::select('id', 'name') ->
                where('name', 'LIKE', '%' . $term . '%') -> where('organization_id', $authUser -> organization_id)
                ->where('status', ConstantHelper::ACTIVE)
                ->get();

                if ($results->isEmpty()) {
                    $results = AuthUser::select('id', 'name')
                    -> where('organization_id', $authUser -> organization_id)
                    ->where('status', ConstantHelper::ACTIVE)
                    ->get();
                }
            } elseif ($type === 'unit_code') {
                $query = UnitMaster::where('status', ConstantHelper::ACTIVE);

                $results = $query->when($term, function ($q) use ($term) {
                    return $q->where(function($query) use ($term) {
                        $query->where('unit_code', 'LIKE', "%$term%")
                              ->orWhere('unit_name', 'LIKE', "%$term%");
                    });
                })
                ->limit(20)
                ->get(['id', 'unit_code', 'unit_name']);

                if ($results->isEmpty()) {
                    $results = UnitMaster::where('status', ConstantHelper::ACTIVE)
                        ->limit(10)
                        ->get(['id', 'unit_code', 'unit_name']);
                }
            } elseif ($type === 'hsn_code') {
                $query = HsnMaster::where('status', ConstantHelper::ACTIVE);

                $results = $query->when($term, function ($q) use ($term) {
                    return $q->where(function($query) use ($term) {
                        $query->where('code', 'LIKE', "%$term%")
                              ->orWhere('description', 'LIKE', "%$term%");
                    });
                })
                ->limit(20)
                ->get(['id', 'code','description']);

                if ($results->isEmpty()) {
                    $results = HsnMaster::where('status', ConstantHelper::ACTIVE)
                        ->limit(10)
                        ->get(['id', 'code','description']);
                }
            } else if ($type === 'document_statuses') {
                $documentStatus = [
                    ['id' => 'draft', 'name' => 'Draft'],
                    ['id' => 'submitted', 'name' => 'Submitted'],
                    ['id' => 'approved', 'name' => 'Approved'],
                ];
                $documentStatus = collect($documentStatus)->map(function ($item) {
                    return (object) $item; // Cast array to stdClass
                });
                $results = $documentStatus;
            }  else if ($type === 'report_items') {
                $query = Item::withDefaultGroupCompanyOrg()
                         ->where('status', ConstantHelper::ACTIVE);
                $results = $query->searchByKeywords($term)
                            ->get(['id', 'item_code', 'item_name']);
                if ($results->isEmpty()) {
                    $results = Item::withDefaultGroupCompanyOrg() -> where('status', ConstantHelper::ACTIVE)
                        ->limit(10)
                        ->get(['id', 'item_code','item_name']);
                }
            } else if ($type === 'report_so_documents') {
                $query = ErpSaleOrder::withDefaultGroupCompanyOrg();
                $results = $query->when($term, function ($q) use ($term) {
                    return $q->where(function($query) use ($term) {
                        $query->where('document_number', 'LIKE', "%$term%");
                    });
                }) -> get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = ErpSaleOrder::withDefaultGroupCompanyOrg()
                        ->limit(10)
                        ->get(['id', 'document_number']);
                }
            } else if ($type === 'report_so_book') {
                $service = Service::where('alias', ConstantHelper::SO_SERVICE_ALIAS) -> first();

                $query = Book::withDefaultGroupCompanyOrg() -> where('service_id', $service ?-> id);
                $results = $query->when($term, function ($q) use ($term) {
                    return $q->where(function($query) use ($term) {
                        $query->where('book_code', 'LIKE', "%$term%")
                        ->orWhere('book_name', 'LIKE', "%$term%");
                    });
                }) -> get(['id', 'book_code']);
                if ($results->isEmpty()) {
                    $results = Book::withDefaultGroupCompanyOrg() -> where('service_id', $service ?-> id)
                        ->limit(10)
                        ->get(['id', 'book_code']);
                }
            } else if ($type === 'report_mr_documents') {
                $query = ErpMaterialReturnHeader::withDefaultGroupCompanyOrg();
                $results = $query->when($term, function ($q) use ($term) {
                    return $q->where(function($query) use ($term) {
                        $query->where('document_number', 'LIKE', "%$term%");
                    });
                }) -> get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = ErpMaterialReturnHeader::withDefaultGroupCompanyOrg()
                        ->limit(10)
                        ->get(['id', 'document_number']);
                }
            } else if ($type === 'report_mr_book') {
                $service = Service::where('alias', ConstantHelper::MATERIAL_RETURN_SERVICE_ALIAS_NAME) -> first();

                $query = Book::withDefaultGroupCompanyOrg() -> where('service_id', $service ?-> id);
                $results = $query->when($term, function ($q) use ($term) {
                    return $q->where(function($query) use ($term) {
                        $query->where('book_code', 'LIKE', "%$term%")
                        ->orWhere('book_name', 'LIKE', "%$term%");
                    });
                }) -> get(['id', 'book_code']);
                if ($results->isEmpty()) {
                    $results = Book::withDefaultGroupCompanyOrg() -> where('service_id', $service ?-> id)
                        ->limit(10)
                        ->get(['id', 'book_code']);
                }
            } else if ($type === 'report_mi_documents') {
                $query = ErpMaterialIssueHeader::withDefaultGroupCompanyOrg();
                $results = $query->when($term, function ($q) use ($term) {
                    return $q->where(function($query) use ($term) {
                        $query->where('document_number', 'LIKE', "%$term%");
                    });
                }) -> get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = ErpMaterialIssueHeader::withDefaultGroupCompanyOrg()
                        ->limit(10)
                        ->get(['id', 'document_number']);
                }
            } else if ($type === 'report_mi_book') {
                $service = Service::where('alias', ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME) -> first();

                $query = Book::withDefaultGroupCompanyOrg() -> where('service_id', $service ?-> id);
                $results = $query->when($term, function ($q) use ($term) {
                    return $q->where(function($query) use ($term) {
                        $query->where('book_code', 'LIKE', "%$term%")
                        ->orWhere('book_name', 'LIKE', "%$term%");
                    });
                }) -> get(['id', 'book_code']);
                if ($results->isEmpty()) {
                    $results = Book::withDefaultGroupCompanyOrg() -> where('service_id', $service ?-> id)
                        ->limit(10)
                        ->get(['id', 'book_code']);
                }
            } else if ($type === 'report_si_documents') {
                $query = ErpSaleInvoice::withDefaultGroupCompanyOrg();
                $results = $query->when($term, function ($q) use ($term) {
                    return $q->where(function($query) use ($term) {
                        $query->where('document_number', 'LIKE', "%$term%");
                    });
                }) -> get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = ErpSaleInvoice::withDefaultGroupCompanyOrg()
                        ->limit(10)
                        ->get(['id', 'document_number']);
                }
            } else if ($type === 'report_si_book') {
                $service = Service::whereIn('alias', [ConstantHelper::SI_SERVICE_ALIAS,ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS]) -> get();

                $query = Book::withDefaultGroupCompanyOrg() -> whereIn('service_id', $service->pluck('id'));
                $results = $query->when($term, function ($q) use ($term) {
                    return $q->where(function($query) use ($term) {
                        $query->where('book_code', 'LIKE', "%$term%")
                        ->orWhere('book_name', 'LIKE', "%$term%");
                    });
                }) -> get(['id', 'book_code']);
                if ($results->isEmpty()) {
                    $results = Book::withDefaultGroupCompanyOrg() -> where('service_id', $service ?-> id)
                        ->limit(10)
                        ->get(['id', 'book_code']);
                }
            } else if ($type === 'report_psv_documents') {
                $query = ErpPsvHeader::withDefaultGroupCompanyOrg();
                $results = $query->when($term, function ($q) use ($term) {
                    return $q->where(function($query) use ($term) {
                        $query->where('document_number', 'LIKE', "%$term%");
                    });
                }) -> get(['id', 'document_number']);
                if ($results->isEmpty()) {
                    $results = ErpPsvHeader::withDefaultGroupCompanyOrg()
                        ->limit(10)
                        ->get(['id', 'document_number']);
                }
            } else if ($type === 'report_psv_book') {
                $service = Service::where('alias', ConstantHelper::PSV_SERVICE_ALIAS) -> first();

                $query = Book::withDefaultGroupCompanyOrg() -> where('service_id', $service ?-> id);
                $results = $query->when($term, function ($q) use ($term) {
                    return $q->where(function($query) use ($term) {
                        $query->where('book_code', 'LIKE', "%$term%")
                        ->orWhere('book_name', 'LIKE', "%$term%");
                    });
                }) -> get(['id', 'book_code']);
                if ($results->isEmpty()) {
                    $results = Book::withDefaultGroupCompanyOrg() -> where('service_id', $service ?-> id)
                        ->limit(10)
                        ->get(['id', 'book_code']);
                }
            } else if ($type === 'organizations') {
                $applicableOrgIds = $authUser -> organizations -> pluck('id') -> toArray();
                $results = Organization::whereIn('id', $applicableOrgIds) -> when($request -> organization_id, function ($compFilter) use($request) {
                    $compFilter -> where('company_id', $request -> company_id);
                }) -> where('name', 'LIKE', '%' . $term . '%') ->
                where('status', ConstantHelper::ACTIVE) -> get(['id', 'name']);
                if ($results -> isEmpty()) {
                    $results = Organization::whereIn('id', $applicableOrgIds) -> when($request -> organization_id, function ($compFilter) use($request) {
                        $compFilter -> where('company_id', $request -> company_id);
                    }) -> where('status', ConstantHelper::ACTIVE)
                    -> limit(10) -> get(['id', 'name']);
                }
            } else if ($type === 'companies') {
                $applicableOrgIds = $authUser -> organizations -> pluck('id') -> toArray();
                $applicableCompIds = Organization::whereIn('id', $applicableOrgIds) ->
                where('status', ConstantHelper::ACTIVE) -> pluck('company_id');
                $results = OrganizationCompany::whereIn('id', $applicableCompIds) ->
                 where('name', 'LIKE', '%' . $term . '%') -> get(['id', 'name']);
                if ($results -> isEmpty()) {
                    $results = OrganizationCompany::whereIn('id', $applicableCompIds) ->
                         limit(10) -> get(['id', 'name']);
                }
            } else if ($type == 'cash_customer_email') {
                $results = CashCustomerDetail::whereHas('customer', function ($custQuery) {
                    $custQuery -> withDefaultGroupCompanyOrg();
                }) ->when($term, function ($emailQuery) use($term) {
                    $emailQuery -> where('email', 'LIKE', '%' . $term . '%');
                })  -> where(function ($custQuery) {
                    $custQuery -> whereNotNull('email') -> whereRaw("TRIM(email) != ''");
                }) -> orderByDesc('id') -> limit(10) -> get();
            } else if ($type == 'cash_customer_phone_no') {
                $results = CashCustomerDetail::whereHas('customer', function ($custQuery) {
                    $custQuery -> withDefaultGroupCompanyOrg();
                }) -> when($term, function ($emailQuery) use($term) {
                    $emailQuery -> where('phone_no', 'LIKE', '%' . $term . '%');
                }) -> where(function ($custQuery) {
                    $custQuery -> whereNotNull('phone_no') -> whereRaw("TRIM(phone_no) != ''");
                }) -> orderByDesc('id') -> limit(10) -> get();
            } else if ($type == 'cash_customer_consignee') {
                $results = CashCustomerDetail::whereHas('customer', function ($custQuery) {
                    $custQuery -> withDefaultGroupCompanyOrg();
                }) -> when($term, function ($emailQuery) use($term) {
                    $emailQuery -> where('name', 'LIKE', '%' . $term . '%');
                }) -> where(function ($custQuery) {
                    $custQuery -> whereNotNull('name') -> whereRaw("TRIM(name) != ''");
                }) -> orderByDesc('id') -> limit(10) -> get();
            } else if ($type == 'cash_customer_gstin') {
                $results = CashCustomerDetail::whereHas('customer', function ($custQuery) {
                    $custQuery -> withDefaultGroupCompanyOrg();
                }) -> when($term, function ($emailQuery) use($term) {
                    $emailQuery -> where('gstin', 'LIKE', '%' . $term . '%');
                })  -> where(function ($custQuery) {
                    $custQuery -> whereNotNull('gstin') -> whereRaw("TRIM(gstin) != ''");
                }) -> orderByDesc('id') -> limit(10) -> get();
            }  elseif ($type === 'item_attr_value') {
                $itemId = $request->item_id;
                $groupId = $request->attr_group_id;
                $itemAttribute = ItemAttribute::where('item_id', $itemId)
                    ->where('attribute_group_id', $groupId)
                    ->first();
                $attributeIds = $itemAttribute->attribute_id ?? [];
                $results = Attribute::whereIn('id', $attributeIds)
                    ->where('status', ConstantHelper::ACTIVE)
                    ->where('value', 'like', '%' . $term . '%')
                    ->get(['id', 'value']);
            } elseif ($type === 'stock_orgs') {
                $orgIds = $authUser -> access_rights_org -> pluck('organization_id') -> toArray();
                array_push($orgIds, $authUser -> organization_id);
                $results = Organization::select('id', 'name') -> whereIn('id', $orgIds) 
                    -> where('status', ConstantHelper::ACTIVE)
                    -> when($term, function ($termQuery) use($term) {
                        $termQuery -> where('name','LIKE', '%'.$term.'%');
                    }) -> limit(10) -> get();
            } elseif ($type === 'stock_locations') {
                $selectedOrg = $request -> organization_id ?? null;
                $results = ErpStore::select('id', 'store_name') -> withDefaultGroupCompanyOrg()
                ->when(($authUser->authenticable_type == "employee"), function ($locationQuery) use($authUser) { // Location with same country and state
                    $locationQuery->whereHas('employees', function ($employeeQuery) use ($authUser) {
                        $employeeQuery->where('employee_id', $authUser->id);
                    });
                }) -> when($term, function ($termQuery) use($term) {
                    $termQuery -> where('store_name', 'LIKE', '%'.$term.'%');
                }) -> where('organization_id', $selectedOrg) -> where('status', ConstantHelper::ACTIVE) -> limit(10) -> get();
            } elseif ($type === 'stock_sub_locations') {
                $selectedLocation = $request -> location_id ?? null;
                $results = ErpSubStore::select('id', 'name') -> when(true, function ($locationQuery) use($selectedLocation) {
                    $locationQuery -> whereHas('parents', function ($parentQuery) use($selectedLocation) {
                        $parentQuery -> where('store_id', $selectedLocation);
                    });
                }) -> when($term, function ($termQuery) use($term) {
                    $termQuery -> where('name', 'LIKE', '%'.$term.'%');
                }) -> where('status', ConstantHelper::ACTIVE) -> limit(10) -> get();
            } else {
                return response()->json(['error' => 'Invalid type specified'], 400);
            }

            return response()->json($results);
        } catch (\Exception $e) {
            \Log::error('Autocomplete search failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
