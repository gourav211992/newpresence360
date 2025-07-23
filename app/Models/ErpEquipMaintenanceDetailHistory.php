<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpEquipMaintenanceDetailHistory extends Model
{
    use HasFactory;

     protected $fillable = [
        'erp_equipment_id',
        'maintenance_type_id',
        'type',
        'frequency',
        'time',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function equipment()
    {
        return $this->belongsTo(ErpEquipmentHistory::class, 'erp_equipment_id','source_id');
    }
    public function checklists()
    {
        return $this->hasMany(ErpEquipMaintenanceChecklistHistory::class, 'erp_equip_maintenance_id');
    }
}
