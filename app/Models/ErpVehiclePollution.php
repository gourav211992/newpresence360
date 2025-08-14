<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpVehiclePollution extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'pollution_no',
        'pollution_date',
        'pollution_expiry_date',
        'amount',
        'attachment_id',
        'created_at',
        'updated_at'
    ];

        public function pollutionAttachment()
    {
        return $this->hasOne(ErpVehicleMedia::class, 'id', 'attachment_id');
    }
}
