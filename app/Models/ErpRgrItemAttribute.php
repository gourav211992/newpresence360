<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpRgrItemAttribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_rgr_item_attributes';

    protected $fillable = [
        'rgr_id',
        'rgr_item_id',
        'item_attribute_id',
        'item_code',
        'attribute_name',
        'attr_name',
        'attribute_value',
        'attr_value',
    ];

    public function rgr()
    {
        return $this->belongsTo(ErpRgr::class, 'rgr_id');
    }

    // Parent RGR Item
    public function rgrItem()
    {
        return $this->belongsTo(ErpRgrItem::class, 'rgr_item_id');
    }

    public function attributeGroup()
    {
        return $this->belongsTo(AttributeGroup::class, 'attr_name', 'id');
    }

     public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attr_value', 'id');
    }

    public function Itemattribute()
    {
        return $this->belongsTo(ItemAttribute::class, 'item_attribute_id', 'id');
    }

}
