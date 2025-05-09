<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpPlItemAttribute extends Model
{
    use HasFactory;
    protected $fillable = [
        'pl_id',
        'pl_item_id',
        'item_attribute_id',
        'item_code',
        'attribute_name',
        'attr_name',
        'attribute_value',
        'attr_value',
    ];

    public function itemAttribute()
    {
        return $this->belongsTo(ItemAttribute::class, 'item_attribute_id');
    }

    public function headerAttribute()
    {
        return $this->hasOne(AttributeGroup::class,'id' ,'attr_name');
    }

    public function headerAttributeValue()
    {
        return $this->hasOne(Attribute::class,'id','attr_value');
    }

    public function pl_item()
    {
        return $this->hasOne(ErpPlItem::class,'pl_item_id');
    }
}
