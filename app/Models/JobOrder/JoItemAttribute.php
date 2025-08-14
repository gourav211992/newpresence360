<?php

namespace App\Models\JobOrder;

use App\Models\AttributeGroup;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoItemAttribute extends Model
{
    use HasFactory;
    protected $table = 'erp_jo_item_attributes';
    protected $fillable = [
        'jo_id',
        'jo_item_id',
        'item_attribute_id',
        'item_id',
        'item_code',
        'attribute_name',
        'attribute_value',
    ];
    public $referencingRelationships = [
        'item' => 'item_id',
        'itemAttribute' => 'item_attribute_id',
        'headerAttribute' => 'attribute_name',
        'headerAttributeValue' => 'attribute_value'
    ];
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    public function itemAttribute()
    {
        return $this->belongsTo(ItemAttribute::class, 'item_attribute_id');
    }
    public function headerAttribute()
    {
        return $this->hasOne(AttributeGroup::class,'id' ,'attribute_name');
    }
    public function headerAttributeValue()
    {
        return $this->hasOne(Attribute::class,'id','attribute_value');
    }
}
