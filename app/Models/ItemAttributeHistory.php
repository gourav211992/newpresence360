<?php

namespace App\Models;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Deletable;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemAttributeHistory extends Model
{
    use HasFactory,Deletable,SoftDeletes;

    protected $table = 'erp_item_attributes_history';

    protected $fillable = [
        'source_id',
        'item_id',
        'attribute_group_id',
        'attribute_id',
        'required_bom',
        'all_checked'
    ];

    protected $casts = [
        'attribute_id' => 'array',
    ];

    public function getAttributeIdAttribute()
    {
        $attributeId = $this->attributes['attribute_id'] ?? null;
        $arr = is_string($attributeId) && json_decode($attributeId) ? json_decode($attributeId, true) : [];

        if ($this->attributes['all_checked'] ?? false) {
            $attribute_group_id = $this->attributes['attribute_group_id'] ?? null;
            if ($attribute_group_id) {
                $arr = Attribute::where('attribute_group_id', $attribute_group_id)
                    ->pluck('id')
                    ->toArray();
            }
        }

        return $arr;
    }


    public function erpItem()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function attributes()
    {
        if (is_array($this->attribute_id) && !empty($this->attribute_id)) {
            return Attribute::whereIn('id', $this->attribute_id)->get();
        }
        return compact([]);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function group()
    {
        return $this->belongsTo(AttributeGroup::class,'attribute_group_id','id');
    }

   
    public function attributeGroup()
    {
        return $this->belongsTo(AttributeGroup::class);
    }
}
