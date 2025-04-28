<?php

namespace App\Http\Controllers;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Http\Requests\DPRTemplateRequest;
use App\Models\ErpDprMaster;
use App\Models\ErpDprTemplateMaster;
use App\Models\Organization;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DPRTemplateController extends Controller
{
    public function index(Request $request)
    {

        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationId = $organization?->id ?? null;
        $companyId = $organization?->company_id ?? null;

        if ($request->ajax()) {
            $dpr_templates = ErpDprTemplateMaster::with('dpr')->get();

            return DataTables::of($dpr_templates)
                ->addIndexColumn()
                ->addColumn('template_name', function ($row) {
                    return $row->template_name;
                })
                ->addColumn('status', function ($row) {
                    return '<span class="badge rounded-pill ' . ($row->status == 'active' ? 'badge-light-success' : 'badge-light-danger') . ' badgeborder-radius">
                                ' . ucfirst($row->status) . '
                            </span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('dpr-template.edit', $row->id);
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
                ->rawColumns(['template_name', 'status', 'action'])
                ->make(true);

        }

        return view('procurement.dpr_templates.index');
    }

    public function create()
    {
        $status = ConstantHelper::STATUS;
        return view('procurement.dpr_templates.create', [
            'status' => $status,
        ]);
    }

    public function store(DPRTemplateRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validated = $request->validated();
        
        ErpDprTemplateMaster::create([
            'template_name' => $validated['name'],
            'status' => $validated['status'] ?? ConstantHelper::ACTIVE,
            'organization_id' => $organization->id,
            'group_id' => $organization->group_id,
            'company_id' => $organization->company_id,
        ]);
        
        return redirect()->route('dpr-template.index');
    }

    public function edit($id)
    {
        $drp_template = ErpDprTemplateMaster::find($id);
        $status = ConstantHelper::STATUS;
        return view('procurement.dpr_templates.edit', [
            'status' => $status,
            'drp_template' => $drp_template,
        ]);
    }

    public function update(DPRTemplateRequest $request, $id)
    {
        $validated = $request->validated();
        $dpr_template = ErpDprTemplateMaster::findOrFail($id);
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
       
        $dpr_template->update([
            'template_name' => $validated['name'],
            'status' => $validated['status'] ?? ConstantHelper::ACTIVE,
            'organization_id' => $organization->id,
            'group_id' => $organization->group_id,
            'company_id' => $organization->company_id,
        ]);
          
        return redirect()->route('dpr-template.index');
    }
    
    public function destroy($id)
    {
        // try {
        //     $attributeGroup = ErpDprTemplateMaster::findOrFail($id);
        //     $referenceTables = [
        //         'erp_attributes' => ['attribute_group_id'], 
        //     ];
        //     $result = $attributeGroup->deleteWithReferences($referenceTables);
        //     if (!$result['status']) {
        //         return response()->json([
        //             'status' => false,
        //             'message' => $result['message'],
        //             'referenced_tables' => $result['referenced_tables'] ?? []
        //         ], 400);
        //     }
        //     return response()->json([
        //         'status' => true,
        //         'message' => 'Record deleted successfully',
        //     ], 200);
            
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'An error occurred while deleting the attribute group: ' . $e->getMessage()
        //     ], 500);
        // }
    }

}
