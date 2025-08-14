<?php

namespace App\Models;

use App\Models\JobOrder\JoProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MrnJoItem extends Model
{
    use HasFactory;

    protected $table = 'erp_mrn_jo_items';

    protected $fillable = [
        'type',
        'mrn_header_id',
        'mrn_detail_id',
        'jo_product_id',
        'jo_item_id',
        'mi_item_id',
        'store_id',
        'sub_store_id',
        'so_id',
        'item_id',
        'item_code',
        'uom_id',
        'attributes',
        'consumed_qty',
        'inventory_uom_qty',
        'cost_per_unit',
        'total_cost',
        'status'
    ];

    public function miItem()
    {
        return $this->belongsTo(ErpMiItem::class, 'mi_item_id');
    }

    public function item()
    {
        return $this->belongsTo(ErpItem::class, 'jo_item_id');
    }
    public function jobProduct()
    {
        return $this->belongsTo(JoProduct::class,'jo_product_id');
    }

    public function header()
    {
        return $this->belongsTo(MrnHeader::class, 'mrn_header_id');
    }

    public function detail()
    {
        return $this->belongsTo(MrnDetail::class, 'mrn_detail_id');
    }

    public function erpStore()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }
    public function subStore()
    {
        return $this->belongsTo(ErpSubStore::class, 'store_id');
    }
}
