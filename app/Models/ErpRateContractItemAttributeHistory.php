<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRateContractItemAttributeHistory extends Model
{
    use HasFactory;
    protected $table = 'erp_rate_contract_item_attributes_history';
    protected $fillable = [
        'source_id',
        'rate_contract_id',
        'rate_contract_item_id',
        'item_attribute_id',
        'item_code',
        'attribute_name',
        'attribute_value',
        'attr_name',
        'attr_value',

    ];
    
    public function source()
    {
        return $this->belongsTo(ErpRateContractItemAttribute::class, 'source_id', 'id');
    }
    public function rateContract()
    {
        return $this->belongsTo(ErpRateContract::class, 'rate_contract_id', 'id');
    }
    public function rateContractItem()
    {
        return $this->belongsTo(ErpRateContractItem::class, 'rate_contract_item_id', 'id');
    }
    public function itemAttribute()
    {
        return $this->belongsTo(ErpItemAttribute::class, 'item_attribute_id', 'id');
    }
    

}
