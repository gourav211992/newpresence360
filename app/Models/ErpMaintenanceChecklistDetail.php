<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpMaintenanceChecklistDetail extends Model
{
    protected $table = 'erp_maintenance_checklist_details';

    protected $fillable = [
        'erp_maintenance_id',
        'erp_equip_maintenance_checklist_id',
        'checklist_name',
        'checklist_answer',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function erpMaintenance()
    {
        return $this->belongsTo(ErpMaintenance::class, 'erp_maintenance_id');
    }

    public function erpEquipMaintenanceChecklist()
    {
        return $this->belongsTo(ErpEquipMaintenanceChecklist::class, 'erp_equip_maintenance_checklist_id');
    }
}
