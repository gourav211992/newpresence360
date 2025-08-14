<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ErpTransportInvoiceTed extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'erp_transport_invoice_ted';

    protected $referenceTables = [
        'taxDetail' => 'ted_id'
    ];

    protected $fillable = [
        'transport_invoice_id',
        'invoice_item_id',
        'ted_type',
        'ted_level',
        'ted_id',
        'ted_group_code',
        'ted_name',
        'assessment_amount',
        'ted_percentage',
        'ted_amount',
        'applicable_type',
    ];

    protected $hidden = ['deleted_at'];

    public function taxDetail()
    {
        return $this->belongsTo(TaxDetail::class, 'ted_id');
    }
}
