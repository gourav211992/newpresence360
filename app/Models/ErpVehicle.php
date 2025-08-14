<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpVehicle extends Model
{
    use HasFactory,DefaultGroupCompanyOrg;

     protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'transporter_id',
        'lorry_no',
        'vehicle_type_id',
        'chassis_no',
        'engine_no',
        'rc_no',
        'rto_no',
        'company_name',
        'model_name',
        'capacity_kg',
        'driver_id',
        'fuel_type',
        'purchase_date',
        'ownership',
        'vehicle_attachment',
        'vehicle_video',
        'attachment_id',
        'status',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

       public function auth_user()
    {
        return $this->belongsTo(AuthUser::class, 'created_by', 'id');
    }

    public function fitness()
    {
        return $this->hasOne(ErpVehicleFitness::class, 'vehicle_id');
    }

    public function pollution()
    {
        return $this->hasOne(ErpVehiclePollution::class, 'vehicle_id');
    }

    public function permit()
    {
        return $this->hasOne(ErpVehiclePermit::class, 'vehicle_id');
    }

    public function insurance()
    {
        return $this->hasOne(ErpVehicleInsurance::class, 'vehicle_id');
    }

    public function roadTax()
    {
        return $this->hasOne(ErpVehicleRoadTax::class, 'vehicle_id');
    }

      public function driver()
    {
        return $this->belongsTo(ErpDriver::class, 'driver_id');
    }

        public function transporter()
    {
        return $this->belongsTo(Organization::class, 'transporter_id');
    }

     public function vehicleType()
    {
        return $this->belongsTo(ErpVehicleType::class, 'vehicle_type_id');
    }

        public function attachment()
    {
        return $this->hasOne(ErpVehicleMedia::class, 'id', 'attachment_id');
    }

    public function vehicleAttachment()
    {
        return $this->hasOne(ErpVehicleMedia::class, 'id', 'vehicle_attachment');
    }

    public function vehicleVideo()
    {
        return $this->hasOne(ErpVehicleMedia::class, 'id', 'vehicle_video');
    }

}
