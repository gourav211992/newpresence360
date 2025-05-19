<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpPwoItemHistory extends Model
{
    use HasFactory;
    protected $table = "erp_pwo_item_history";

    protected $fillable = [
        'source_id',
        'pwo_id',
        'item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'order_qty',
        'manf_order_qty',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'so_id'
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'inventoryUom' => 'inventory_uom_id',
    ];

    public function header()
    {
        return $this->belongsTo(ErpProductionWorkOrder::class, 'pwo_id');
    }

    
    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }

    public function attributes()
    {
        return $this->hasMany(ErpPwoItemAttributeHistory::class,'pwo_item_id');
    }

    public function source()
    {
        return $this->belongsTo(ErpPwoItem::class,'source_id');
    }
    public function mapping(){
        return $this->hasMany(PwoSoMapping::class,'pwo_item_id');
    }
    public function mappedids(){
        $this->mapping()->select('pwo_item_id', 'so_item_id') // Select only needed columns
        ->get()
        ->pluck('so_item_id') // Extract only so_item_id values
        ->unique() // Remove duplicate values
        ->values();
    }
}
