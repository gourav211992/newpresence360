<?php

namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Traits\DateFormatTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpSaleReturnItemAttribute extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait;

    protected $fillable = [
        'sale_return_id',
        'sale_return_item_id',
        'item_attribute_id',
        'item_code',
        'attribute_name',
        'attribute_value',
        'attr_name',
        'attr_value',
    ];
    public function header(){
        return $this->belongsTo(ErpSaleReturnItem::class, 'sale_return_item_id');
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
