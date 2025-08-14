<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomOverhead extends Model
{
    use HasFactory;

    protected $table = 'erp_bom_overheads';

    protected $fillable = [
        'bom_id',
        'bom_detail_id',
        'type',
        'level',
        'overhead_id',
        'overhead_description',
        'overhead_perc',
        'ledger_name',
        'overhead_amount',
        'ledger_id',
        'ledger_group_id'
    ];

    public function bom()
    {
        return $this->belongsTo(Bom::class,'bom_id');
    }

    public function bomItem()
    {
        return $this->belongsTo(BomDetail::class, 'bom_detail_id');
    } 

}
