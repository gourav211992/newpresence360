<?php

namespace App\Models\Scrap;

use App\Traits\DateFormatTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ErpScrapItemAttribute extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait;

    protected $fillable = [
        'erp_scrap_id',
        'scrap_item_id',
        'item_attribute_id',
        'item_code',
        'attribute_name',
        'attribute_value',
        'attr_name',
        'attr_value',
    ];

    public function scrap()
    {
        return $this->belongsTo(ErpScrap::class);
    }

    public function header()
    {
        return $this->belongsTo(ErpScrapItem::class, 'scrap_item_id');
    }

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
