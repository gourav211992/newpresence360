<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSaleInvoiceTedHistory extends Model
{
    use HasFactory;

    protected $referenceTables = [
        'taxDetail' => 'ted_id'
    ];

    protected $table = 'erp_sale_invoice_ted_history';

    public function taxDetail()
    {
        return $this->belongsTo(TaxDetail::class, 'ted_id');
    }
}
