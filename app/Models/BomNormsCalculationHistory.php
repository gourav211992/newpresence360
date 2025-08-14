<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomNormsCalculationHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_bom_norms_cals_history';
    protected $fillable = [
        'bom_id',
        'source_id',
        'bom_detail_id',
        'qty_per_unit',
        'total_qty',
        'std_qty'
    ];

    public function bomDetail()
    {
        return $this->belongsTo(BomDetailHistory::class);
    }
}
