<?php
namespace App\Models;

use App\Traits\DateFormatTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockLedgerDetail extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait;

    protected $table = 'stock_ledger_details';

    // Define relationships
    public function stockLedger()
    {
        return $this->belongsTo(StockLedger::class, 'stock_ledger_id');
    }

    public function rack()
    {
        return $this->belongsTo(ErpRack::class, 'rack_id');
    }

    public function shelf()
    {
        return $this->belongsTo(ErpShelf::class, 'shelf_id');
    }

    public function bin()
    {
        return $this->belongsTo(ErpBin::class, 'bin_id');
    }

}
