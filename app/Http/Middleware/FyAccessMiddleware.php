<?php

namespace App\Http\Middleware;

use App\Helpers\Helper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FyAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Helper::getAuthenticatedUser();
         if ($user && !session()->has('fy_access_updated')) {
            Helper::updateFinancialYearAccessBy($user);
            session(['fy_access_updated' => true]);
        }
        return $next($request);
    }
}
