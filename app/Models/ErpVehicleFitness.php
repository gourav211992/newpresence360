<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpVehicleFitness extends Model
{
    use HasFactory;


    protected $fillable = [
        'vehicle_id',
        'fitness_no',
        'fitness_date',
        'fitness_expiry_date',
        'amount',
        'attachment_id',
        'created_at',
        'updated_at'
    ];

       public function fitnessAttachment()
    {
        return $this->hasOne(ErpVehicleMedia::class, 'id', 'attachment_id');
    }
}
