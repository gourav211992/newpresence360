<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MrnItemLocation extends Model
{
    use HasFactory;

    protected $table = 'erp_mrn_item_locations';

    protected $fillable = [
        'mrn_header_id', 
        'mrn_detail_id', 
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

    public function mrnHeader()
    {
        return $this->belongsTo(MrnHeader::class);
    }

    public function mrnDetail()
    {
        return $this->belongsTo(MrnDetail::class);
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
