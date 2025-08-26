<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpBundleItemAttribute extends Model
{
    use SoftDeletes;

    protected $table = 'erp_bundle_item_attributes';

    protected $fillable = [
        'bundle_id',
        'bundle_item_id',
        'item_attribute_id',
        'item_code',
        'attribute_name',
        'attr_name',
        'attribute_value',
        'attr_value'
    ];

    /** Relationships */

    public function bundle()
    {
        return $this->belongsTo(ErpItemBundle::class, 'bundle_id');
    }

    public function bundleItem()
    {
        return $this->belongsTo(ErpBundleItemDetail::class, 'bundle_item_id');
    }

    public function attributeGroup()
    {
        return $this->belongsTo(ErpAttributeGroup::class, 'attr_name');
    }

    public function attribute()
    {
        return $this->belongsTo(ErpAttribute::class, 'attr_value');
    }
}