<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSoJobWorkItemAttribute extends Model
{
    use HasFactory;

    protected $table = 'erp_so_job_work_item_attributes';
    protected $fillable = [
        'sale_order_id',
        'job_work_item_id',
        'item_id',
        'item_code',
        'item_attribute_id',
        'attribute_name',
        'attr_name',
        'attribute_value',
        'attr_value'
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
        return $this->hasOne(AttributeGroup::class,'id' ,'attr_name');
    }

    public function headerAttributeValue()
    {
        return $this->hasOne(Attribute::class,'id','attr_value');
    }
}
