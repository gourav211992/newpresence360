<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLedgerStoragePoint extends Model
{
    use HasFactory;

    protected $table = 'stock_ledger_storage_points';

    protected $fillable = [
        'stock_ledger_id', 
        'item_id', 
        'packet_name', 
        'packet_number', 
        'storage_number', 
        'wh_detail_id', 
        'store_id', 
        'sub_store_id', 
        'quantity', 
        'status'
    ];

    public function stockLedger()
    {
        return $this->belongsTo(StockLedger::class, 'stock_ledger_id');
    }
    
    public function item()
    {
        return $this->belongsTo(ErpItem::class);
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
