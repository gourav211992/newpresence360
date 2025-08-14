<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpVehicleInsurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'policy_no',
        'insurance_company',
        'insurance_date',
        'insurance_expiry_date',
        'amount',
        'attachment_id',
        'created_at',
        'updated_at'
    ];

        public function insuranceAttachment()
    {
        return $this->hasOne(ErpVehicleMedia::class, 'id', 'attachment_id');
    }
}
