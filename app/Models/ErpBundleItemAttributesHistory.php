<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpBundleItemAttributesHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_bundle_item_attributes_history';

    protected $fillable = [
        'source_id',
        'bundle_id',
        'bundle_item_id',
        'item_attribute_id',
        'item_code',
        'attribute_name',
        'attr_name',
        'attribute_value',
        'attr_value',
    ];
}
