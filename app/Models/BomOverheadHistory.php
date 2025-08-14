<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomOverheadHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_bom_overheads_history';

    protected $fillable = [
        'source_id',
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
}