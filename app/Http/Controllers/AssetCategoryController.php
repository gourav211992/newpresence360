<?php

namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Models\ErpAssetCategory;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;


class AssetCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $categories = ErpAssetCategory::orderBy('id', 'ASC')
            ->get();

            return DataTables::of($categories)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return '<span class="badge rounded-pill ' . ($row->status == 'active' ? 'badge-light-success' : 'badge-light-danger') . ' badgeborder-radius">
                                ' . ucfirst($row->status) . '
                            </span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('asset-category.edit', $row->id);
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

        return view('asset-category.index');
    
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $status = ConstantHelper::STATUS;
        return view('asset-category.create', compact('status'));
   
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $additionalData = [
            'created_by' => $user->id,
            'type' => get_class($user),
            'organization_id' => $user->organization->id,
            'group_id' => $user->organization->group_id,
            'company_id' => $user->organization->company_id,
        ];
        $asset = ErpAssetCategory::where('name', $request->name)->first();
        

        $data = array_merge($request->all(), $additionalData);

        try {
            if ($asset) {
                return response()->json([
                    'status' => false,
                    'message' => 'Category already exist',
                    'error' => "Category already exists",
                ], 500);
                }
            else{
            $category = ErpAssetCategory::create($data);
            return response()->json([
                'status' => true,
                'message' => 'Record created successfully',
                'data' => $category,
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while creating the record',
            'error' => $e->getMessage(),
        ], 500);
    }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $category = ErpAssetCategory::findOrFail($id); 
        $status = ConstantHelper::STATUS;
        return view('asset-category.edit', compact('category', 'status'));
    
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $asset = ErpAssetCategory::find($id);

        if (!$asset) {
            return redirect()
                ->route('asset-category.index')
                ->with('error', 'Category not found.');
        }

        $data = $request->all();

        try {
            $asset->update($data);
            
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
                'data' => $asset,
            ]);
            } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
