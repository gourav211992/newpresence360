<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\AuthUser;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PeterPetrus\Auth\PassportToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class UserAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
{
    if (app()->environment('local')) {
        $authUser = AuthUser::find(1);
        Auth::guard('web')->login(Employee::find(1));
        auth()->user()->authenticable_type = $authUser->authenticable_type;
        auth()->user()->auth_user_id = $authUser->id;
        $request->merge(['auth_type' => 'employee']);
        $request->setUserResolver(fn() => auth()->user());

        return $next($request);
    }

    $returnUrl = $request->fullUrl();
    $authUrl = env("AUTH_URL", "/") . 'login?' . http_build_query(['return_url' => $returnUrl]);

    $authType = @$_COOKIE['sso_auth'];
    $token = @$_COOKIE['sso_token'];

    if (!$token) return redirect($authUrl);

    if (!empty($authType)) {
        if (!$this->newAuth($request, $token)) return redirect($authUrl);
        return $next($request);
    }

    // fallback SSO handling via token split
    $row = explode("|", urldecode($token));
    $tokenRow = !empty($row[0]) ? PassportToken::dirtyDecode($row[0]) : null;

    if (!isset($tokenRow['user_id'])) return redirect($authUrl);

    $dbName = $row[2] ?? 'staqo_presence';
    Session::put('DB_DATABASE', $dbName);
    config(['database.connections.mysql.database' => $dbName]);
    DB::reconnect('mysql');

    $authType = $row[3] ?? 'user';
    $user = $authType === 'auth-1'
        ? Employee::find($tokenRow['user_id'])
        : User::find($tokenRow['user_id']);

    if (!$user) return redirect($authUrl);

    $guard = $authType === 'auth-1' ? 'web2' : 'web';
    Auth::guard($guard)->login($user);

    $request->merge(['auth_type' => $authType]);

    $authUser = AuthUser::where('authenticable_type', '=', $authType)
        ->where('authenticable_id', '=', $user->id)
        ->where('db_name', '=', $dbName)
        ->first();

    if ($authUser) {
        $user->auth_user_id = $authUser->id;
        $user->authenticable_type = $authUser->authenticable_type;
    }

    $user->auth_type = $authType;
    $user->db_name = $dbName;
    $request->merge(['db_name' => $dbName]);
    $request->setUserResolver(fn() => $user);

    return $next($request);
}


    public function newAuth($request, $token) {

        $tokenRow = PassportToken::dirtyDecode($token);

        $dbName = @$_COOKIE['sso_instance'];
        if ($dbName) {
            Session::put('DB_DATABASE', $dbName);
            config(['database.connections.mysql.database' => $dbName]);
            DB::reconnect('mysql');
        }

        $authType = @$_COOKIE['sso_auth'];
        if (!empty($authType) && !empty($tokenRow['user_id'])) {
            $authUser = AuthUser::find($tokenRow['user_id']);


            if(in_array($authType, array('IAM-SUPER', 'IAM-ADMIN', 'IAM-ROOT'))) {
                $authType = 'user';
                $user = User::find($authUser->authenticable_id);
                $user->auth_user_id = $authUser->id;
                $user->authenticable_type = $authUser->authenticable_type;
                $user->auth_type = $authType;
                $user->db_name = $dbName;
                Auth::guard('web')->login($user);
            }else {
                $authType = 'employee';
                $user = Employee::find($authUser->authenticable_id);
                $user->auth_user_id = $authUser->id;
                $user->authenticable_type = $authUser->authenticable_type;
                $user->auth_type = $authType;
                $user->db_name = $dbName;
                Auth::guard('web2')->login($user);
            }

            $request->merge(['auth_type' => $authType]);
            $request->setUserResolver(fn() => $user);
    }
    else {
            return false;
    }

        $request->merge(['db_name' => $dbName]);

        return true;
    }
}