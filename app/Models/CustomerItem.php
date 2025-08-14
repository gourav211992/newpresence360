<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;

class CustomerItem extends Model
{
    use SoftDeletes,Deletable,DefaultGroupCompanyOrg;

    protected $table = 'erp_customer_items';

    protected $fillable = [
        'customer_id',
        'customer_code',
        'item_id',
        'item_code',
        'item_name',
        'part_number',
        'uom_id',
        'sell_price',
        'item_details',
        'organization_id', 
        'group_id',       
        'company_id'      
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class); 
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }
}
