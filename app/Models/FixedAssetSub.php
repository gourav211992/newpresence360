<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FixedAssetSub extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_finance_fixed_asset_sub';
    protected $guarded = ['id'];
    protected $appends = ['insurance'];
    public function asset()
    {
        return $this->belongsTo(FixedAssetRegistration::class, 'parent_id');
    }
    public function location()
    {
        return $this->belongsTo(ErpStore::class, 'location_id');
    }
    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }
    public static function generateSubAssets($parentId, $assetCode, $quantity, $totalValue, $salvageValue)
    {
        $asset = FixedAssetRegistration::findOrFail($parentId); // Ensure parent asset exists
        if (!empty($asset->capitalize_date) && strtotime($asset->capitalize_date) && is_numeric($asset->useful_life) && $asset->useful_life > 0) {
            $expiry = Carbon::parse($asset->capitalize_date)->addYears($asset->useful_life)->subDay()->toDateString();
            Log::info("Expiry calculated successfully", [
                'capitalize_date' => $asset->capitalize_date,
                'useful_life' => $asset->useful_life,
                'expiry' => $expiry,
            ]);
        } else {
            Log::error("Failed to calculate expiry", [
                'capitalize_date' => $asset->capitalize_date,
                'useful_life' => $asset->useful_life
            ]);
            $expiry = null;
        }
        $cost_centerId = $asset?->cost_center_id; // Assuming cost_center_id is available in the parent asset
        $locationId = $asset?->location_id; // Assuming location_id is available in the parent asset

        $subAssets = [];
        $unitValue = $totalValue / $quantity;
        $salvageValueUnit = $salvageValue / $quantity;

        for ($i = 1; $i <= $quantity; $i++) {
            $subAssets[] = self::create([
                'parent_id' => $parentId,
                'sub_asset_code' => $assetCode . '-' . sprintf('%02d', $i),
                'current_value' => $unitValue,
                'current_value_after_dep' => $unitValue,
                'salvage_value' => $salvageValueUnit,
                'location_id' => $locationId, // Assuming location_id is nullable
                'cost_center_id' => $cost_centerId, // Assuming cost_center_id is nullable
                'capitalize_date' => $asset->capitalize_date ?? null, // Copying capitalize_date from parent asset
                'last_dep_date' => $asset->capitalize_date ?? null, // Copying last_dep_date from parent asset
                'expiry_date' => $expiry,
            ]);
        }
        Log::info("Sub Assets", [
            'Sub Assets' => $subAssets,
        ]);

        return $subAssets;
    }
    public static function regenerateSubAssets($parentId, $assetCode, $quantity, $totalValue, $salvageValue)
    {
        // Delete all existing sub-assets with the same parent_id
        self::withTrashed()->where('parent_id', $parentId)->forceDelete();
        return self::generateSubAssets($parentId, $assetCode, $quantity, $totalValue, $salvageValue);
    }


    public static function oldSubAssets($merger = null, $split = null)
    {
        // Get sub_asset_ids from FixedAssetSplit excluding given $split ids
        $splitQuery = FixedAssetSplit::query();
        if (!is_null($split)) {
            $splitQuery->whereNotIn('id', (array)$split);
        }
        $splitSubAssetIds = $splitQuery->pluck('sub_asset_id')->filter();

        // Get sub_asset_ids from FixedAssetMerger excluding given $merger ids
        $mergerQuery = FixedAssetMerger::query();
        if (!is_null($merger)) {
            $mergerQuery->whereNotIn('id', (array)$merger);
        }

        $mergerSubAssetIds = $mergerQuery->pluck('asset_details')
            ->flatMap(function ($json) {
                $decoded = json_decode($json, true);
                return is_array($decoded)
                    ? collect($decoded)->flatMap(function ($item) {
                        return isset($item['sub_asset_id']) && is_array($item['sub_asset_id'])
                            ? collect($item['sub_asset_id'])->map(fn($id) => (int) $id)
                            : [];
                    })
                    : [];
            });

        return $splitSubAssetIds->merge($mergerSubAssetIds)->unique()->values()->all();
    }
    public function getInsurancesAttribute()
    {
        return FixedAssetInsurance::whereJsonContains('sub_asset', (string) $this->id)->get();
    }
    public function getMaintenanceAttribute()
    {
        return FixedAssetMaintenance::whereJsonContains('sub_asset', (string) $this->id)->get();
    }
}
