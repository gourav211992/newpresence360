<?php

namespace App\Http\Controllers\CostCenter;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\CostCenterOrgLocations;
use App\Models\CostCenter;
use App\Models\CostGroup;
use App\Models\ErpStore;
use App\Models\Ledger;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;
use App\Models\Organization;
use Auth;

class CostCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $centers = CostCenter::where('organization_id',Helper::getAuthenticatedUser()->organization_id)->orderBy('id', 'desc')->get();
        $centers = CostCenter::withDefaultGroupCompanyOrg()->get();
        $companies = Helper::getAuthenticatedUser()->access_rights_org;
        $organizationId = Helper::getAuthenticatedUser()->organization_id;
        
        return view('costCenter.view', compact('centers','companies','organizationId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Helper::getAuthenticatedUser();
        $companies = $user -> access_rights_org;
        $groups = CostGroup::where('organization_id',Helper::getAuthenticatedUser()->organization_id)->where('status','active')->get();
        return view('costCenter.create', compact('groups','companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255|unique:erp_cost_centers,name',
        ]);

        // Find the organization based on the user's organization_id
        // $organization = Organization::where('id', Helper::getAuthenticatedUser()->organization_id)->first();
        // Create a new cost center record with organization details
        $parentUrl = ConstantHelper::COST_CENTER_SERVICE_ALIAS;
        $validatedData = Helper::prepareValidatedDataWithPolicy($parentUrl);
        $costCenter = CostCenter::create(array_merge($request->all(),$validatedData));

        $locationOrgMappings = json_decode($request->input('location_org_mappings'), true);
            if (is_array($locationOrgMappings)) {
                foreach ($locationOrgMappings as $map) {
                    if (!empty($map['organization_id']) && !empty($map['location_id'])) {
                        CostCenterOrgLocations::updateOrCreate(
                            [
                                'organization_id' => $map['organization_id'],
                                'location_id' => $map['location_id'],
                                'cost_center_id' => $costCenter->id,
                            ]
                        );
                    }
                }
            }

        // Redirect with a success message
        return redirect()->route('cost-center.index')->with('success', 'Cost Center created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = CostCenter::find($id);
        $user = Helper::getAuthenticatedUser();
        $companies = $user -> access_rights_org;
        
        $groups = CostGroup::where('organization_id',Helper::getAuthenticatedUser()->organization_id)->where('status','active')->orWhere('id',$data->cost_group_id)->get();
        return view('costCenter.edit', compact('groups', 'data', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('erp_cost_groups')->ignore($id)],
        ]);

        $update = CostCenter::find($id);
        $update->update($request->all());
        CostCenterOrgLocations::where('cost_center_id', $id)->delete();
        $locationOrgMappings = json_decode($request->input('location_org_mappings'), true);
            if (is_array($locationOrgMappings)) {
                foreach ($locationOrgMappings as $map) {
                    if (!empty($map['organization_id']) && !empty($map['location_id'])) {
                        CostCenterOrgLocations::updateOrCreate(
                            [
                                'organization_id' => $map['organization_id'],
                                'location_id' => $map['location_id'],
                                'cost_center_id' => $id,
                            ]
                        );
                    }
                }
            }
        return redirect()->route('cost-center.index')->with('success', 'Cost Center updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $record = CostCenter::findOrFail($id);
        $record->delete();
        CostCenterOrgLocations::where('cost_center_id', $id)->delete();
        return redirect()->route('cost-center.index')->with('success', 'Cost Center deleted successfully');
    }
    public function getLocation(Request $r){
        $organizations = $r->organizations;
        $location = Helper::getStoreLocation($organizations);
        return response()->json($location);
    }
}
