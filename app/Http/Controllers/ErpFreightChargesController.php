<?php

namespace App\Http\Controllers;

use App\Models\ErpVehicle;
use Illuminate\Http\Request;
use App\Models\AuthUser;
use App\Helpers\Helper; 
use App\Http\Requests\FreightChargeRequest;
use App\Models\ErpVehicleType;
use App\Helpers\ConstantHelper;
use App\Models\Customer;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\ErpFreightCharge;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\ErpRouteMaster;
use App\Models\Organization;

class ErpFreightChargesController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        $organization = Organization::with('addresses')->find($organizationId);
        $countryId = optional($organization->addresses->first())->country_id;
        $states = State::where('country_id',$countryId)->get();
        $status = ConstantHelper::STATUS;
        $customers = Customer::withDefaultGroupCompanyOrg()->where('status','active')->get();
        $vehicleTypes = ErpVehicleType::withDefaultGroupCompanyOrg()->where('status','active')->get();
        $routeMasters = ErpRouteMaster::withDefaultGroupCompanyOrg()->where('status','active')->get();
        $freightCharges = ErpFreightCharge::withDefaultGroupCompanyOrg()->get();
        
        return view('logistics.freight-charges.index', compact('customers', 'vehicleTypes', 'states', 'freightCharges','routeMasters'));
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

    public function store(FreightChargeRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;

        $selectedIndexes = $request->input('row_checkbox', []);
        $insertAll = empty($selectedIndexes);
        $savedCount = 0;
        

        foreach ($request->freight_charges as $index => $charge) {
            if ($insertAll || in_array($index, $selectedIndexes)) {
            if (empty($charge['source_route_id']) || empty($charge['destination_route_id'])) {
                continue;
            }

                $data = [
                    'organization_id'       => $organization->id,
                    'group_id'              => $organization->group_id,
                    'company_id'            => $user->company_id ?? null,
                    'source_route_id'       => $charge['source_route_id'],
                    'destination_route_id'  => $charge['destination_route_id'],
                    'distance'              => $charge['distance'],
                    'vehicle_type_id'       => $charge['vehicle_type_id'],
                    'no_bundle'             => $charge['no_bundle'],
                    'amount'                => $charge['amount'],
                    'per_bundle'            => $charge['per_bundle'],
                    'customer_id'           => $charge['customer_id'] ?? null,
                ];

                try {
                    if (!empty($charge['id'])) {
                        ErpFreightCharge::where('id', $charge['id'])->update($data);
                    } else {
                        ErpFreightCharge::create($data);
                    }

                    $savedCount++;
                } catch (\Exception $e) {
                    \Log::error("Failed to save freight charge row {$index}: " . $e->getMessage());
                }
            }
        }

        if ($savedCount > 0) {
            return response()->json([
                'status' => true,
                'message' => "Records saved successfully.",
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No rows were saved. Please check your selections and input.',
            ], 422);
        }
    }

    public function deleteMultiple(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'status' => false,
                'message' => 'No records selected for deletion.'
            ], 400);
        }

        try {
            ErpFreightCharge::whereIn('id', $ids)->delete();

            return response()->json([
                'status' => true,
                'message' => 'Records deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting records: ' . $e->getMessage()
            ], 500);
        }
    }


     public function getFreightChargeDetails(Request $request)
    {
        $sourceId = $request->source_id;
        $destinationId = $request->destination_id;
        $vehicleId = $request->vehicle_id;
        $customerId = $request->customer_id;
        $freightCharge = null;

        $vehicle = ErpVehicle::find($vehicleId);
        $vehicleTypeId = $vehicle->vehicle_type_id ?? null;

     $query = ErpFreightCharge::withDefaultGroupCompanyOrg();

        $freightCharge = (clone $query)
            ->where('source_route_id', $sourceId)
            ->where('destination_route_id', $destinationId)
            ->where('vehicle_type_id', $vehicleTypeId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$freightCharge) {
            $freightCharge = (clone $query)
                ->where('source_route_id', $sourceId)
                ->where('destination_route_id', $destinationId)
                ->where('vehicle_type_id', $vehicleTypeId)
               ->where(function ($q) {
                    $q->whereNull('customer_id')
                    ->orWhere('customer_id', '');
                })->first();
        }

        if (!$freightCharge) {
            return response()->json(['message' => 'No freight charge found.'], 404);
        }

        return response()->json([
            'message' => 'Get freight charge data',
            'vehicle_type_id' => $freightCharge->vehicle_type_id,
            'vehicle_type_name' => optional($freightCharge->vehicleType)->name,
            'distance' => $freightCharge->distance,
            'freight_charges' => $freightCharge->amount,
            'no_bundle' => $freightCharge->no_bundle,
            'per_bundle' => $freightCharge->per_bundle,
            'vehicle_type_capacity'   => $freightCharge->vehicleType->capacity ?? '',
            'vehicle_type_unit_name'  => optional($freightCharge->vehicleType->unit)->name ?? '',
            'source_name'             => optional($freightCharge->sourceRoute)->name ?? '',
            'destination_name'        => optional($freightCharge->destinationRoute)->name ?? '',
        ]);
    }

}
