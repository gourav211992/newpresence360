<?php

namespace App\Providers;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Models\ErpFinancialYear;
use App\Models\OrganizationMenu;
use App\Models\OrganizationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {

            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $organizationId = $user->organization_id;
                // Fetch organization menus based on services
                $menues = OrganizationMenu::with(['childMenus' => function($query) use ($user) {
                        $query->where('group_id', $user->organization->group_id);
                    }])
                    ->where('group_id', $user->organization->group_id)
                    ->whereNull('parent_id')
                    ->orderBy('sequence','ASC')->get();


                // Fetch user organization mappings
                $mappings = $user -> access_rights_org;

                // Fetch Organization Logo
                $orgLogo = Helper::getOrganizationLogo($organizationId);

                //financialyears
                $fyears = Helper::getFinancialYears();
                $c_fyear = Helper::getFinancialYear(date('Y-m-d'));
                // Pass organization id and mappings
                $view->with([
                    'authSessionUser' => $user,
                    'menues' => $menues,
                    'organizations' => $mappings,
                    'organization_id' => $organizationId,
                    'orgLogo' => $orgLogo,
                    'logedinUser'=> $user,
                    'fyears' => $fyears,
                    'c_fyear' => $c_fyear['range'] ?? ''
                ]);
            }
        });
    }
}
