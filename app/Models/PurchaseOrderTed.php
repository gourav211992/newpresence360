<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderTed extends Model
{
    use HasFactory;

    protected $table = 'erp_purchase_order_ted';

    protected $fillable = [
        'hsn_id',
        'tax_amount',
        'tax_breakup',
        'purchase_order_id',
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

    protected $appends = [
        'ted_name'
    ];

    protected $casts = [
        'tax_breakup' => 'array',
    ];

    public $referencingRelationships = [
        'hsn' => 'hsn_id',
        'taxDetail' => 'ted_id',
    ];

    public function getTaxBreakupAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getTedNameAttribute()
    {
        $tedName = null;
        $tedId = $this->ted_id ?? null;
        if (!$tedId) {
            return null;
        }
        switch ($this->ted_type ?? null) {
            case 'Tax':
                $tedName = TaxDetail::where('id', $tedId)->value('tax_type');
                break;

            case 'Expense':
                $tedName = ExpenseMaster::where('id', $tedId)->value('name');
                break;

            case 'Discount':
                $tedName = DiscountMaster::where('id', $tedId)->value('name');
                break;

            default:
                $tedName = null;
                break;
        }
        return $tedName;
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function poItem()
    {
        return $this->belongsTo(PoItem::class, 'po_item_id');
    }

    public function hsn()
    {
        return $this->belongsTo(Hsn::class);
    }

    public function taxDetail()
    {
        return $this->belongsTo(TaxDetail::class, 'ted_id', 'id');
    }
}
