<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpVehicleType extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg;



    protected $fillable = [
         'organization_id',
        'group_id',
        'company_id',
        'name',
        'capacity',
        'uom_id',
        'description',
        'status',
        'created_at',
        'updated_at'
    ];

      public function unit()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

       public function vehicle()
    {
        return $this->belongsTo(ErpVehicle::class, 'vehicle_type_id');
    }
}
