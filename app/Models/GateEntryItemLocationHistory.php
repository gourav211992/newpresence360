<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GateEntryItemLocationHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_gate_entry_item_locations_history';

    protected $fillable = [
        'source_id',
        'header_id',
        'detail_id',
        'item_id',
        'store_id',
        'rack_id',
        'shelf_id',
        'bin_id',
        'quantity',
        'inventory_uom_qty',
    ];

    public function gateEntryHeader()
    {
        return $this->belongsTo(GateEntryHeader::class);
    }

    public function gateEntryDetail()
    {
        return $this->belongsTo(GateEntryDetail::class);
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

    public function gateEntryHeaderHistory()
    {
        return $this->belongsTo(GateEntryHeaderHistory::class);
    }

    public function gateEntryDetailHistory()
    {
        return $this->belongsTo(GateEntryDetailHistory::class);
    }

    public function gateEntryItemLocation()
    {
        return $this->belongsTo(GateEntryItemLocation::class);
    }
}
