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
use Illuminate\Support\Facades\URL;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;


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
        // \DB::listen(function ($query) {
        //     \Log::debug('SQL: ' . $query->sql);
        //     \Log::debug('Bindings: ' . json_encode($query->bindings));
        //     \Log::debug('Time: ' . $query->time . ' ms');
        // });

        View::composer('*', function ($view) {

            $user = request()->user();
            // $user = Helper::getAuthenticatedUser();
            // $profileUser = $user->authUser();

            if ($user) {
                $user = Helper::getAuthenticatedUser();
                $organizationId = $user->organization_id;
                // Fetch organization menus based on services
                $menues = [];

                // Fetch user organization mappings
                $mappings = $user -> access_rights_org;

                // Fetch Organization Logo
                $orgLogo = Helper::getOrganizationLogo($organizationId);

                //financialyears
                $c_fyear = "";
                $fyears = Helper::getFinancialYears();
                if($fyears!=null)
                $c_fyear = Helper::getFinancialYear(date('Y-m-d'));


                // Pass organization id and mappings
                $view->with([
                    'authSessionUser' => $user,
                    'menues' => $menues,
                    'organizations' => $mappings ,
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
