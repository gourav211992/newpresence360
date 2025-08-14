<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuthUser;
use App\Helpers\Helper; 
use App\Http\Requests\MultiFixedRequest;
use App\Models\ErpVehicleType;
use App\Helpers\ConstantHelper;
use App\Models\Customer;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\ErpLogisticsMultiFixedLocation;
use App\Models\ErpLogisticsMultiFixedPricing;
use App\Models\ErpLogisticsMultiPointPricing;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\ErpRouteMaster;
use App\Models\Organization;

class ErpMultiPointFixedController extends Controller
{

    public function create(){
        $user = Helper::getAuthenticatedUser();
        $status = ConstantHelper::STATUS;
        $organizationId = $user->organization_id;
        $organization = Organization::with('addresses')->find($organizationId);
        $countryId = optional($organization->addresses->first())->country_id;
        $states = State::where('country_id',$countryId)->get();
        $customers = Customer::withDefaultGroupCompanyOrg()->where('status', 'active')->get();
        $routeMasters = ErpRouteMaster::withDefaultGroupCompanyOrg()->where('status','active')->get();
        $vehicleTypes = ErpVehicleType::withDefaultGroupCompanyOrg()->where('status', 'active')->get();

        return view('logistics.multi-point-pricing.fixed.create', compact('states','customers', 'vehicleTypes','status', 'routeMasters'));
    }

      public function getCityByState(Request $request)
    {
        $stateId = $request->get('state_id');

        if (!$stateId) {
            return response()->json([
                'status' => false,
                'message' => 'State ID is required.',
                'data' => []
            ], 400);
        }

        $cities = City::where('state_id', $stateId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'status' => true,
            'data' => $cities
        ]);
    }

    public function edit($id)
{
    $user = Helper::getAuthenticatedUser();
    $organizationId = $user->organization_id;
    $organization = Organization::with('addresses')->find($organizationId);
    $countryId = optional($organization->addresses->first())->country_id;
    $status = ConstantHelper::STATUS;
    $states = State::where('country_id',$countryId)->get();
    $multiPricing = ErpLogisticsMultiFixedPricing::with('locations')->findOrFail($id);
    $customers = Customer::withDefaultGroupCompanyOrg()->where('status', 'active')->get();
    $routeMasters = ErpRouteMaster::withDefaultGroupCompanyOrg()->where('status','active')->get();
    $vehicleTypes = ErpVehicleType::withDefaultGroupCompanyOrg()->where('status', 'active')->get();

    return view('logistics.multi-point-pricing.fixed.edit', [
        'multiPricing' => $multiPricing,
        'vehicleTypes' => $vehicleTypes,
        'customers' => $customers,
        'states' => $states,
        'status' => $status,
        'routeMasters' => $routeMasters
    ]);
}


    public function store(MultiFixedRequest $request)
  {
    $user = Helper::getAuthenticatedUser();
    $organization = $user->organization;
    DB::beginTransaction();

    try {

        $multiPricing = new ErpLogisticsMultiFixedPricing();
        $multiPricing->organization_id       = $organization->id;
        $multiPricing->group_id              = $organization->group_id;
        $multiPricing->company_id            = $user->company_id ?? null;
        $multiPricing->source_route_id       = $request->source_route_id;
        $multiPricing->destination_route_id   = $request->destination_route_id;
        $multiPricing->vehicle_type_id      = json_encode($request->vehicle_type_id); 
        $multiPricing->customer_id           = $request->customer_id;
        $multiPricing->created_by            = $user->auth_user_id ;
        $multiPricing->status                = $request->status;
        $multiPricing->save();

     
        foreach ($request->multi_fixed_pricing as $location) {
            ErpLogisticsMultiFixedLocation::create([
                'multi_fixed_pricing_id' => $multiPricing->id,
                'location_route_id'   => $location['location_route_id'],
                'amount'     => $location['amount'] ?? $location["'amount'"], 
            ]);
        }

        DB::commit();

         return response()->json([
            'status' => true,
            'message' => 'Record created successfully',
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while creating the driver',
            'error' => $e->getMessage(),
        ], 500);
    }
 }

 public function update(MultiFixedRequest $request, $id)
{
    $user = Helper::getAuthenticatedUser();
    $organization = $user->organization;

    DB::beginTransaction();


    try {
        $multiPricing = ErpLogisticsMultiFixedPricing::findOrFail($id);

        $multiPricing->organization_id       = $organization->id;
        $multiPricing->group_id              = $organization->group_id;
        $multiPricing->company_id            = $user->company_id ?? null;
        $multiPricing->source_route_id       = $request->source_route_id;
        $multiPricing->destination_route_id   = $request->destination_route_id;
        $multiPricing->vehicle_type_id       = json_encode($request->vehicle_type_id);
        $multiPricing->customer_id           = $request->customer_id;
        $multiPricing->updated_by            = $user->auth_user_id ;
        $multiPricing->status                = $request->status;
        $multiPricing->save();

        $locationIds = [];

        foreach ($request->multi_fixed_pricing as $location) {
            $amount = $location['amount'] ?? null;
            $location_routeId = $location['location_route_id'] ?? null;
          
            if (!$location_routeId && !$amount) {
                continue;
            }

            $data = [
                'multi_fixed_pricing_id' => $multiPricing->id,
                'location_route_id'      => $location_routeId,
                'amount'                 => $amount,
            ];

            if (!empty($location['id'])) {
                $existing = ErpLogisticsMultiFixedLocation::find($location['id']);
                if ($existing) {
                    $existing->update($data);
                    $locationIds[] = $existing->id;
                }
            } else {
                $new = ErpLogisticsMultiFixedLocation::create($data);
                $locationIds[] = $new->id;
            }
        }

        ErpLogisticsMultiFixedLocation::where('multi_fixed_pricing_id', $multiPricing->id)
            ->whereNotIn('id', $locationIds)
            ->delete();

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Record updated successfully',
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
 public function destroy($id)
{
    DB::beginTransaction();

    try {
        $multiFixed = ErpLogisticsMultiFixedPricing::with([
            'locations',
        ])->findOrFail($id);

        $multiFixed->delete(); 

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Record deleted successfully.'
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => 'An error occurred while deleting the record: ' . $e->getMessage()
        ], 500);
    }
}


}
