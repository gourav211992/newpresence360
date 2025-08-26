<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpBundleItemDetail extends Model
{
    use SoftDeletes;

    protected $table = 'erp_bundle_item_details';

    protected $fillable = [
        'bundle_id',
        'item_id',
        'item_code',
        'item_name',
        'uom_id',
        'hsn_id',
        'qty',
        'group_id',
        'company_id',
        'organization_id'
    ];

    /** Relationships */

    public function bundle()
    {
        return $this->belongsTo(ErpItemBundle::class, 'bundle_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
     public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }
      public function uom()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function attributes()
    {
        return $this->hasMany(ErpBundleItemAttribute::class, 'bundle_item_id');
    }
}