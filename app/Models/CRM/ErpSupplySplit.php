<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSupplySplit extends Model
{
    use HasFactory;

     protected $fillable = [
        "organization_id",
        "customer_id",
        "customer_code",
        "supply_partner_id",
        'supply_percentage'
    ];

    protected $appends = [
        'partner_name',
    ];

    public function getPartnerNameAttribute()
    {
        $supplyPartner = ErpSupplyPartner::where('id',$this->supply_partner_id)->first();
        return $supplyPartner ? $supplyPartner->name : null;
    }

}
