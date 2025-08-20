<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderTedHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_purchase_order_ted_history';

    protected $fillable = [
        'hsn_id',
        'tax_amount',
        'tax_breakup',
        'purchase_order_id',
        'source_id',
        'po_item_id',
        'ted_type',
        'ted_level',
        'ted_id',
        'ted_name',
        'assessment_amount',
        'ted_perc',
        'ted_amount',
        'applicable_type',
    ];

    protected $casts = [
        'tax_breakup' => 'array',
    ];

    public $referencingRelationships = [
        'hsn' => 'hsn_id',
        'taxDetail' => 'ted_id',
    ];

    public function hsn()
    {
        return $this->belongsTo(Hsn::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrderHistory::class, 'purchase_order_id');
    }

    public function poItem()
    {
        return $this->belongsTo(PoItemHistory::class, 'po_item_id');
    }

    public function taxDetail()
    {
        return $this->belongsTo(TaxDetail::class, 'ted_id');
    }
}
