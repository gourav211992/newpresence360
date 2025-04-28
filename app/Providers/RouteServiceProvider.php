<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            //return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware(['SwitchDatabaseConnection','web'])
                ->group(base_path('routes/web.php'));

            Route::middleware(['web'])
                ->prefix('crm')
                ->namespace('App\Http\Controllers\CRM')
                ->group(base_path('routes/crm.php'));

            Route::middleware(['web'])
                ->prefix('supplier')
                ->namespace('App\Http\Controllers\Supplier')
                ->group(base_path('routes/supplier.php'));

            Route::middleware(['web'])
                ->prefix('recruitment')
                ->namespace('App\Http\Controllers\Recruitment')
                ->group(base_path('routes/recruitment.php'));
        });
    }
}
