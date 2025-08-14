<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ErpTransportInvoiceTedHIstory extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'erp_transport_invoice_ted_history';

    
    protected $hidden = ['deleted_at'];

    public function taxDetail()
    {
        return $this->belongsTo(TaxDetail::class, 'ted_id');
    }
}
