<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;

class FixedAssetMaintenance extends Model
{
    use HasFactory, DefaultGroupCompanyOrg, Deletable;

    protected $table = 'erp_finance_fixed_asset_maintenance';
     protected $guarded = ['id'];

    public function asset()
    {
        return $this->belongsTo(FixedAssetRegistration::class, 'asset_id','id');
    }
}
