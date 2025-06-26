<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use App\Http\Requests\RouteMasterRequest;
use App\Helpers\Helper; 
use App\Models\ErpRouteMaster;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Organization;

class ErpRouteMasterController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        $organization = Organization::with('addresses')->find($organizationId);
        $selectedCountryId = optional($organization->addresses->first())->country_id;
        $countries = Country::all();
         $states = $selectedCountryId
        ? State::where('country_id', $selectedCountryId)->get()
        : collect();
       
       return view('route-masters.index', compact(
        'countries', 'states', 'selectedCountryId'));
    }

    public function store(RouteMasterRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;

        $selectedIndexes = $request->input('selected_rows', []);
        $insertAll = empty($selectedIndexes);

        foreach ($request->route_master as $index => $type) {
            if ($insertAll || in_array($index, $selectedIndexes)) {
                if (!empty($type['name'])) {
                    
                    $data = [
                        'organization_id' => $organization->id,
                        'group_id'        => $organization->group_id,
                        'company_id'      => $user->company_id ?? null,
                        'name'            => $type['name'],
                        'country_id'      => $type['country_id'],
                        'state_id'        => $type['state_id'],
                        'city_id'         => $type['city_id'] ,
                        'status'          => $type['status'],

                    ];

                    if (!empty($type['id'])) {
                        ErpRouteMaster::where('id', $type['id'])->update($data);
                    } else {
                        ErpRouteMaster::create($data);
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
