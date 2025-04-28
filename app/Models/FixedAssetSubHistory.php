<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedAssetSubHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_finance_fixed_asset_sub_history';
     protected $guarded = ['id'];
     public function asset()
    {
        return $this->belongsTo(FixedAssetRegistrationHistory::class, 'parent_id');
    }
    public static function generateSubAssets($parentId, $assetCode, $quantity, $totalValue)
    {
        $subAssets = [];
        $unitValue = $totalValue / $quantity;
        
        for ($i = 1; $i <= $quantity; $i++) {
            $subAssets[] = self::create([
                'parent_id' => $parentId,
                'sub_asset_code' => $assetCode .'-'. sprintf('%02d', $i),
                'current_value' => $unitValue,
            ]);
        }
        
        return $subAssets;
    }
    public static function regenerateSubAssets($parentId, $assetCode, $quantity, $totalValue)
{
    // Delete all existing sub-assets with the same parent_id
    self::where('parent_id', $parentId)->delete();

    $subAssets = [];
    $unitValue = $totalValue / $quantity;
    
    for ($i = 1; $i <= $quantity; $i++) {
        $subAssets[] = self::create([
            'parent_id' => $parentId,
            'sub_asset_code' => $assetCode . '-' . sprintf('%02d', $i),
            'current_value' => $unitValue,
        ]);
    }
    
    return $subAssets;
}
    
}
