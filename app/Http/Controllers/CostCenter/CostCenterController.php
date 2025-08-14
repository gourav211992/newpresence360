<?php

namespace App\Http\Controllers\CostCenter;

use App\Helpers\ConstantHelper;
use App\Helpers\CostCenterHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AuthUser;
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
        $centers = CostCenter::orderBy('id','desc')->get();
        $user = Helper::getAuthenticatedUser();
        $useRole = AuthUser::where('id', $user->auth_user_id)->first();
       if ($useRole && isset($useRole->user_type) && $useRole->user_type === 'IAM-SUPER') {
            $companies = Organization::where('group_id', $user->group_id)
            ->where('status', 'active')
                ->with('addresses')
                ->get();
        } else {
            $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
            if ($user->organization_id) {
                $orgIds[] = $user->organization_id;
            }
            $companies = Organization::whereIn('id', $orgIds)
                ->with('addresses')
                ->where('status', 'active')
                ->get();
        }
        $organizationId = Helper::getAuthenticatedUser()->organization_id;

        return view('costCenter.view', compact('centers','companies','organizationId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Helper::getAuthenticatedUser();
        
        $useRole = AuthUser::where('id', $user->auth_user_id)->first();
       if ($useRole && isset($useRole->user_type) && $useRole->user_type === 'IAM-SUPER') {
            $companies = Organization::where('group_id', $user->group_id)
            ->where('status', 'active')
                ->with('addresses')
                ->get();
        } else {
            $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
            if ($user->organization_id) {
                $orgIds[] = $user->organization_id;
            }
            $companies = Organization::whereIn('id', $orgIds)
                ->with('addresses')
                ->where('status', 'active')
                ->get();
        }
        $existingCostCenters = CostCenter::pluck('name')->toArray();
        $groups = CostGroup::where('status','active')->get();
        return view('costCenter.create', compact('groups','companies','companies','existingCostCenters'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $authOrganization = Helper::getAuthenticatedUser()->organization;
        $organizationId = $authOrganization->id;
        $companyId = $authOrganization ?-> company_id;
        $groupId = $authOrganization ?-> group_id;
        // Validate the request data
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Helper::uniqueRuleWithConditions('erp_cost_centers', [
                //     'organization_id' => $organizationId,
                //     'company_id' => $companyId,
                //     'group_id' => $groupId
                // ], null, 'id', false),
            ],
            // 'name' => 'required|string|max:255|unique:erp_cost_centers,name',
        ]);
        $existingName = CostCenter::where('name', $request->name)
        ->first();

            if ($existingName) {
                return back()->withErrors(['name' => 'The name has already been taken.'])->withInput();
            }

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
                                'group_id' => $map['group_id'] ?? null,
                                'company_id' => $map['company_id'] ?? null,
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
        $useRole = AuthUser::where('id', $user->auth_user_id)->first();
       if ($useRole && isset($useRole->user_type) && $useRole->user_type === 'IAM-SUPER') {
            $companies = Organization::where('group_id', $user->group_id)
            ->where('status', 'active')
                ->with('addresses')
                ->get();
        } else {
            $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
            if ($user->organization_id) {
                $orgIds[] = $user->organization_id;
            }
            $companies = Organization::whereIn('id', $orgIds)
                ->with('addresses')
                ->where('status', 'active')
                ->get();
        }
         $existingCostCenters = CostCenter::where('id', '!=', $id)
        ->pluck('name')
        ->toArray();

        $groups = CostGroup::where('organization_id',Helper::getAuthenticatedUser()->organization_id)->where('status','active')->orWhere('id',$data->cost_group_id)->get();
        return view('costCenter.edit', compact('groups', 'data', 'companies','existingCostCenters'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $authOrganization = Helper::getAuthenticatedUser()->organization;
        $organizationId = $authOrganization->id;
        $companyId = $authOrganization ?-> company_id;
        $groupId = $authOrganization ?-> group_id;
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Helper::uniqueRuleWithConditions('erp_cost_centers', [
                //     'organization_id' => $organizationId,
                //     'company_id' => $companyId,
                //     'group_id' => $groupId
                // ], $id, 'id', false),
            ],
            // 'name' => ['required', 'string', 'max:255', Rule::unique('erp_cost_groups')->ignore($id)],
        ]);
        $existingName = CostCenter::where('name', $request->name)
        ->where('id', '!=', $id)
        ->first();

            if ($existingName) {
                return back()->withErrors(['name' => 'The name has already been taken.'])->withInput();
            }


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
    public function getCostCenter($id){
        $cost_centers =  CostCenterOrgLocations::where('location_id',$id)->with(['costCenter' => function ($query) {
            $query->where('status', 'active');
        }])
        ->get()
        ->filter(function ($item) {
            return $item->costCenter !== null;
        })
        ->map(function ($item) {
            return [
                'id' => $item->costCenter->id,
                'name' => $item->costCenter->name,
            ];
        })
        ->toArray();
        return $cost_centers;

    }

    public function getCostCenterLocationBasis(Request $r){
        $locationId = $r->locationId;
        $location = CostCenterHelper::getAccessibleCostCenters($locationId);
        return response()->json($location);
    }

}
