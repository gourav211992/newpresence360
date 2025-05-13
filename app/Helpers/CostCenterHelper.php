<?php

namespace App\Helpers;
use App\Models\CostCenter;
use Illuminate\Database\Eloquent\Collection;

class CostCenterHelper
{
    public static function getAccessibleCostCenters(int $locationId, int $costCenterId = null) : Collection
    {
        $costCenters = CostCenter::select('id', 'name') -> when($costCenterId, function ($costQuery) use($costCenterId) {
            $costQuery -> where('id', $costCenterId);
        }) -> orWhere(function ($orQuery) use($locationId) {
            $orQuery ->  whereHas('orgLocationMap', function ($locQuery) use($locationId) {
                $locQuery -> where('location_id', $locationId);
            }) -> where('status', ConstantHelper::ACTIVE);
        }) -> get();
        return $costCenters;
    }
}