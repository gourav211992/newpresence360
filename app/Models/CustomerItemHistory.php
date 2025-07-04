<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerItemHistory extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'erp_customer_items_history';

    protected $fillable = [
        'source_id',
        'item_id',
        'customer_id',
        'uom_id',
        'customer_code',
        'item_code',
        'item_name',
        'part_number',
        'item_details',
        'sell_price',
        'group_id',
        'company_id',
        'organization_id'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }
}
