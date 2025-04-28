<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\PincodeMaster;

use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function countries(Request $request)
    {
        try{
            $term = $request->get('term'); 
            $countries = Country::select('id AS value', 'name AS label') 
            ->when($term, function($query, $term) {
                return $query->where('name', 'LIKE', "%$term%");  
            })
            ->get();
            return response() -> json([
                'data' => array(
                    'countries' => $countries
                )
            ]);
        } catch(\Exception $ex) {
            return response() -> json([
                'message' => $ex -> getMessage()
            ]);
        }
    }

    public function states(Request $request, String $countryId)
    {
        try{
            $term = $request->get('term');
            $states = State::where('country_id', $countryId) -> select('id AS value', 'name AS label')  
            ->when($term, function ($query, $term) {
                return $query->where('name', 'LIKE', "%$term%");
            })
            ->get();
            return response() -> json([
                'data' => array(
                    'states' => $states
                )
            ]);
        } catch(\Exception $ex) {
            return response() -> json([
                'message' => $ex -> getMessage()
            ]);
        }
    }
    public function cities(Request $request, String $stateId)
    {
        try{
            $term = $request->get('term');
            $cities = City::where('state_id', $stateId) -> select('id AS value', 'name AS label') 
            ->when($term, function ($query, $term) {
                return $query->where('name', 'LIKE', "%$term%");
            })
            ->get();
            return response() -> json([
                'data' => array(
                    'cities' => $cities
                )
            ]);
        } catch(\Exception $ex) {
            return response() -> json([
                'message' => $ex -> getMessage()
            ]);
        }
    }

    public function pincodes(Request $request, $stateId)
    {
        try {
            $term = $request->get('term', '');
    
            $pincodes = PincodeMaster::where('state_id', $stateId)
                ->where('status', 'active') 
                ->select('id AS value', 'pincode AS label') 
                ->when($term, function ($query, $term) { 
                    return $query->where('pincode', 'LIKE', "%$term%");
                })
                ->get();  
    
            return response()->json([
                'data' => [
                    'pincodes' => $pincodes 
                ]
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage()
            ]);
        }
    }

    public function getStateIdByCode($stateCode)
    {
        try {
            $formattedStateCode = str_pad($stateCode, 2, '0', STR_PAD_LEFT);
            
            $state = State::where('state_code', $formattedStateCode)
                ->where('status', 'active') 
                ->first();
    
            if ($state) {
                return response()->json([
                    'state_id' => $state->id,
                    'state_name' => $state->name, 
                ]);
            }
            return response()->json(['message' => 'State not found for code: ' . $stateCode], 404);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }
    
    public function getCountryIdByState($stateId)
    {
        try {
            $state = State::where('id', $stateId)->first();
    
            if ($state) {
                $country = Country::find($state->country_id);
    
                if ($country) {
                    return response()->json([
                        'country_id' => $country->id,
                        'country_name' => $country->name, 
                    ]);
                }
    
                return response()->json(['message' => 'Country not found for state ID: ' . $stateId], 404);
            }
    
            return response()->json(['message' => 'State not found for ID: ' . $stateId], 404);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }

    public function getCityIdByName($stateId, $cityName)
    {
        try {
            $city = City::where('state_id', $stateId)
                ->where('name', $cityName)
                ->first();
    
            if ($city) {
                return response()->json([
                    'city_id' => $city->id,
                    'city_name' => $city->name, 
                ]);
            }
    
            return response()->json(['message' => 'City not found for name: ' . $cityName], 404);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }

    public function getPincodeIdByCode($stateId, $pincode)
    {
        try {
            $pincodeRecord = PincodeMaster::where('state_id', $stateId)
                ->where('pincode', $pincode)
                ->first();
    
            if ($pincodeRecord) {
                return response()->json([
                    'pincode_id' => $pincodeRecord->id,
                    'pincode' => $pincodeRecord->pincode, 
                ]);
            }
    
            return response()->json(['message' => 'Pincode not found for code: ' . $pincode], 404);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }
    
}
