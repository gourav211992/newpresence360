<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpEquipMaintenanceDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function equipment()
    {
        return $this->belongsTo(ErpEquipment::class, 'erp_equipment_id');
    }
    public function checklists()
    {
        return $this->hasMany(ErpEquipMaintenanceChecklist::class, 'erp_equip_maintenance_id');
    }

    public function maintenanceType()
    {
        return $this->belongsTo(ErpMaintenanceType::class, 'maintenance_type_id');
    }
     public function bom()
    {
        return $this->belongsTo(PlantMaintBom::class, 'maintenance_bom_id');
    }
}
