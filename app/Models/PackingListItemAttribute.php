<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingListItemAttribute extends Model
{
    use HasFactory;
    protected $table = 'erp_packing_list_item_attributes';
    protected $fillable = [
        'plist_id',
        'plist_detail_id',
        'plist_item_id',
        'item_attribute_id',
        'attribute_name',
        'attr_name',
        'attribute_value',
        'attr_value'
    ];
}
