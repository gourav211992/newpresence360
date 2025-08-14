<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleBankSecurity extends Model
{
    protected $table = 'erp_vehicle_bank_securities';

    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(VehicleBankSecurity::class, 'vehicle_id');
    }
}
