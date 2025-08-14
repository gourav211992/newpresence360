<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpVehiclePermit extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'type',
        'permit_no',
        'permit_date',
        'permit_expiry_date',
        'amount',
        'attachment_id',
        'created_at',
        'updated_at'
    ];

      public function permitAttachment()
    {
        return $this->hasOne(ErpVehicleMedia::class, 'id', 'attachment_id');
    }
}
