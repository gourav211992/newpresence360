<?php
namespace App\Helpers;
use App\Models\ErpSubStore;
use App\Models\Station;
use Illuminate\Database\Eloquent\Collection;

class StationHelper
{
    const ENABLE = 'yes';
    const DISABLE = 'no';
    const IS_STOCKING_VALUES = [self::ENABLE, self::DISABLE];

    public static function getStockingStationsForSubStore(int $subStoreId, int $selectedStationId = null, int $onlySelectedStationId = null) : array
    {
        //Only retrieve one station (for non-editable fields)
        if (isset($onlySelectedStationId)) {
            $stations = Station::where('id', $selectedStationId) -> select('id', 'name', 'alias') -> get();
            return array(
                'status' => 'success',
                'message' => 'Stations found',
                'data' => $stations
            );
        }
        //Always include the selected station along with other applicable stations
        if (isset($selectedStationId)) {
            $stations = Station::select('id', 'name', 'alias') -> where('id', $selectedStationId) -> orWhere(function ($subQuery) {
                $subQuery -> withDefaultGroupCompanyOrg() -> where('status', ConstantHelper::ACTIVE);
            }) -> get();
            return array(
                'status' => 'success',
                'message' => 'Stations found',
                'data' => $stations
            );
        }
        //Check sub store if it exists
        $subStore = ErpSubStore::find($subStoreId);
        if (!isset($subStore)) {
            return array(
                'status' => 'error',
                'message' => 'Sub Store Not Found',
                'data' => array()
            );
        }
        // Check if sub store if of type Shop Floor (Production)
        if ($subStore -> type !== ConstantHelper::SHOP_FLOOR) {
            return array(
                'status' => 'success',
                'message' => 'Sub Store is not of type Shop Floor',
                'data' => array()
            );
        }
        // Check if sub store has station_wise_consumption enabled
        if ($subStore -> station_wise_consumption !== 'yes') {
            return array(
                'status' => 'success',
                'message' => 'Station wise consumption is not required',
                'data' => array()
            );
        }
        //Return all applicable stations
        $stations = Station::select('id', 'name', 'alias') -> withDefaultGroupCompanyOrg() -> where('status', ConstantHelper::ACTIVE) 
            -> get();
        return array(
            'status' => 'success',
            'message' => 'Stations found',
            'data' => $stations
        );
    }
}
