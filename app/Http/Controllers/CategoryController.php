<?php

namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\CategoryRequest;
use App\Helpers\ConstantHelper;
use App\Models\Item;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Auth;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $currentUrlSegment = request()->segment(1);
        if ($request->ajax()) {
            $categories = Category::orderBy('id', 'desc')
             ->with('parent', 'subCategories'); 
             if ($currentUrlSegment === 'equipment-categories') {
                $categories->where('type', strtolower(ConstantHelper::EQUIPMENT));
            } else {
                $categories->whereIn('type', ConstantHelper::CATEGORY_TYPES);
            }
            return DataTables::of($categories)
                ->addIndexColumn()
                ->addColumn('parent_category', function($row) {
                    return $row->parent ? $row->parent->name : 'N/A';
                })
                 ->editColumn('type', function($row) {
                    return $row->type ? $row->type : 'N/A';
                })
                ->addColumn('last_level', function($row) {
                    if ($row->subCategories()->exists()) {
                        return '-';
                    } else {
                        return '<i data-feather="check-circle" style="color: green;"></i>';
                    }
                })
                ->addColumn('status', function ($row) {
                    return '<span class="badge rounded-pill ' . ($row->status == 'active' ? 'badge-light-success' : 'badge-light-danger') . ' badgeborder-radius">
                                ' . ucfirst($row->status) . '
                            </span>';
                })
                ->addColumn('action', function ($row) use ($currentUrlSegment) {
                 $editUrl = ($currentUrlSegment === 'equipment-categories')
                    ? route('equipment-categories.edit', $row->id)
                    : route('categories.edit', $row->id);
                    return '<div class="dropdown">
                                <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                    <i data-feather="more-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="' . $editUrl . '">
                                       <i data-feather="edit-3" class="me-50"></i>
                                        <span>Edit</span>
                                    </a>
                                </div>
                            </div>';
                })

                ->rawColumns(['status','last_level','action'])
                ->make(true);
        }
          return view('procurement.category.index',compact('currentUrlSegment'));
    }
    
    
    public function create()
    {
        $categoryTypes = ConstantHelper::CATEGORY_TYPES;
        $currentUrlSegment = request()->segment(1); 
        if ($currentUrlSegment === 'equipment-categories') {
            $categoryTypes = [ConstantHelper::EQUIPMENT];
        }
        $status = ConstantHelper::STATUS;
        return view('procurement.category.create', [
            'categoryTypes' => $categoryTypes,
            'currentUrlSegment'=>$currentUrlSegment,
            'status' => $status,
        ]);
    }

    public function store(CategoryRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = $request->validated();
        $parentUrl = ConstantHelper::CATEGORY_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if ($services && $services['services'] && $services['services']->isNotEmpty()) {
            $firstService = $services['services']->first();
            $serviceId = $firstService->service_id;
            $policyData = Helper::getPolicyByServiceId($serviceId);
            if ($policyData && isset($policyData['policyLevelData'])) {
                $policyLevelData = $policyData['policyLevelData'];
                $validatedData['group_id'] = $policyLevelData['group_id'];
                $validatedData['company_id'] = $policyLevelData['company_id'];
                $validatedData['organization_id'] = $policyLevelData['organization_id'];
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = $organization->company_id;
                $validatedData['organization_id'] = null;
            }
        } else {
            $validatedData['group_id'] = $organization->group_id;
            $validatedData['company_id'] = $organization->company_id;
            $validatedData['organization_id'] = null;
        }

        if (!empty($validatedData['parent_id'])) {
            $validatedData['sub_cat_initials'] = $validatedData['cat_initials'];
            $validatedData['cat_initials'] = null;
        } else {
            $validatedData['sub_cat_initials'] = null;  
        }
        DB::beginTransaction(); 

        try {
            
            $category = Category::create([
                'type' => $validatedData['type'],
                'parent_id' => $validatedData['parent_id'],
                'name' => $validatedData['name'],
                'cat_initials' => $validatedData['cat_initials'], 
                'sub_cat_initials' => $validatedData['sub_cat_initials'], 
                'hsn_id' => $validatedData['hsn_id'],
                'inspection_checklist_id' => $validatedData['inspection_checklist_id'],
                'status' => $validatedData['status'],
                'organization_id' => $validatedData['organization_id'], 
                'group_id' => $validatedData['group_id'],
                'company_id' => $validatedData['company_id'],
            ]);
            
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Record created successfully',
                'data' => $category,
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); 

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    public function show(Category $category)
    {
        //
    }

    public function edit($id)
    {
        $category = Category::with('subCategories')->findOrFail($id); 
        $categoryType = $category->type; 
        $allChildCategoryIds = $category->load('subCategories')->getAllNestedSubCategoryIds();
        $usedCategoryIds = Item::whereNotNull('subcategory_id')->pluck('subcategory_id')->toArray();
        $excludeIds = array_merge([$category->id], $allChildCategoryIds,$usedCategoryIds);
        $categories = Category::select('id', 'name', 'parent_id')
            ->with('parent')
            ->whereNotIn('id', $excludeIds)
            ->where(function ($query) use ($categoryType) {
                if ($categoryType === 'Product') {
                    $query->where('type', 'Product');
                } elseif ($categoryType === 'Customer') {
                    $query->where('type', 'Customer');
                } elseif ($categoryType === 'Vendor') {
                    $query->where('type', 'Vendor');
                } elseif ($categoryType === 'Equipment') {
                    $query->where('type', 'Equipment');
                }
            })
            ->get();
            $isLastLevel = $category->subCategories()->exists() ? 0 : 1;
            $categoryTypes = ConstantHelper::CATEGORY_TYPES;
            $currentUrlSegment = request()->segment(1); 
            if ($currentUrlSegment === 'equipment-categories') {
                $categoryTypes = [ConstantHelper::EQUIPMENT];
            }
            $status = ConstantHelper::STATUS;
       
        return view('procurement.category.edit', [
            'category' => $category,
            'categoryTypes' => $categoryTypes,
            'currentUrlSegment'=>$currentUrlSegment,
            'status' => $status,
            'categories' => $categories,
            'isLastLevel'=>$isLastLevel,
        ]);
    }
    
    public function update(CategoryRequest $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = $request->validated();
        $parentUrl = ConstantHelper::CATEGORY_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if ($services && $services['services'] && $services['services']->isNotEmpty()) {
            $firstService = $services['services']->first();
            $serviceId = $firstService->service_id;
            $policyData = Helper::getPolicyByServiceId($serviceId);
            if ($policyData && isset($policyData['policyLevelData'])) {
                $policyLevelData = $policyData['policyLevelData'];
                $validatedData['group_id'] = $policyLevelData['group_id'];
                $validatedData['company_id'] = $policyLevelData['company_id'];
                $validatedData['organization_id'] = $policyLevelData['organization_id'];
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = $organization->company_id;
                $validatedData['organization_id'] = null;
            }
        } else {
            $validatedData['group_id'] = $organization->group_id;
            $validatedData['company_id'] = $organization->company_id;
            $validatedData['organization_id'] = null;
        }
        if (!empty($validatedData['parent_id'])) {
            $validatedData['sub_cat_initials'] = $validatedData['cat_initials'];
            $validatedData['cat_initials'] = null;
        } else {
            $validatedData['sub_cat_initials'] = null;  
        }
        DB::beginTransaction(); 
        try {
            $category = Category::findOrFail($id);

                $category->update([
                    'type' => $validatedData['type'],
                    'parent_id' => $validatedData['parent_id'],
                    'name' => $validatedData['name'],
                    'hsn_id' => $validatedData['hsn_id'],
                    'cat_initials' => $validatedData['cat_initials'],
                    'sub_cat_initials' => $validatedData['sub_cat_initials'],
                    'inspection_checklist_id' => $validatedData['inspection_checklist_id'],
                    'status' => $validatedData['status'],
                    'organization_id' => $validatedData['organization_id'], 
                    'group_id' => $validatedData['group_id'],
                    'company_id' => $validatedData['company_id'],
                ]);

                DB::commit(); 

                return response()->json([
                    'status' => true,
                    'message' => 'Record updated successfully',
                    'data' => $category,
                ]);
    
            } catch (\Exception $e) {
                DB::rollBack(); 
                return response()->json([
                    'status' => false,
                    'message' => 'An error occurred while updating the record',
                    'error' => $e->getMessage(),
                ], 500);
            }
    }

    public function getCategoriesByType(Request $request)
    {
        $type = $request->input('type');
        $usedCategoryIds = Item::whereNotNull('subcategory_id')->pluck('subcategory_id')->toArray();
        $categories = Category::where('type', $type)
            ->with('parent')
            ->where('status', ConstantHelper::ACTIVE)
            ->whereNotIn('id', $usedCategoryIds) 
            ->select('id','parent_id','name')
            ->get();
          
        return response()->json($categories);
    }
    
    public function getHsnByParent(Request $request)
    {
        $parentId = $request->input('parent_id');
    
        $parentCategory = Category::where('id', $parentId)
            ->with('hsn') 
            ->where('status', ConstantHelper::ACTIVE)
            ->first();
    
        if ($parentCategory && isset($parentCategory->hsn->code) && isset($parentCategory->hsn->id)) {
            return response()->json([
                'hsn' => $parentCategory->hsn->code,
                'hsn_id' => $parentCategory->hsn->id,
            ]);
        }
        return response()->json(['hsn' => '', 'hsn_id' => '']);
    }
    
    public function deleteSubcategory($id)
    {
        DB::beginTransaction();
        try {
            $subcategory = Category::findOrFail($id);
            $referenceTables = [
                'erp_categories' => ['id'], 
            ];
            $result = $subcategory->deleteWithReferences($referenceTables);
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? [],
                ], 400);
            }
            DB::commit(); 

            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack(); 

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the category: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        DB::beginTransaction(); 

        try {
            $category = Category::findOrFail($id);
            $referenceTables = [
                'erp_categories' => ['parent_id'], 
            ];
            $result = $category->deleteWithReferences($referenceTables);
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }
    
            DB::commit(); 

            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the subcategory: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    public function getSubcategories($categoryId)
    {
        $subcategories = Category::where('parent_id', $categoryId)
            ->get();
    
        return response()->json($subcategories);
    }
    
}