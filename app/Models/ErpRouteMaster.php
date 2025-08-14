<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;

class ErpRouteMaster extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg;

    protected $table = 'erp_logistics_route_masters';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'name',
        'country_id',
        'state_id',
        'city_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];


    public function country()
    {
        return $this->belongsTo(State::class, 'country_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

   
}
