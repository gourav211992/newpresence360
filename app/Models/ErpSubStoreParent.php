<?php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpSubStoreParent extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg;

    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'sub_store_id',
        'store_id'
    ];

    public function store()
    {
        return $this -> belongsTo(ErpStore::class, 'store_id');
    }
    public function sub_store()
    {
        return $this -> belongsTo(ErpSubStore::class, 'sub_store_id');
    }
}
