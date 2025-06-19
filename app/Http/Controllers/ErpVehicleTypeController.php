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
use App\Models\Organization;

class ErpVehicleTypeController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::find($user->organization_id);
        $organizationId = $organization?->id;
        $companyId = $organization?->company_id;

       $vehicleTypes = ErpVehicleType::where('organization_id', $organizationId)->get();
       return view('vehicle-types.index', compact('vehicleTypes'));
    }


     public function create(){

        return view('vehicle-types.create');
    }
   public function edit($id)
    {
        $user = Helper::getAuthenticatedUser();
        $vehicleType = ErpVehicleType::where('id', $id)
            ->where('organization_id', $user->organization_id)
            ->firstOrFail();

        return view('vehicle-types.edit', compact('vehicleType'));
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
                        'description'     => $type['description'] ?? null,
                        'status'          => $type['status'] ?? 'Active',
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
