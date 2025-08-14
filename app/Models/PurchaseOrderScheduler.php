<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderScheduler extends Model
{
    use HasFactory;

    public $table = 'erp_purchase_order_schedulers';

    protected $guarded = [];


    public function to()
    {
        return $this->morphTo();
    }
}
