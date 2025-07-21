<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockLedgerReservation extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'stock_ledger_reservations';

    protected $fillable = [
        'issue_header_id', 
        'issue_detail_id',
        'receipt_header_id', 
        'receipt_detail_id',
        'issue_book_type',
        'receipt_book_type',
        'stock_ledger_id',
        'quantity'
    ];
    // Define relationships
    public function stockLedger()
    {
        return $this->belongsTo(StockLedger::class, 'stock_ledger_id');
    }
}
