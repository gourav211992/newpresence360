<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;

class ErpAssetCategory extends Model
{
    protected $table = 'erp_asset_category';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
     use HasFactory, DefaultGroupCompanyOrg, Deletable,softDeletes;
     protected $guarded = ['id'];
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'string', // Cast the status column as string
    ];
    public function setup()
    {
        return $this->hasOne(FixedAssetSetup::class, 'asset_category_id');
    }
    public function assets()
    {
        return $this->hasMany(FixedAssetRegistration::class, 'category_id');
    }
    

}
