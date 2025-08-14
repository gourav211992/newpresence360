<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoProductHistory extends Model
{
    use HasFactory;
    protected $table = 'erp_mo_products_history';
    protected $fillable = [
        'source_id',
        'production_bom_id',
        'mo_id',
        'item_id',
        'customer_id',
        'item_code',
        'uom_id',
        'qty',
        'pwo_mapping_id',
        'order_id'
    ]; 

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function attributes()
    {
        return $this->hasMany(MoProductAttributeHistory::class,'mo_product_id');
    }

    public function customer()
    {
        return $this->belongsTo(ErpCustomer::class, 'customer_id');
    }
}
