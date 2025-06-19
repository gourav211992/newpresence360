<?php

namespace App\Http\Controllers;

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
        $customers = Customer::withDefaultGroupCompanyOrg()->get();
        $vehicleTypes = ErpVehicleType::withDefaultGroupCompanyOrg()->get();
        $freightCharges = ErpFreightCharge::withDefaultGroupCompanyOrg()->get();
        

        return view('freight-charges.index', compact('customers', 'vehicleTypes', 'states', 'freightCharges'));
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
            if (empty($charge['source_state_id']) || empty($charge['destination_state_id'])) {
                continue;
            }

                $data = [
                    'organization_id'       => $organization->id,
                    'group_id'              => $organization->group_id,
                    'company_id'            => $user->company_id ?? null,
                    'source_state_id'       => $charge['source_state_id'],
                    'source_city_id'        => $charge['source_city_id'],
                    'destination_state_id'  => $charge['destination_state_id'],
                    'destination_city_id'   => $charge['destination_city_id'],
                    'distance'              => $charge['distance'],
                    'vehicle_type_id'       => $charge['vehicle_type_id'],
                    'amount'                => $charge['amount'],
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

}
