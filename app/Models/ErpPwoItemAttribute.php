<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpPwoItemAttribute extends Model
{
    use HasFactory;

    protected $table = "erp_pwo_item_attributes";
    protected $fillable = [
        'pwo_id',
        'pwo_item_id',
        'item_attribute_id',
        'item_id',
        'item_code',
        'attribute_group_id',
        'attribute_id',
        'attribute_name',
        'attribute_value',
    ];

    public $referencingRelationships = [
        'source' => 'source_id',
        'item' => 'item_id',
        'itemAttribute' => 'item_attribute_id',
        'headerAttribute' => 'attribute_name',
        'headerAttributeValue' => 'attribute_value'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    
    public function item_attribute()
    {
        return $this->belongsTo(ItemAttribute::class, 'item_attribute_id');
    }

    public function headerAttribute()
    {
        return $this->hasOne(AttributeGroup::class,'attribute_group_id' ,'id');
    }

    public function headerAttributeValue()
    {
        return $this->hasOne(Attribute::class,'attribute_id','id');
    }
    public function header()
    {
        return $this->belongsTo(ErpProductionWorkOrder::class,'pwo_id');
    }
    public function header_item()
    {
        return $this->belongsTo(ErpPwoItem::class,'pwo_item_id');
    }
    
}
