<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpPqItemAttribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pq_id',
        'pq_item_id',
        'item_attribute_id',
        'item_code',
        'attribute_name',
        'attr_name',
        'attribute_value',
        'attr_value'
    ];

    protected $hidden = ['deleted_at'];

    public $referencingRelationships = [
        'itemAttribute' => 'item_attribute_id',
        'headerAttribute' => 'attr_name',
        'headerAttributeValue' => 'attr_value'
    ];

    public function itemAttribute()
    {
        return $this->belongsTo(ItemAttribute::class, 'item_attribute_id');
    }

    public function headerAttribute()
    {
        return $this->hasOne(AttributeGroup::class, 'id', 'attr_name');
    }

    public function headerAttributeValue()
    {
        return $this->hasOne(Attribute::class, 'id', 'attr_value');
    }
}
