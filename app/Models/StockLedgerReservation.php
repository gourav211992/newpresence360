<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockLedgerReservation extends Model
{
    use HasFactory;

    protected $table = 'stock_ledger_reservations';

    protected $fillable = [
        'stock_ledger_id',
        'mo_id',
        'mo_production_item_id',
        'so_id',
        'so_item_id',
        'quantity'
    ];
    // Define relationships
    public function stockLedger()
    {
        return $this->belongsTo(StockLedger::class, 'stock_ledger_id');
    }
}
