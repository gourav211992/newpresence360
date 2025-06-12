<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AuthUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $connection = 'mysql_master';

    protected $fillable = [
        'organization_id', 
        'organization_name', 
        'organization_alias', 
        'email', 
        'mobile', 
        'db_name',
        'status',
    ];

    public function authUser() {
        if ($this->authenticable_type == 'employee') {
            return Employee::find($this->authenticable_id);
        } 

        return User::find($this->authenticable_id);
    }

    public function vendor_portal()
    {
        return $this->hasOne(VendorPortalUser::class,'user_id','id');
    }

    public function vendor_portals()
    {
        return $this->hasMany(VendorPortalUser::class,'user_id','id');
    }

    public function access_rights_org()
    {
        if ($this->authenticable_type == 'employee') {
            return $this -> hasMany(EmployeeOrganizationMapping::class, 'employee_id', 'authenticable_id');

        } 
        return $this -> hasMany(UserOrganizationMapping::class, 'user_id', 'authenticable_id');
    }

    public function scopeOrganizationWiseUsers($query, int $orgId)
    {
        $query-> whereHas('access_rights_org', function ($subQuery) use ($orgId) {
            $subQuery->where('organization_id', $orgId);
        })->orWhere('organization_id', $orgId)->whereNotIn('user_type', ['IAM-SUPER', 'IAM-ROOT']);
    }

    
    public function getInitials()
    {
        $nameParts = explode(' ', $this->name);
        $initials = '';

        foreach ($nameParts as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }

        return $initials;
    }


    // public function organization() {
    //     return $this->belongsTo(Organization::class);
    // }
}
