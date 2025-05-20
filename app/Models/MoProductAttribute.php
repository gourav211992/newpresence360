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

    protected $appends = [
        'dis_attribute_name',
        'dis_attribute_value'
    ];

    public function getDisAttributeNameAttribute()
    {
        return $this->headerAttribute?->name ?? null;
    }
    
    public function getDisAttributeValueAttribute()
    {
        return $this->headerAttributeValue?->value ?? null;
    }
    
    public function headerAttribute()
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_name');
    }

    public function headerAttributeValue()
    {
        return $this->belongsTo(Attribute::class, 'attribute_value');
    }
}
