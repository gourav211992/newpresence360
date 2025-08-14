<?php

// User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'organization_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // protected $appends = [
    //     'authenticable_type',
    //     'auth_user_id'
    // ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function organizations() {
        return $this->belongsToMany(Organization::class, 'user_organization_mapping');
    }
    public function access_rights_org()
    {
        return $this -> hasMany(UserOrganizationMapping::class);
    }

    public function userRole()
    {
        return $this->hasOne(RoleUser::class);
    }

    public function vouchers()
    {
        return $this->morphMany(Voucher::class, 'voucherable');
    }

    public function loanApplicationLogs()
    {
        return $this->hasOne(LoanApplicationLog::class, 'user_id');
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

    public function roles()
    {

       return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id');
        // return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    public function hasPermission($permission)
    {
        // Super admin bypass
        if (strtolower($this->user_type) === strtolower('IAM-SUPER')) {
            return true;
        }
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('alias', $permission)) {
                return true;
            }
        }
        return false;
    }

    public function groupOrganizationsIds()
    {
        if (isset($this->organization->group->organizations)) {
            return $this->organization->group->organizations->pluck('id')->toArray();

        } else {
            return [$this->organization->id];
        }

    }

    public function groupCompaniesIds()
    {
        if (isset($this->organization->group->companies)) {
            return $this->organization->group->companies->pluck('id')->toArray();

        } else {
            return [$this->organization->id];
        }
    }

    public function auth_user()
    {
        return $this -> belongsTo(AuthUser::class, 'id', 'authenticable_id');
    }
    // public function getAuthenticableTypeAttribute()
    // {
    //     return $this -> auth_user -> authenticable_type;
    // }

    // public function getAuthUserIdAttribute()
    // {
    //     return $this -> auth_user -> id;
    // }
    public function stakeholderInteractions()
    {
        return $this->morphMany(StakeholderInteraction::class, 'userable');
    }
}
