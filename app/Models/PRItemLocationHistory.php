<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PRItemLocationHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_purchase_return_item_locations_history';

    protected $fillable = [
        'header_id',
        'header_history_id', 
        'detail_id',
        'detail_history_id',
        'item_location_id', 
        'item_id', 
        'store_id', 
        'rack_id', 
        'shelf_id', 
        'bin_id', 
        'quantity',
        'inventory_uom_qty'
    ];

    public function header()
    {
        return $this->belongsTo(PRHeader::class, 'header_id');
    }

    public function headerHistory()
    {
        return $this->belongsTo(PRHeaderHistory::class, 'header_history_id');
    }

    public function detail()
    {
        return $this->belongsTo(PRDetail::class, 'detail_id');
    }

    public function detailHistory()
    {
        return $this->belongsTo(PRDetailHistory::class, 'detail_history_id');
    }

    public function itemLocation()
    {
        return $this->belongsTo(PRItemLocation::class, 'item_location_id');
    }

    public function erpStore()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
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
