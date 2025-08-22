<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;

class ErpDriver extends Model
{
    use HasFactory,DefaultGroupCompanyOrg;

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'user_id',
        'name',
        'email',
        'mobile_no',
        'experience_years',
        'license_no',
        'license_expiry_date',
        'license_front',
        'license_back',
        'id_proof_front',
        'id_proof_back',
        'status',
        'created_by',
        'updated_by',
    ];

    public function auth_user()
    {
        return $this->belongsTo(AuthUser::class, 'created_by', 'id');
    }

    public function employee(){

        return $this->belongsTo(Employee::class,'user_id');
    }

    public function licenseFrontMedia()
{
    return $this->hasOne(ErpDriverMedia::class, 'id', 'license_front');
}

    public function licenseBackMedia()
    {
        return $this->hasOne(ErpDriverMedia::class, 'id', 'license_back');
    }

    public function idProofFrontMedia()
    {
        return $this->hasOne(ErpDriverMedia::class, 'id', 'id_proof_front');
    }

    public function idProofBackMedia()
    {
        return $this->hasOne(ErpDriverMedia::class, 'id', 'id_proof_back');
    }


}
