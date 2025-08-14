<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpVehicleRoadTax extends Model
{
    use HasFactory;

        protected $fillable = [
        'vehicle_id',
        'road_tax_from',
        'road_tax_to',
        'road_tax_amount',
        'road_paid_on',
        'attachment_id',
        'created_at',
        'updated_at'
    ];

         public function roadTaxAttachment()
    {
        return $this->hasOne(ErpVehicleMedia::class, 'id', 'attachment_id');
    }
}
