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
            // dd($user);
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

                //update access_by
                $financialYear = Helper::getCurrentFy();
                if($financialYear && $financialYear->access_by  == null){
                    $employees = Helper::getOrgWiseUserAndEmployees($organizationId);

                    $accessBy = [];

                    foreach ($employees as $employee) {
                        $authUser = $employee->authUser();
                        if ($authUser) {
                            $accessBy[] = [
                                'user_id' => $authUser->id,
                                'authenticable_type' => $authUser->authenticable_type?? null,
                                'authorized' => true,
                            ];
                        }
                    }
                    $financialYear->access_by = $accessBy;
                    $financialYear->save();
                }

                // Pass organization id and mappings
                $view->with([
                    'authSessionUser' => $user,
                    'menues' => $menues,
                    'organizations' => $mappings,
                    'organization_id' => $organizationId,
                    'orgLogo' => $orgLogo,
                    'logedinUser'=> $user,
                    'fyears' => $fyears,
                    'c_fyear' => $c_fyear['range']
                ]);
            }
        });
    }
}
