<?php
namespace App\Http\Controllers;

use App\Traits\Deletable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

use App\Helpers\Helper;
use App\Helpers\ConstantHelper;

use App\Models\Organization;
use App\Models\UserOrganizationMapping;

use App\Models\WhLevel;
use App\Models\WhDetail;
use App\Models\WhStructure;

use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\ErpSubStoreParent;

use Illuminate\Http\Request;
use App\Http\Requests\WhStructureRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class WarehouseStructureController extends Controller
{

    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        if ($request->ajax()) {
            $records = WhStructure::withDefaultGroupCompanyOrg()
                ->get();

            return DataTables::of($records)
                ->addIndexColumn()
                ->addColumn('store', function ($row) {
                    return $row->store ? $row->store?->store_name : 'N/A';
                })
                ->addColumn('sub_store', function ($row) {
                    return $row->sub_store ? $row->sub_store?->name : 'N/A';
                })
                ->addColumn('levels', function ($pr) {
                    if ($pr->levels && $pr->levels->isNotEmpty()) {
                        $levelCount = $pr->levels->count();
                        $displayLevels = $pr->levels->take(3)->map(function ($level) {
                            return '<span class="badge rounded-pill badge-light-secondary badgeborder-radius">' . $level->name . '</span>';
                        })->implode('');

                        if ($levelCount > 3) {
                            $remainingCount = $levelCount - 3;
                            $displayLevels .= '<span class="badge rounded-pill badge-light-primary badgeborder-radius">+' . $remainingCount . '</span>';
                        }

                        return $displayLevels;
                    }
                    return '<input type="text" class="form-control" value="N/A" readonly>';
                })
                ->addColumn('status', function ($pr) {
                    return '<span class="badge rounded-pill badge-light-' . ($pr->status == 'active' ? 'success' : 'danger') . '">'
                        . ucfirst($pr->status) . '</span>';
                })
                ->addColumn('action', function ($pr) {
                    $editUrl = route('warehouse-structure.edit', $pr->id);
                    $deleteUrl = route('warehouse-structure.delete', $pr->id);
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
                ->rawColumns(['levels', 'status', 'action'])
                ->make(true);
        }

        return view('procurement.warehouse-structure.index');
    }

    public function create()
    {
        $user = Helper::getAuthenticatedUser();
        $status = ConstantHelper::STATUS;
        $stores = ErpStore::withDefaultGroupCompanyOrg()
            ->get();
        return view('procurement.warehouse-structure.create', [
            'user' => $user,
            'status' => $status,
            'stores' => $stores,
        ]);
    }

    public function store(WhStructureRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        DB::beginTransaction();
        try {
            $levels = $request->all()['levels'];
            if(!$levels){
                return response()->json([
                    'message' => "Levels for this warehouse required.",
                    'error' => "Levels for this warehouse required.",
                ], 422);
            }

            $organization = Organization::where('id', $user->organization_id)->first();
            $organizationId = $organization?->id ?? null;
            $groupId = $organization?->group_id ?? null;
            $companyId = $organization?->company_id ?? null;
            // Header Save
            $whStructure = new WhStructure();
            $whStructure->fill($request->all());
            $whStructure->organization_id = $organizationId;
            $whStructure->group_id = $groupId;
            $whStructure->company_id = $companyId;
            $whStructure->save();

            // Level Save
            if (isset($request->all()['levels'])) {
                $previousLevel = null;
                
                foreach ($request->all()['levels'] as $l_key => $level) {
                    $whLevel = new WhLevel();
                    $whLevel->wh_structure_id = $whStructure->id;
                    $whLevel->store_id = $whStructure->store_id;
                    $whLevel->sub_store_id = $whStructure->sub_store_id;
                    $whLevel->level = $level['level'] ?? null;
                    $whLevel->name = $level['name'] ?? null;
                    $whLevel->status = 'active';
                    $whLevel->parent_id = $previousLevel ? $previousLevel->id : null; // Set parent_id to the previous level's ID
                    $whLevel->save();

                    $previousLevel = $whLevel; // Update the previous level to the current one
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Record created successfully',
                'data' => $whStructure
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $user = Helper::getAuthenticatedUser();
        $status = ConstantHelper::STATUS;
        $stores = ErpStore::withDefaultGroupCompanyOrg()
            ->get();
        // Fetch the production route with levels and related station details
        $whStructure = WhStructure::with(['levels' => function ($e) {
            $e->orderBy('level');
        }])->findOrFail($id);
        return view('procurement.warehouse-structure.edit', [
            'status' => $status,
            'stores' => $stores,
            'whStructure' => $whStructure,
        ]);
    }

    public function update(WhStructureRequest $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        DB::beginTransaction();
        try {
            // Structure Update
            $whStructure = WhStructure::find($id);
            $whStructure->status = $request->all()['status'];
            $whStructure->save();

            // Level Update
            if (isset($request->all()['levels'])) {
                $previousLevel = null;
                $existingLevelIds = WhLevel::where('wh_structure_id', $whStructure->id)->pluck('id')->toArray();
                $updatedLevelIds = [];
                
                foreach ($request->all()['levels'] as $l_key => $level) {
                    $whLevelId = $level['l_id'] ?? null;
                    $whLevel = $whLevelId ? WhLevel::find($whLevelId) : new WhLevel;
                
                    $whLevel->wh_structure_id = $whStructure->id;
                    $whLevel->store_id = $whStructure->store_id;
                    $whLevel->sub_store_id = $whStructure->sub_store_id;
                    $whLevel->level = $level['level'] ?? null;
                    $whLevel->name = $level['name'] ?? null;
                    $whLevel->status = 'active';
                
                    if (!$whLevelId) {
                        $whLevel->parent_id = $previousLevel ? $previousLevel->id : null; // Set parent_id to the previous level's ID
                    }
                
                    $whLevel->save();
                    $updatedLevelIds[] = $whLevel->id;
                
                    $previousLevel = $whLevel; // Update the previous level to the current one
                }
                
                // Delete levels that are no longer present in the request
                $levelsToDelete = array_diff($existingLevelIds, $updatedLevelIds);
                WhLevel::whereIn('id', $levelsToDelete)->forceDelete();
            }

            DB::commit();
            return response()->json([
                'message' => 'Record updated successfully',
                'data' => '$pRoute'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        $pRoute = WhStructure::findOrFail($id);
        return redirect()->route("warehouse-structure.index")->with('success', 'Record deleted successfully.');
    }

    // Delete Level
    public function deleteLevel(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        DB::beginTransaction();
        try {
            $whLevel = WhLevel::findOrFail($request->id);
            $whMapping = WhDetail::where('wh_level_id', $whLevel->id)->first();
            if ($whMapping) {
                return response()->json([
                    'message' => 'This level is already mapped to a warehouse mapping. Please remove the mapping first.',
                    'error' => 'This level is already mapped to a warehouse mapping. Please remove the mapping first.',
                ], 422);
            }
            $whLevel->delete();

            DB::commit();
            return response()->json([
                'message' => 'Selected rows have been deleted.',
                'data' => $whLevel
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while deleting the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
