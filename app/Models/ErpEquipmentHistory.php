<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;

class ErpEquipmentHistory extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable;


    protected $table = 'erp_equipment_history';
    protected $guarded = [];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(
            related: Organization::class,
            foreignKey: 'organization_id',
        );
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(
            related: ErpStore::class,
            foreignKey: 'location_id',
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            related: Category::class,
            foreignKey: 'category_id',
        );
    }

    public function spareParts(): HasMany
    {
        return $this->hasMany(ErpEquipSparepartDetailHistory::class,'erp_equipment_id','source_id'
        );
    }

    public function maintenanceDetails(): HasMany
    {
        return $this->hasMany(ErpEquipMaintenanceDetailHistory::class,'erp_equipment_id','source_id'
        );
    }

}
