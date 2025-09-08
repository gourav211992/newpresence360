<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpEquipMaintenanceChecklist extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'erp_equip_maintenance_id',
        'name',
        'description',
        'type',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        'checklist_detail',
    ];

    public function maintenanceDetail()
    {
        return $this->belongsTo(ErpEquipMaintenanceDetail::class, 'erp_equip_maintenance_id');
    }
}
