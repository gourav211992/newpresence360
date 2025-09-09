<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use App\Models\User;
use P360\Core\Models\AuthUser;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PeterPetrus\Auth\PassportToken;
use Illuminate\Support\Facades\Auth;
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
        // $authUser = AuthUser::find(5);
        // Auth::guard('web')->login(User::find(2));
        // auth() -> user() -> authenticable_type = $authUser->authenticable_type;
        // auth() -> user() -> auth_user_id = $authUser->id;


        $returnUrl = $request->fullUrl();

        $authUrl = env("AUTH_URL", "/") . 'login?' . http_build_query([
            'return_url' => $request->fullUrl(),
        ]);

        $authType = @$_COOKIE['sso_auth'];
        $token = @$_COOKIE['sso_token'];

        if (!$token) {
            return redirect($authUrl);
        }
        if (!empty($authType)) {

            return $this->newAuth($request, $token) ? $next($request) : redirect($authUrl);
        }

        $row = explode("|", urldecode($token));

        if (!empty($row[0])) {
            $tokenRow = PassportToken::dirtyDecode($row[0]);
        }

        if (!empty($row[1])) {
            Session::put('organization_id', $row[1]);
        }

        // $dbName = env('DB_DATABASE');
        $dbName = 'staqo_presence';
        if (!empty($row[2])) {
            $dbName = $row[2];
        }
        Session::put('DB_DATABASE', $dbName);
        config(['database.connections.mysql.database' => $dbName]);
        DB::reconnect('mysql');

        if (!empty($row[3])) {
            $authType = $row[3];
        }

        $user = null;

        if (!empty($authType) && !empty($tokenRow['user_id'])) {
            if ($authType == 'auth-0') {
                $authType = 'user';
                $user = User::find($tokenRow['user_id']);
                // Auth::guard('web')->login($user);
            } else if ($authType == 'auth-1') {
                $authType = 'employee';
                $user = Employee::find($tokenRow['user_id']);
                // Auth::guard('web2')->login($user);
            }

            $request->merge(['auth_type' => $authType]);
        } else {

            return redirect($authUrl);
        }

        $authUser = AuthUser::where('authenticable_type', '=', $authType)
            ->where('authenticable_id', '=', $user->id)
            ->where('db_name', '=', $dbName)
            ->first();

        if (!$authUser) {
            $user->auth_user_id = $authUser->id;
        }
        $user->authenticable_type = $authUser->authenticable_type;
        $user->auth_type = $authType;
        $user->db_name = $dbName;

        $request->merge(['db_name' => $dbName]);
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }

    public function newAuth($request, $token)
    {

        $tokenRow = PassportToken::dirtyDecode($token);

        $dbName = @$_COOKIE['sso_instance'];
        if ($dbName) {
            config(['database.connections.mysql.database' => $dbName]);
            DB::reconnect('mysql');
        }

        $authType = @$_COOKIE['sso_auth'];
        if (!empty($authType) && !empty($tokenRow['user_id'])) {
            $authUser = AuthUser::find($tokenRow['user_id']);
            if (!$authUser) {
                return false;
            }
            $authUser->auth_user_id = $authUser->id;

            $request->setUserResolver(fn() => $authUser);
        } else {
            return false;
        }

        return true;
    }
}
