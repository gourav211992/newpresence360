<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;

class FixedAssetDepreciationHistory extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable;

    protected $table = 'erp_finance_fixed_asset_depreciation_history';

    protected $guarded = ['id'];
    public function book(){
       return $this->belongsTo(Book::class, 'book_id');
    }
    public function getAssetsAttribute()
    {
        $assetIds = json_decode($this->attributes['assets'], true) ?? [];

        return FixedAssetRegistration::whereIn('id', $assetIds)->get();
    }
}
