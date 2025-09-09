<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'employees';

    protected $fillable = [
        'device_person_id',
        'organization_id',
        'location_id',
        'sub_loc_id',
        'loc_type_id',
        'name',
        'email',
        'mobile',
        'father_name',
        'geo_address',
        'temporary_geo_address',
        'blood_group',
        'password',
        'employee_code',
        'employee_type_id',
        'designation_id',
        'department_id',
        'sub_department_id',
        'manager_id',
        'guarantor_id',
        'contractor_id',
        'valid_from',
        'valid_to',
        'workman_identity',
        'total_leaves',
        'face_id',
        'system_face_id',
        'gender',
        'dob',
        'status',
        'dial_code',
        'sos_call',
        'live_tracking',
        'check_in',
        'flexi_hours',
        'with_face',
        'fcm_token',
        'sms_setting',
        'email_setting',
        'push_setting',
        'premises',
        'shift_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'employee_type',
        'can_reset',
        'date_of_joining',
        'imei',
        'imei_enable',
        'remember_token',
        'workman_identity_type',
        'user_type',
        'employement_type',
        'marital_status',
        'date_exit',
        'transfer_date',
        'transferred_from',
        'device_sync',
        'free_location',
        'imagebase64code',
        'device_image',
        'card_no',
        'vip',
        'max_hour',
        'is_app_login',
        'is_attendance_access',
        'is_app_in_out',
        'pf_no',
        'uan_number',
        'aadhar_number',
        'name_as_per_aadhar',
        'nationality',
        'disability',
        'qualification',
        'passport_number',
        'passport_valid_from_date',
        'passport_valid_to_date',
        'esic_no',
        'bank_account_no',
        'bank_account_holder_name',
        'bank_name',
        'bank_ifsc',
        'mode_of_payment',
        'date_of_leaving',
        'no_of_child',
        'metrocity',
        'cityzen_type',
        'working_hour',
        'alternate_mobile',
        'parent_area_id',
        'area_id',
        'remarks',
        'blacklist_date',
        'contact_person_name',
        'contact_person_mobile',
        'contact_person_relation',
        'pan_no',
        'is_bus_allowed',
        'bus_id',
        'reason',
        'attendance_data_removal',
        'contact_person_dial_code',
        'disable_employee',
        'login_attempts',
        'last_login_attempt',
        'ip_number',
        'time_zone_id',
        'work_station_id',
        'category_id',
        'grade_id',
        'company_code_id',
        'company_pf_code_id',
        'reason_id',
        'inactivation_date',
        'configuration'
    ];

    // protected $appends = [
    //     'authenticable_type',
    //     'auth_user_id',
    //     'db_name'
    // ];

    public function areaMaster()
    {
        return $this->belongsTo(AreaMaster::class, 'organization_id', 'organization_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id', 'id');
    }

    public function vouchers()
    {
        return $this->morphMany(Voucher::class, 'voucherable');
    }

    /**
     * The channels the user receives notification broadcasts on.
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'users.'.$this->id;
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
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

    public function access_rights_org()
    {
        return $this -> hasMany(EmployeeOrganizationMapping::class);
    }
    public function teams()
    {
        return $this->hasOne(AssignTeam::class, 'team', 'id');
    }
    public function approver(){
        return $this->hasOne(ApprovalWorkflow::class, 'user_id');

    }


    public function roles()
    {
        return $this->belongsToMany(Role::class, 'employee_role', 'employee_id', 'role_id');
    }
    public function stores()
    {
        return $this->belongsToMany(ErpStore::class, 'erp_employee_stores', 'employee_id', 'location_id');
    }
    public function sub_stores()
    {
        return $this->belongsToMany(ErpSubStore::class, 'erp_employee_sub_stores', 'employee_id', 'location_id');
    }

    public function hasPermission($permission)
    {
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('alias', $permission)) {
                return true;
            }
        }
        return false;
    }
    public function sign()
    {
        return $this->hasOne(UserSignature::class, 'employee_id','id');
    }
    public function stakeholderInteractions()
    {
        return $this->morphMany(StakeholderInteraction::class, 'userable');
    }
    public function organizations() {

        return $this->belongsToMany(Organization::class, 'employee_organization_mapping', 'employee_id', 'organization_id');

    }

    public function auth_user()
    {
        return $this -> belongsTo(AuthUser::class, 'id', 'authenticable_id');
    }
    // public function getAuthenticableTypeAttribute()
    // {
    //     return $this -> auth_user -> authenticable_type ??null;
    // }

    // public function getAuthUserIdAttribute()
    // {
    //     return $this -> auth_user -> id??null;
    // }

    // public function getDbNameAttribute()
    // {
    //     return $this->auth_user->db_name ?? '';
    // }
    public function groupOrganizationsIds()
    {
        if (isset($this->organization->group->organizations)) {
            return $this->organization->group->organizations->pluck('id')->toArray();

        } else {
            return [$this->organization->id];
        }

    }
}

