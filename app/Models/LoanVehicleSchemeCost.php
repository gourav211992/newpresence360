<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanVehicleSchemeCost extends Model
{
    protected $table = 'erp_loan_vehicle_scheme_costs';

    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(LoanVehicleSchemeCost::class, 'vehicle_id');
    }
}
