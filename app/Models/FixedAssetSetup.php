<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Illuminate\Database\Eloquent\SoftDeletes;


class FixedAssetSetup extends Model
{
    use HasFactory, DefaultGroupCompanyOrg, Deletable, softDeletes;

    protected $table = 'erp_finance_fixed_asset_setup';
    protected $guarded = ['id'];
    public function assetCategory()
    {
        return $this->belongsTo(ErpAssetCategory::class, 'asset_category_id');
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }

    public function ledgerGroup()
    {
        return $this->belongsTo(Group::class, 'ledger_group_id');
    }
    public static function generateuniquePrefix($name)
    {
        // Clean and prepare name
        $cleanedName = preg_replace('/[^a-zA-Z\s]/', '', $name); // Keep only letters and spaces
        $cleanedName = strtoupper(trim($cleanedName));
        $words = preg_split('/\s+/', $cleanedName); // Split by spaces

        if (count($words) === 0)
            return null;

        $used = FixedAssetSetup::pluck('prefix')->map(function ($p) {
            return strtoupper($p);
        })->toArray();

        $candidates = [];

        // 1. If 2+ words, try 2-letter (first letters of first two words)
        if (count($words) >= 2) {
            $prefix2 = substr($words[0], 0, 2) . substr($words[1], 0, 1);
            $candidates[] = $prefix2;

            // 2. If 3+ words, try 3-letter (first letters of all three)
            if (count($words) >= 3) {
                $prefix3 = substr($words[0], 0, 1) . substr($words[1], 0, 1) . substr($words[2], 0, 1);
                $candidates[] = $prefix3;
            }
        }

        // 3. If only one word or more variations needed
        $base = str_replace(' ', '', $cleanedName); // Combine name into single string
        $baseLength = strlen($base);

        // Try all 2 and 3 letter combinations starting with first letter
        for ($i = 1; $i < $baseLength; $i++) {
            // $prefix2 = $base[0] . $base[$i];
            // if (strlen($prefix2) === 2) {
            //     $candidates[] = $prefix2;
            // }

            for ($j = $i + 1; $j < $baseLength; $j++) {
                $prefix3 = $base[0] . $base[$i] . $base[$j];
                if (strlen($prefix3) === 3) {
                    $candidates[] = $prefix3;
                }
            }
        }

        // Remove duplicates and check against used
        $candidates = array_unique($candidates);

        foreach ($candidates as $candidate) {
            if (!in_array($candidate, $used)) {
                return $candidate;
            }
        }

        return null; // No available prefix
    }
    public static function getPrefix($id)
    {
        $category = ErpAssetCategory::find($id);
        
        $setup = FixedAssetSetup::find($category?->setup?->id);
        if (empty($setup->prefix)) {
            $name = $category->name;
            return self::generateuniquePrefix($name);
        } else
            return $setup->prefix;
    }
    public static function updatePrefix($reg_id, $prefix)
    {
        $reg = FixedAssetRegistration::find($reg_id);

        if (empty($reg)) {
            return;
        }

        $setup = FixedAssetSetup::find($reg?->category?->setup?->id);
        if (empty($setup)) {
            return;
        }


        if ($setup->prefix == null) {
            $setup->prefix = $prefix;
            $setup->save();
        }
    }
}
