<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpMaintenanceDefectDetailHistory extends Model
{
    protected $table = 'erp_maintenance_defect_detail_histories';

    protected $fillable = [
        'erp_maintenance_id',
        'erp_equip_sparepart_id',
        'defect_type_id',
        'priority',
        'due_date',
        'description',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function erpMaintenance()
    {
        return $this->belongsTo(ErpMaintenance::class, 'erp_maintenance_id');
    }

    public function erpEquipSparepart()
    {
        return $this->belongsTo(ErpEquipSparepartDetail::class, 'erp_equip_sparepart_id');
    }

    public function defectType()
    {
        return $this->belongsTo(ErpDefectType::class, 'defect_type_id');
    }
}
