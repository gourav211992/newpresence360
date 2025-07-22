<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ErpEquipment extends Model
{

    protected $table = 'erp_equipment';
    protected $guarded = [];

    public function spareParts(): HasMany
    {
        return $this->hasMany(
            related: ErpEquipSparepartDetail::class,
            foreignKey: 'erp_equipment_id',
        );
    }

    public function maintenanceDetails(): HasMany
    {
        return $this->hasMany(
            related: ErpEquipMaintenanceDetail::class,
            foreignKey: 'erp_equipment_id',
        );
    }

    


}
