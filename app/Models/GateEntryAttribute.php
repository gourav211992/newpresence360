<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GateEntryAttribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_gate_entry_attributes';

    protected $fillable = [
        'header_id',
        'detail_id',
        'item_id',
        'item_code',
        'item_attribute_id',
        'attr_name',
        'attr_value',
    ];

    public function gateEntrydetail()
    {
        return $this->belongsTo(GateEntryDetail::class, 'detail_id');
    }

    public function gateEntryheader()
    {
        return $this->belongsTo(GateEntryHeader::class, 'header_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function itemAttribute()
    {
        return $this->belongsTo(ItemAttribute::class);
    }

    public function attributeName()
    {
        return $this->belongsTo(ErpAttributeGroup::class, 'attr_name');
    }

    public function attributeValue()
    {
        return $this->belongsTo(ErpAttribute::class, 'attr_value');
    }
}
