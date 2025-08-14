<?php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\UserStampTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ErpMachine extends Model
{
    protected $table = 'erp_machines';
    use HasFactory,DefaultGroupCompanyOrg,UserStampTrait;
    protected $fillable=["name","attribute_group_id","production_route_id","status","created_by","updated_by","group_id","company_id","organization_id"];

    public function details()
    {
        return $this->hasMany(ErpMachineDetail::class, 'machine_id');
    }

    public function attribute_group()
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }

    public function val_name()
    {
        $machine_id = $this->id;
        $details = ErpMachineDetail::where('machine_id', $machine_id)->get();
        $size = $details->pluck('attribute_value')->toArray();
        $attribute_group_names = implode(',', $size);
        return $attribute_group_names;
    }



}
