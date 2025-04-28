<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomNormsCalculation extends Model
{
    use HasFactory;

    protected $table = 'erp_bom_norms_cals';
    protected $fillable = [
        'bom_id',
        'bom_detail_id',
        'qty_per_unit',
        'total_qty',
        'std_qty'
    ];

    public function bomDetail()
    {
        return $this->belongsTo(BomDetail::class);
    }

}
