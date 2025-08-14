<?php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSubStoreZone extends Model
{
    use HasFactory, Deletable, DefaultGroupCompanyOrg;

    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'store_id',
        'code',
        'name',
        'type',
        'status'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function sub_store()
    {
        return $this -> belongsTo(ErpSubStore::class, 'sub_store_id');
    }

    public function erp_store()
    {
        return $this -> belongsTo(ErpSubStore::class, 'sub_store_id');
    }
}
