<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAssetSub extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'erp_finance_fixed_asset_sub';
     protected $guarded = ['id'];
     public function asset()
    {
        return $this->belongsTo(FixedAssetRegistration::class, 'parent_id');
    }
    public static function generateSubAssets($parentId, $assetCode, $quantity, $totalValue,$salvageValue)
    {
        $subAssets = [];
        $unitValue = $totalValue / $quantity;
        $salvageValueUnit = $salvageValue / $quantity;
        
        for ($i = 1; $i <= $quantity; $i++) {
            $subAssets[] = self::create([
                'parent_id' => $parentId,
                'sub_asset_code' => $assetCode .'-'. sprintf('%02d', $i),
                'current_value' => $unitValue,
                'current_value_after_dep'=> $unitValue,
                'salvage_value' => $salvageValueUnit,
            ]);
        }
        
        return $subAssets;
    }
    public static function regenerateSubAssets($parentId, $assetCode, $quantity, $totalValue,$salvageValue)
{
    // Delete all existing sub-assets with the same parent_id
    self::where('parent_id', $parentId)->delete();

    $subAssets = [];
    $unitValue = $totalValue / $quantity;
    $salvageValueUnit = $salvageValue / $quantity;
    
    for ($i = 1; $i <= $quantity; $i++) {
        $subAssets[] = self::create([
            'parent_id' => $parentId,
            'sub_asset_code' => $assetCode . '-' . sprintf('%02d', $i),
            'current_value' => $unitValue,
            'current_value_after_dep'=> $unitValue,
            'salvage_value' => $salvageValueUnit,
        ]);
    }
    
    return $subAssets;
}
    
}
