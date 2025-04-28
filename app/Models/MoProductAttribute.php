<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoProductAttribute extends Model
{
    use HasFactory;
    protected $table = 'erp_mo_product_attributes';
    protected $fillable = [
        'mo_id',
        'mo_product_id',
        'item_attribute_id',
        'item_code',
        'attribute_group_id',
        'attribute_name',
        'attribute_value'
    ];

    public function headerAttribute()
    {
        return $this->hasOne(AttributeGroup::class,'id' ,'attribute_name');
    }

    public function headerAttributeValue()
    {
        return $this->hasOne(Attribute::class,'id','attribute_value');
    }
}
