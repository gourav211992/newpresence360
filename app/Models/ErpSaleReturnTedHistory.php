<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSaleReturnTedHistory extends Model
{
    use HasFactory;
    public function taxDetail()
    {
        return $this->belongsTo(TaxDetail::class, 'ted_id');
    }
}
