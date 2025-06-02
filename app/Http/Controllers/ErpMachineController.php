<?php

namespace App\Http\Controllers;
use App\Models\ProductionRoute;
use Yajra\DataTables\Facades\DataTables;
use App\Models\AttributeGroup;
use App\Models\Attribute;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use DB;
use App\Models\ErpMachine;
use App\Models\ErpMachineDetail;

class ErpMachineController extends Controller
{    
    public function index(Request $request)
    {
        $pathUrl = request()->segments()[0];
        $redirectUrl = route('machine.index');
        $createRoute = route('machine.create');
        $typeName = "Machine";
    
        if ($request->ajax()) {
            try {
                $docs = ErpMachine::withDefaultGroupCompanyOrg()
                    ->bookViewAccess($pathUrl)
                    ->orderByDesc('id');
                return DataTables::of($docs)
                    ->addIndexColumn()
                    ->editColumn('status', function ($row) {
                        $documentStatus = $row->status == 'active' ? ConstantHelper::APPROVED : ConstantHelper::REJECTED;
                        $statusClasss = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$documentStatus ?? ConstantHelper::APPROVED];
                        $displayStatus = $row->status == 'active' ? 'Active' : 'Inactive';
                        $editRoute = route('machine.edit', ['id' => $row->id]);
                        return "
                            <div style='text-align:right;'>
                                <span class='badge rounded-pill $statusClasss badgeborder-radius'>$displayStatus</span>
                                <div class='dropdown' style='display:inline;'>
                                    <button type='button' class='btn btn-sm dropdown-toggle hide-arrow py-0 p-0' data-bs-toggle='dropdown'>
                                        <i data-feather='more-vertical'></i>
                                    </button>
                                    <div class='dropdown-menu dropdown-menu-end'>
                                        <a class='dropdown-item' href='" . $editRoute . "'>
                                            <i data-feather='edit-3' class='me-50'></i>
                                            <span>View/ Edit Detail</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        ";
                    })
                    ->addColumn('Machine', function ($row) {
                        return $row->name ? $row->name : 'N/A';
                    })
                    ->editColumn('Attribute', function ($row) {
                        return $row->attribute_group->name ?? 'N/A';
                    })
                    ->addColumn('Values', function ($row) {
                        return $row?->val_name() ?? " ";
                    })
                    ->rawColumns(['status'])
                    ->make(true);
            } catch (Exception $ex) {
                return response()->json([
                    'message' => $ex->getMessage()
                ]);
            }
        }
        $parentURL = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        return view('machine.index', [
            'typeName' => $typeName,
            'redirect_url' => $redirectUrl,
            'create_route' => $createRoute,
            'create_button' => count($servicesBooks['services']),
        ]);
    }

    public function create()
    {
        $attribute = AttributeGroup::withDefaultGroupCompanyOrg()
                    ->where('status', 'active')
                    ->whereRaw('LOWER(name) = ?', ['size'])
                    ->take(1)
                    ->get();
        $productionRoutes = ProductionRoute::withDefaultGroupCompanyOrg()
        ->where('status', ConstantHelper::ACTIVE)
        ->get();
        return view('machine.create', [
            'attributes' => $attribute,
            'productionRoutes' => $productionRoutes
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'machine_name' => 'required|string|max:255',
            'attribute_group_id' => 'required',
            'machine_details.*.attribute_id' => 'required',
            'machine_details.*.length' => 'required|numeric|min:0',
            'machine_details.*.width' => 'required|numeric|min:0',
            'machine_details.*.no_of_pairs' => 'required|integer|min:0',
        ]);

        $existingMachine = ErpMachine::where('name', $request->input('machine_name'))
            ->where('attribute_group_id', $request->input('attribute_group_id'))
            ->first();

        if ($existingMachine) {
            return redirect()->back()->withErrors([
                'error' => 'A machine with the same name and attribute already exists. Please select a different attribute.',
            ]);
        }

        try {
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::find($user?->organization_id);
            $organizationId = $organization?->id ?? null;
            $groupId = $organization?->group_id ?? null;
            $companyId = $organization?->company_id ?? null;
            $production_route_id = $request->production_route_id;

            $group = AttributeGroup::where('id', $request->input('attribute_group_id'))->first();
            if (!$group) {
                return redirect()->back()->withErrors(['error' => 'Attribute group not found.']);
            }
            DB::beginTransaction();
            // Create the machine
            $machine = new ErpMachine;
            $machine->organization_id = $organizationId;
            $machine->group_id = $groupId;
            $machine->company_id = $companyId;
            $machine->name = $request->input('machine_name');
            $machine->attribute_group_id = $request->input('attribute_group_id');
            $machine->attribute_group_name = $group->name;
            $machine->status = $request->input('status') ?? ConstantHelper::ACTIVE;
            $machine->production_route_id = $production_route_id;
            // Save the machine
            $machine->save();
            // Handle machine details
            foreach ($request->input('machine_details', []) as $detail) {
                $attributeVal = Attribute::find($detail['attribute_id']);
                $machineDetail = new ErpMachineDetail;
                $machineDetail->machine_id = $machine->id;
                $machineDetail->attribute_group_id = $request->input('attribute_group_id');
                $machineDetail->attribute_id = $attributeVal->id;
                $machineDetail->attribute_value = $attributeVal->value;
                $machineDetail->length = $detail['length'];
                $machineDetail->width = $detail['width'];
                $machineDetail->no_of_pairs = $detail['no_of_pairs'];
                $machineDetail->save();
            }
            DB::commit();
            return response()->json([
                'message' => 'Record created successfully',
                'data' => $machine,
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
        $machine = ErpMachine::find($id);
        $attribute = AttributeGroup::withDefaultGroupCompanyOrg()
                    ->where('status', 'active')
                    ->whereRaw('LOWER(name) = ?', ['size'])
                    ->take(1)
                    ->get();
        $productionRoutes = ProductionRoute::withDefaultGroupCompanyOrg()
        ->where('status', ConstantHelper::ACTIVE)
        ->get();
        $values = $machine->attribute_group->attributes()->select('id', 'value', 'attribute_group_id')->get();
        return view('machine.edit', [
            'machine' => $machine,
            'attributes' => $attribute,
            'productionRoutes' => $productionRoutes,
            'selectedValues' => $values
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'machine_name' => 'required|string|max:255',
            'attribute_group_id' => 'required',
            'machine_details.*.attribute_id' => 'required',
            'machine_details.*.length' => 'required|numeric|min:0',
            'machine_details.*.width' => 'required|numeric|min:0',
            'machine_details.*.no_of_pairs' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $machine = ErpMachine::findOrFail($id);
            $machine->update([
                'name' => $request->input('machine_name'),
                'attribute_group_id' => $request->input('attribute_group_id'),
                'production_route_id' => $request->input('production_route_id')
            ]);

            $existingDetailIds =$machine->details()->pluck('id')->filter()->toArray();
            $inputDetailIds = collect($request->input('machine_details', []))
                ->pluck('id')
                ->filter()
                ->toArray();
            // Only delete if there's a difference
            $idsToDelete = array_diff($existingDetailIds, $inputDetailIds);
            if (!empty($idsToDelete)) {
                ErpMachineDetail::whereIn('id', $idsToDelete)->delete();
            }

            foreach ($request->input('machine_details', []) as $detail) {
                $attributeVal = Attribute::find($detail['attribute_id']);
                $machineDetail = ErpMachineDetail::find($detail['id'] ?? null) ?? new ErpMachineDetail;
                $machineDetail->machine_id = $machine->id;
                $machineDetail->attribute_group_id = $request->input('attribute_group_id');
                $machineDetail->attribute_id = $attributeVal->id;
                $machineDetail->attribute_value = $attributeVal->value;
                $machineDetail->length = $detail['length'];
                $machineDetail->width = $detail['width'];
                $machineDetail->no_of_pairs = $detail['no_of_pairs'];
                $machineDetail->save();
            }

            DB::commit();
            return response()->json([
                'message' => 'Record updated successfully',
                'data' => $machine,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while updating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function attributeValues(Request $request)
    {
        try {
            $attributeGroupId = $request->input('attribute_group_id');
            $attributeGroup = AttributeGroup::find($attributeGroupId);
            if (!$attributeGroup) {
                return response()->json(['status' => 404, 'message' => 'Attribute group not found.'], 404);
            }
            $values = $attributeGroup->attributes()->select('id', 'value', 'attribute_group_id')->get();
            return response()->json(['data' => ['values' => $values], 'status' => 200, 'message' => 'Attribute values fetched successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 400, 'message' => $e->getMessage()]);
        }
    }
}
