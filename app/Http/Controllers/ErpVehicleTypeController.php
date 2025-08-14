<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use App\Http\Requests\VehicleTypeRequest;
use App\Helpers\Helper; 
use App\Models\ErpVehicleType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\Unit;
use App\Models\ErpFreightCharge;
use App\Models\ErpLogisticsMultiFixedPricing;
use App\Models\ErpVehicle;
use App\Models\Organization;

class ErpVehicleTypeController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::find($user->organization_id);
        $organizationId = $organization?->id;
        $companyId = $organization?->company_id;
        $uoms = Unit::where('status', 'active')->get();

       $vehicleTypes = ErpVehicleType::where('organization_id', $organizationId)->get();
       return view('logistics.vehicle-types.index', compact('vehicleTypes', 'uoms'));
    }


   public function store(VehicleTypeRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;

        $selectedIndexes = $request->input('selected_rows', []);
        $insertAll = empty($selectedIndexes);

        foreach ($request->vehicle_type as $index => $type) {
            if ($insertAll || in_array($index, $selectedIndexes)) {
                if (!empty($type['name'])) {
                    
                    $data = [
                        'organization_id' => $organization->id,
                        'group_id'        => $organization->group_id,
                        'company_id'      => $user->company_id ?? null,
                        'name'            => $type['name'],
                        'capacity'        => $type['capacity'],
                        'uom_id'          => $type['uom_id'],
                        'description'     => $type['description'] ?? null,
                        'status'          => $type['status'],

                    ];

                    if (!empty($type['id'])) {
                        ErpVehicleType::where('id', $type['id'])->update($data);
                    } else {
                        ErpVehicleType::create($data);
                    }
                }
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Records saved successfully.',
        ], 201);
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

    // Check dependencies in related tables
    $usedInFreight = ErpFreightCharge::whereIn('vehicle_type_id', $ids)->exists();
    $usedInMultiFixed = ErpLogisticsMultiFixedPricing::whereIn('vehicle_type_id', $ids)->exists();
    $usedInVehicle = ErpVehicle::whereIn('vehicle_type_id', $ids)->exists();

    if ($usedInFreight || $usedInMultiFixed || $usedInVehicle) {
        return response()->json([
            'status' => false,
            'message' => 'Selected vehicle types are in use in Freight Charges, Multi-Fixed Pricing, or Vehicles and cannot be deleted.'
        ], 400);
    }

    try {
        ErpVehicleType::whereIn('id', $ids)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Selected records deleted successfully.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Error deleting records: ' . $e->getMessage()
        ], 500);
    }
}



}
