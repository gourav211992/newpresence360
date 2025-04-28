<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpTransporterRequestBid extends Model
{
    use HasFactory;

    protected $fillable =[
        'transporter_request_id',
        'transporter_id',
        'bid_price',
        'vehicle_number',
        'driver_name',
        'driver_contact_no',
        'transporter_remarks',
        'bid_status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    public function transporter(){
        return $this->belongsTo(Vendor::class,'transporter_id');
    }
}
