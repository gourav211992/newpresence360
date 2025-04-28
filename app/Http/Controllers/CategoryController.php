<?php

namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\CategoryRequest;
use App\Helpers\ConstantHelper;
use App\Models\Organization;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Auth;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first(); 
        $organizationId = $organization?->id ?? null;
        $companyId = $organization?->company_id ?? null;
        if ($request->ajax()) {
            $categories = Category::WithDefaultGroupCompanyOrg()
            ->whereNull('parent_id')
            ->orderBy('id', 'desc');
            return DataTables::of($categories)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return '<span class="badge rounded-pill ' . ($row->status == 'active' ? 'badge-light-success' : 'badge-light-danger') . ' badgeborder-radius">
                                ' . ucfirst($row->status) . '
                            </span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('categories.edit', $row->id);
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

                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('procurement.category.index');
    }
    
    
    public function create()
    {
        $categoryTypes = ConstantHelper::CATEGORY_TYPES;
        $status = ConstantHelper::STATUS;
        return view('procurement.category.create', compact('categoryTypes','status'));
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
                $validatedData['company_id'] = null;
                $validatedData['organization_id'] = null;
            }
        } else {
            $validatedData['group_id'] = $organization->group_id;
            $validatedData['company_id'] = null;
            $validatedData['organization_id'] = null;
        }
        DB::beginTransaction(); 

        try {
            $category = Category::create([
                'type' => $validatedData['type'],
                'name' => $validatedData['name'],
                'cat_initials' => $validatedData['cat_initials'],
                'hsn_id' => $validatedData['hsn_id'],
                'status' => $validatedData['status'],
                'organization_id' => $validatedData['organization_id'], 
                'group_id' => $validatedData['group_id'],
                'company_id' => $validatedData['company_id'],
            ]);
    
            $subCategories = $validatedData['subcategories'] ?? [];
            foreach ($subCategories as $subCategory) {
                if (!empty($subCategory['name'])) {
                    Category::create([
                        'type' => $category->type,
                        'parent_id' => $category->id,
                        'name' => $subCategory['name'],
                        'sub_cat_initials' => $subCategory['sub_cat_initials'],
                        'organization_id' => $validatedData['organization_id'], 
                        'group_id' => $validatedData['group_id'],
                        'company_id' => $validatedData['company_id'],
                    ]);
                }
            }
    
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
        $categoryTypes = ConstantHelper::CATEGORY_TYPES;
        $status = ConstantHelper::STATUS;
        return view('procurement.category.edit', compact('category', 'categoryTypes', 'status'));
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
                $validatedData['company_id'] = null;
                $validatedData['organization_id'] = null;
            }
        } else {
            $validatedData['group_id'] = $organization->group_id;
            $validatedData['company_id'] = null;
            $validatedData['organization_id'] = null;
        }
        DB::beginTransaction(); 

        try {
            $category = Category::findOrFail($id);
            $category->update([
                'type' => $validatedData['type'],
                'name' => $validatedData['name'] ?? '',
                'hsn_id' => $validatedData['hsn_id'],
                'cat_initials' => $validatedData['cat_initials'],
                'status' => $validatedData['status'],
            ]);

          $subCategories = $validatedData['subcategories'] ?? [];
            $newCategoryIds = [];

            foreach ($subCategories as $subCategory) {
                $subCategoryId = $subCategory['id'] ?? null;
                
                if ($subCategoryId) {
                    $sub = Category::find($subCategoryId);
                    if ($sub) {
                        $sub->update([
                            'name' => $subCategory['name'] ?? '',
                            'sub_cat_initials' => $subCategory['sub_cat_initials'],
                            'type' => $category->type,
                            'parent_id' => $category->id,
                            'organization_id' => $validatedData['organization_id'], 
                            'group_id' => $validatedData['group_id'],
                            'company_id' => $validatedData['company_id'],
                        ]);
                    }
                } else {
                    $sub = Category::create([
                        'name' => $subCategory['name'] ?? '',
                        'sub_cat_initials' => $subCategory['sub_cat_initials'],
                        'type' => $category->type,
                        'parent_id' => $category->id,
                        'hsn_id' => $category['hsn_id'],
                        'organization_id' => $validatedData['organization_id'], 
                        'group_id' => $validatedData['group_id'],
                        'company_id' => $validatedData['company_id'],
                    ]);
                }

                $newCategoryIds[] = $sub->id;
            }
            Category::where('parent_id', $category->id)
                ->whereNotIn('id', $newCategoryIds)
                ->delete();

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
            ->WithDefaultGroupCompanyOrg()
            ->get();
    
        return response()->json($subcategories);
    }
    
}