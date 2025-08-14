<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;

class UserSignature extends Model
{
    use HasFactory, DefaultGroupCompanyOrg,Deletable;


    protected $table = 'erp_user_signatures';

    protected $fillable = [
        'name',
        'designation',
        'organization_id',
        'group_id',
        'employee_id',
        'company_id',
        'sign_upload_file',
        'created_by',
        'type',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function group()
    {
        return $this->belongsTo(OrganizationGroup::class);
    }

    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class);
    }
    public function user()
    {
        return $this->belongsTo(AuthUser::class,'employee_id');
    }
}
