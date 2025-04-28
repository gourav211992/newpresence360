<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomAttribute extends Model
{
    use HasFactory;

    protected $table = 'erp_bom_attributes';

    protected $fillable = [
        'bom_id',
        'bom_detail_id',
        'item_attribute_id',
        'type',
        'item_code',
        'attribute_name',
        'attribute_value'
    ];

    public $referencingRelationships = [
        'itemAttribute' => 'item_attribute_id',
        'headerAttribute' => 'attribute_name',
        'headerAttributeValue' => 'attribute_value'  
    ];

    public function itemAttribute()
    {
        return $this->hasOne(ItemAttribute::class,'id' ,'item_attribute_id');
    }

    public function headerAttribute()
    {
        return $this->hasOne(AttributeGroup::class,'id' ,'attribute_name');
    }

    public function headerAttributeValue()
    {
        return $this->hasOne(Attribute::class,'id','attribute_value');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function bomDetail()
    {
        return $this->belongsTo(BomDetail::class, 'bom_detail_id');
    }

    public function soItem()
    {
        return $this->hasOne(ErpSoItem::class, 'item_id', 'item_id');
    }
}
