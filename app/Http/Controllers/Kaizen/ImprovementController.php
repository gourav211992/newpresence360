<?php

namespace App\Http\Controllers\Kaizen;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Kaizen\ErpKaizenImprovement;
use App\Http\Requests\Kaizen\ErpKaizenImprovementRequest;

class ImprovementController extends Controller
{
    public function index(Request $request)
    {

        
        $user = Helper::getAuthenticatedUser();
        $organizationId = $user?->organization_id ?? null;
        $groupId = $user->organization ? $user->organization->group_id : null; 
        // ErpKaizenImprovement::query()->update(['group_id' => $groupId]);

        if ($request->ajax()) {
            $erpKaizenImprovements = ErpKaizenImprovement::where('organization_id', $organizationId)
                ->orderBy('id', 'desc');
                return DataTables::of($erpKaizenImprovements)
                    ->addIndexColumn()
                    ->editColumn('type', function ($imp) {
                        return $imp->type ?? 'N/A';
                })
                ->editColumn('description', function ($imp) {
                    return $imp->description ?? 'N/A';
                })
                ->editColumn('marks', function ($imp) {
                    return $imp->marks ?? 0;
                })
                ->editColumn('status', function ($imp) {
                    $statusClass = 'badge-light-secondary';
            
                    if ($imp->status == 'active') {
                        $statusClass = 'badge-light-success';
                    } elseif ($imp->status == 'inactive') {
                        $statusClass = 'badge-light-danger';
                    } elseif ($imp->status == 'draft') {
                        $statusClass = 'badge-light-warning';
                    }
                    return '<span class="badge rounded-pill ' . $statusClass . ' badgeborder-radius">'
                        . ucfirst($imp->status ?? 'Unknown') . '</span>';
                })
                ->addColumn('actions', function ($imp) {
                    return '
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i data-feather="more-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#" class="dropdown-item text-warning edit-btn"
                                    data-id="' . $imp->id . '"
                                    data-type="' . $imp->type . '"
                                    data-description="' . $imp->description . '"
                                    data-marks="' . $imp->marks . '" 
                                    data-status="' . $imp->status . '">
                                    <i data-feather="edit" class="me-50"></i> Edit
                                </a>
                                <a href="#" class="dropdown-item text-danger delete-btn" 
                                data-url="' . route('improvement-masters.destroy', $imp->id) . '" 
                                data-message="Are you sure you want to delete this Improvement Master?">
                                    <i data-feather="trash-2" class="me-50"></i> Delete
                                </a>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns(['status', 'actions']) 
                ->make(true);
        }
            
        $status = ConstantHelper::STATUS;

        return view('kaizen.evaluation.index', compact('status'));
    }
    
    public function store(ErpKaizenImprovementRequest $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;

            $organization_id = $organization ? $organization->id : null;
            $group_id = $organization ? $organization->group_id : null; 
            $company_id = $organization ? $organization->company_id : null;

            $validated['organization_id']=$organization_id;
            $validated['group_id']=$group_id;
            $validated['company_id']=$company_id;
          
            $erpKaizenImprovement = ErpKaizenImprovement::create($validated);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record created successfully',
                'data' => $erpKaizenImprovement,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the Kaizen Improvement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(ErpKaizenImprovementRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $erpKaizenImprovement = ErpKaizenImprovement::findOrFail($id);
            $erpKaizenImprovement->update($validated);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
                'data' => $erpKaizenImprovement,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the Kaizen Improvement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            ErpKaizenImprovement::whereId($id)->delete();


            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the Kaizen Improvement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pdfView(Request $request){
        $user = Helper::getAuthenticatedUser();
        $organizationId = $user?->organization_id ?? null;

        $types = ErpKaizenImprovement::select('type')->distinct()->pluck('type');
        $selects = ['marks'];

        foreach ($types as $type) {
            $selects[] = DB::raw("MAX(CASE WHEN type = '{$type}' THEN description END) AS `{$type}`");
        }

        $data = ErpKaizenImprovement::select($selects)->where('marks', '!=', 0)->where('organization_id', $organizationId)
            ->groupBy('marks')
            ->orderBy('marks')
            ->get();
     
        $pdf = PDF::loadView('kaizen.evaluation.pdf', [
            'data' => $data,
        ]);

        return $pdf->download('kaizen-evaluation.pdf');

    }
}
