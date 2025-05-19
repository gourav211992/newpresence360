<?php

namespace App\Http\Controllers;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Http\Requests\DPRMasterRequest;
use App\Models\ErpDprMaster;
use App\Models\ErpDprTemplateMaster;
use App\Models\Organization;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ErpDprMasterController extends Controller
{
    public function index(Request $request)
    {

        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationId = $organization?->id ?? null;
        $companyId = $organization?->company_id ?? null;

        if ($request->ajax()) {
            $dpr_master = ErpDprMaster::with('template')->get();

            return DataTables::of($dpr_master)
                ->addIndexColumn()
                ->addColumn('template_name', function ($row) {
                    return $row->template->template_name;
                })
                ->addColumn('field_name', function ($row) {
                    return $row->field_name;
                })
                ->addColumn('status', function ($row) {
                    return '<span class="badge rounded-pill ' . ($row->status == 'active' ? 'badge-light-success' : 'badge-light-danger') . ' badgeborder-radius">
                                ' . ucfirst($row->status) . '
                            </span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('dpr-master.edit', $row->id);
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

        return view('procurement.dpr_fields.index');
    }

    public function create()
    {
        $status = ConstantHelper::STATUS;
        $dpr_templates = ErpDprTemplateMaster::get();

        return view('procurement.dpr_fields.create', [
            'status' => $status,
            'dpr_templates' => $dpr_templates,
        ]);
    }

    public function store(DPRMasterRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validated = $request->validated();

        ErpDprMaster::create([
            'template_id' => $validated['template_id'],
            'field_name' => $validated['field_name'],
            'status' => $validated['status'] ?? ConstantHelper::ACTIVE,
            'organization_id' => $organization->id,
            'group_id' => $organization->group_id,
            'company_id' => $organization->company_id,
        ]);

        return redirect()->route('dpr-master.index');
    }

    public function edit($id)
    {
        $dpr_master = ErpDprMaster::find($id);
        $status = ConstantHelper::STATUS;
        $dpr_templates = ErpDprTemplateMaster::get();

        return view('procurement.dpr_fields.edit', [
            'status' => $status,
            'dpr_master' => $dpr_master,
            'dpr_templates' => $dpr_templates,
        ]);
    }

    public function update(DPRMasterRequest $request, $id)
    {
        $validated = $request->validated();
        $dpr_field = ErpDprMaster::findOrFail($id);
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;

        $dpr_field->update([
            'template_id' => $validated['template_id'],
            'field_name' => $validated['field_name'],
            'status' => $validated['status'] ?? ConstantHelper::STATUS,
            'organization_id' => $organization->id,
            'group_id' => $organization->group_id,
            'company_id' => $organization->company_id,
        ]);

        return redirect()->route('dpr-master.index');
    }
}
