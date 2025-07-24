<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpEquipSparepartDetailHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'erp_equipment_id',
        'item_code',
        'item_name',
        'attributes',
        'uom',
        'qty',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function equipment()
    {
        return $this->belongsTo(ErpEquipmentHistory::class, 'erp_equipment_id','source_id');
    }
}
