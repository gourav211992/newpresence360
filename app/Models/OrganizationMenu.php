<?php

// app/Models/OrganizationMenu.php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\Helper;

class OrganizationMenu extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg;
    protected $table = 'organizations_menu';
    protected $appends = ['menu_link'];
    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'menu_id',
        'service_id',
        'name',
        'alias',
        'parent_id',
        'sequence',
        'created_by',
        'updated_by',
        'deleted_by',
    ];



    public function generateLink($user) {

        $baseUrl = '/';

        $baseUrls = [
            'app' => env("APP_PORTAL_URL", 'https://app.thepresence360.com'),
            'erp' => env("ERP_URL", 'https://erp.thepresence360.com'),
            'hrms_member' => env("HRMS_URL", 'https://login.thepresence360.com'),
            'hrms' => env("HRMS_URL", 'https://login.thepresence360.com'),
            'portal' => env("PORTAL_URL", 'https://portal.thepresence360.com'),
            'web' => env('WEB_URL', 'https://web.thepresence360.com'),
            'auth' => env('AUTH_URL', 'https://auth.thepresence360.com'),
            'onboarding' => env('ONBOARDING_URL', 'https://onboarding.thepresence360.com'),
            'admin' => env('ADMIN_URL', 'https://admin.thepresence360.com'),
            'root' => env('ROOT_URL', 'https://root.thepresence360.com'),
            'attendance' => env('ATTENDNACE_URL', 'https://attendance.thepresence360.com'),
            'leave' => env('LEAVE_URL', 'https://leave.thepresence360.com')
        ];
        
        $serviceGroupAlias = @$this->menu->serviceGroup->alias;
        if (isset($baseUrls[$serviceGroupAlias])) {

            $baseUrl = $baseUrls[$serviceGroupAlias];
            if($serviceGroupAlias == 'hrms') {
                $baseUrl = $baseUrls[$serviceGroupAlias]. '/'. $user->organization->alias;
            }

            if($serviceGroupAlias == 'hrms_member') {
                $baseUrl = $baseUrls[$serviceGroupAlias] . '/'. 'member/' . $user->organization->alias;
            }
        }

        $alias = str_replace('_', '/', $this->alias);

        return $baseUrl. '/'. $alias;
    }

    public function getMenuLinkAttribute()
    {
        return str_replace('_', '/', $this->alias);
    }
    public function group()
    {
        return $this->belongsTo(OrganizationGroup::class, 'group_id', 'id');
    }
    public function service()
    {
        return $this->belongsTo(Services::class, 'service_id');
    }
    
    public function menu()
    {
        return $this->belongsTo(ServiceMenu::class, 'menu_id');
    }

    public function serviceMenu()
    {
        return $this->belongsTo(ServiceMenu::class, 'menu_id');
    }
    // public function childMenus1()
    // {
    //     return $this->hasMany(OrganizationMenu::class, 'parent_id', 'menu_id')->with('childMenus');
    // }

    public function childMenus()
    {
        return $this->hasMany(OrganizationMenu::class, 'parent_id', 'menu_id');
                   // ->where('group_id', Helper::getAuthenticatedUser()->organization->group_id);
    }
}
