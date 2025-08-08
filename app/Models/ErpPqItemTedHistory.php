<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpPqItemTedHistory extends Model
{
    use HasFactory;

    protected $referenceTables = [
        'taxDetail' => 'ted_id'
    ];

    protected $table = 'erp_pb_item_teds_history';

    public function taxDetail()
    {
        return $this->belongsTo(TaxDetail::class, 'ted_id');
    }
}

