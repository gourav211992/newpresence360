<?php

namespace App\Models;

use App\Models\JobOrder\JoProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MrnMiMapping extends Model
{
    use HasFactory;

    protected $table = 'mrn_mi_mappings';

    protected $fillable = [
        'mrn_header_id',
        'mrn_detail_id',
        'jo_product_id',
        'jo_item_id',
        'mi_item_id',
        'mi_qty',
        'mi_rate',
        'mi_inventory_uom_qty',
        'from_store_id',
        'to_store_id',
        'supplier_qty',
        'consumed_qty',
        'consumed_inventory_uom_qty',
    ];

    public function miItem()
    {
        return $this->belongsTo(ErpMiItem::class, 'mi_item_id');
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
}
