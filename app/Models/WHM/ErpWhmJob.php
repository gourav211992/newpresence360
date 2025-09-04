<?php

namespace App\Models\WHM;

use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\Organization;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpWhmJob extends Model
{
    use HasFactory, DefaultGroupCompanyOrg;

    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'morphable_id',
        'morphable_type',
        'type',
        'status',
        'deviation_qty',
        'deviation_approved_by',
        'deviation_approved_at',
        'job_closed_at',
        'store_id',
        'sub_store_id',
        'trns_type',
        'reference_type',
        'reference_id',
        'reference_no',
    ];

    public function morphable()
    {
        return $this->morphTo();
    }

    public function itemUniqueCodes()
    {
        return $this->hasMany(ErpItemUniqueCode::class, 'job_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function subStore()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

}