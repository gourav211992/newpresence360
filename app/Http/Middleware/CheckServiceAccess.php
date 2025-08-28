<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\Helper;
use Illuminate\Http\Request;

class CheckServiceAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $parentUrl = $request->segment(1);
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);

        if (!isset($servicesBooks['services']) || count($servicesBooks['services']) === 0) {
            return response()->view('errors.no-service-access', [
                'parentUrl' => $parentUrl,
            ], 403);
        }

        return $next($request);
    }
}
