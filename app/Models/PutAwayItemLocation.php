<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PutAwayItemLocation extends Model
{
    use HasFactory;

    protected $table = 'erp_putaway_item_locations';

    protected $fillable = [
        'header_id', 
        'detail_id', 
        'item_id', 
        'packet_name', 
        'packet_number', 
        'storage_number', 
        'store_id', 
        'sub_store_id', 
        'wh_detail_id', 
        'rack_id', 
        'shelf_id', 
        'bin_id', 
        'quantity', 
        'inventory_uom_qty', 
        'status'
    ];

    public function header()
    {
        return $this->belongsTo(PutAwayHeader::class);
    }

    public function detail()
    {
        return $this->belongsTo(PutAwayDetail::class);
    }

    public function erpStore()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function erpSubStore()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }

    public function whDetail()
    {
        return $this->belongsTo(WhDetail::class, 'wh_detail_id');
    }
}
