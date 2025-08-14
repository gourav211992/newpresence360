<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoProductionItemLocation extends Model
{
    use HasFactory;
    protected $table = 'erp_mo_production_item_locations';

    protected $fillable = [
        'mo_id',
        'mo_production_item_id',
        'item_id',
        'item_code',
        'store_id',
        'sub_store_id',
        'store_code',
        'rack_id',
        'rack_code',
        'shelf_id',
        'shelf_code',
        'bin_id',
        'bin_code',
        'quantity',
        'inventory_uom_qty'
    ];
    
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function header()
    {
        return $this->belongsTo(MfgOrder::class, 'mo_id');
    }

    public function detail()
    {
        return $this->belongsTo(MoProductionItem::class, 'mo_production_item_id');
    }

    public function erpStore()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function sub_store()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }
    
    public function erpRack()
    {
        return $this->belongsTo(ErpRack::class, 'rack_id');
    }

    public function erpShelf()
    {
        return $this->belongsTo(ErpShelf::class, 'shelf_id');
    }

    public function erpBin()
    {
        return $this->belongsTo(ErpBin::class, 'bin_id');
    }
}
