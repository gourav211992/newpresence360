<?php
namespace App\Models;

use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockLedgerLotNumber extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait, DefaultGroupCompanyOrg;

    protected $table = 'stock_ledger_lot_numbers';

    // Define relationships
    public function stockLedger()
    {
        return $this->belongsTo(StockLedger::class, 'stock_ledger_id');
    }

}
