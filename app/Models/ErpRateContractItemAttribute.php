<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRateContractItemAttribute extends Model
{
    use HasFactory;

    protected $table = 'erp_rate_contract_item_attributes';
    protected $fillable = [
        'rate_contract_id',
        'rate_contract_item_id',
        'item_attribute_id',
        'item_code',
        'attribute_name',
        'attribute_value',
        'attr_name',
        'attr_value',

    ];
    
    public function header()
    {
        return $this->belongsTo(ErpRateContract::class, 'rate_contract_id', 'id');
    }
    public function headerItem()
    {
        return $this->belongsTo(ErpRateContractItem::class, 'rate_contract_item_id', 'id');
    }
    public function itemAttribute()
    {
        return $this->belongsTo(ErpItemAttribute::class, 'item_attribute_id', 'id');
    }  
}
