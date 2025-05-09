<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoItemAttribute extends Model
{
    use HasFactory;
    protected $table = 'erp_mo_item_attributes';

    protected $fillable = [
        'mo_id',
        'mo_item_id',
        'item_id',
        'item_code',
        'item_attribute_id',
        'attribute_name',
        'attribute_value'
    ];

    public function getAttrNameAttribute()
    {
        $attributeGroup = AttributeGroup::find($this -> attribute_name);
        return $attributeGroup ?-> name ?? '';
    }

    public function getAttrValueAttribute()
    {
        $attribute = Attribute::find($this -> attribute_value);
        return $attribute ?-> value ?? '';
    }
}
