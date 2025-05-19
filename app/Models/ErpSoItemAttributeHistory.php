<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSoItemAttributeHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_so_item_attributes_history';

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
        return $this->hasOne(AttributeGroup::class,'id' ,'attr_name');
    }

    public function headerAttributeValue()
    {
        return $this->hasOne(Attribute::class,'id','attr_value');
    }
}
