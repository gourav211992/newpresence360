<?php

// namespace App\Http\Middleware;

// use Closure;
// use Session;
// use App\Models\User;
// use App\Models\Employee;
// use App\Models\AuthUser;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use PeterPetrus\Auth\PassportToken;
// use Illuminate\Support\Facades\Auth;
// use Symfony\Component\HttpFoundation\Response;

// class UserAuthenticate
// {
//     /**
//      * Handle an incoming request.
//      *
//      * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
//      */
//     public function handle(Request $request, Closure $next): Response
//     {
//         $authUser = AuthUser::find(7);
//         Auth::guard('web')->login(User::find(1));
//         auth() -> user() -> authenticable_type = $authUser->authenticable_type;
//         auth() -> user() -> auth_user_id = $authUser->id;

//         $token = @$_COOKIE['sso_token'];

//         if ($token) {
//             $row = explode("|", urldecode($token));

//             if (!empty($row[0])) {
//                 $tokenRow = PassportToken::dirtyDecode($row[0]);
//             }

//             if (!empty($row[1])) {
//                 Session::put('organization_id', $row[1]);
//             }

//             $dbName = env('DB_DATABASE');
//             if (!empty($row[2])) {
//                 $dbName = $row[2];
//                 Session::put('DB_DATABASE', $dbName);
//                 config(['database.connections.mysql.database' => $dbName]);
//                 DB::reconnect('mysql');
//             }

//             if (!empty($row[3])) {
//                 $authType = $row[3];
//             }

//             if (!empty($authType) && !empty($tokenRow['user_id'])) {
//                 if ($authType == 'auth-0') {
//                     $authType = 'user';
//                     Auth::guard('web')->login(User::find($tokenRow['user_id']));
//                 } else if ($authType == 'auth-1') {
//                     $authType = 'employee';
//                     Auth::guard('web2')->login(Employee::find($tokenRow['user_id']));
//                 }

//                 $request->merge(['auth_type' => $authType]);
//             }

//             $request->merge(['db_name' => $dbName]);

//         } else {

//             // if ($_SERVER['SERVER_NAME'] === 'erp.thepresence360.com') {
//             //     return redirect('https://login.thepresence360.com');
//             // }
//             // // Hardcoded user login for user
//             $user = User::find(1);
//             // if ($user) {
//             //     Auth::guard('web')->login($user);
//             //     Auth::guard('web2')->logout();
//             //     $request->merge(['auth_type' => 'user', 'db_name' => env('DB_DATABASE')]);
//           // $user = Employee::find(1);
//          //$user = Employee::find(1);
//             if ($user) {
//                 Auth::guard('web2')->login($user);
//                 Auth::guard('web')->logout();
//                 $request->merge(['auth_type' => 'user', 'db_name' => env('DB_DATABASE')]);
//             } else {
//                 return redirect(env('MAIN_APP_URL', 'http://143.110.243.104/'));
//             }

//             // // Hardcoded user login for employee
//         }

//         return $next($request);
//     }
// }



// namespace App\Http\Middleware;

// use Closure;
// use Session;
// use App\Models\User;
// use App\Models\Employee;
// use App\Models\AuthUser;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use PeterPetrus\Auth\PassportToken;
// use Illuminate\Support\Facades\Auth;
// use Symfony\Component\HttpFoundation\Response;

// class UserAuthenticate
// {
//     /**
//      * Handle an incoming request.
//      *
//      * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
//      */
//     public function handle(Request $request, Closure $next): Response
//     {
//         $authUser = AuthUser::find(7);
//         Auth::guard('web')->login(User::find(1));
//         auth() -> user() -> authenticable_type = $authUser->authenticable_type;
//         auth() -> user() -> auth_user_id = $authUser->id;

//         $token = @$_COOKIE['sso_token'];

//         if ($token) {
//             $row = explode("|", urldecode($token));

//             if (!empty($row[0])) {
//                 $tokenRow = PassportToken::dirtyDecode($row[0]);
//             }

//             if (!empty($row[1])) {
//                 Session::put('organization_id', $row[1]);
//             }

//             $dbName = env('DB_DATABASE');
//             if (!empty($row[2])) {
//                 $dbName = $row[2];
//                 Session::put('DB_DATABASE', $dbName);
//                 config(['database.connections.mysql.database' => $dbName]);
//                 DB::reconnect('mysql');
//             }

//             if (!empty($row[3])) {
//                 $authType = $row[3];
//             }

//             if (!empty($authType) && !empty($tokenRow['user_id'])) {
//                 if ($authType == 'auth-0') {
//                     $authType = 'user';
//                     Auth::guard('web')->login(User::find($tokenRow['user_id']));
//                 } else if ($authType == 'auth-1') {
//                     $authType = 'employee';
//                     Auth::guard('web2')->login(Employee::find($tokenRow['user_id']));
//                 }

//                 $request->merge(['auth_type' => $authType]);
//             }

//             $request->merge(['db_name' => $dbName]);

//         } else {

//             // if ($_SERVER['SERVER_NAME'] === 'erp.thepresence360.com') {
//             //     return redirect('https://login.thepresence360.com');
//             // }
//             // // Hardcoded user login for user
//             $user = User::find(1);
//             // if ($user) {
//             //     Auth::guard('web')->login($user);
//             //     Auth::guard('web2')->logout();
//             //     $request->merge(['auth_type' => 'user', 'db_name' => env('DB_DATABASE')]);
//           // $user = Employee::find(1);
//          //$user = Employee::find(1);
//             if ($user) {
//                 Auth::guard('web2')->login($user);
//                 Auth::guard('web')->logout();
//                 $request->merge(['auth_type' => 'user', 'db_name' => env('DB_DATABASE')]);
//             } else {
//                 return redirect(env('MAIN_APP_URL', 'http://143.110.243.104/'));
//             }

//             // // Hardcoded user login for employee
//         }

//         return $next($request);
//     }
// }



namespace App\Http\Middleware;

use Closure;
use Session;
use App\Models\User;
use App\Models\AuthUser;
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
        $authUser = AuthUser::find(1);
        Auth::guard('web')->login(Employee::find(1));
        auth() -> user() -> authenticable_type = $authUser->authenticable_type;
        auth() -> user() -> auth_user_id = $authUser->id;
        $request->merge(['auth_type' => 'user']);
        $request->setUserResolver(fn() => auth() -> user());

        return $next($request);

	
	$returnUrl = $request->fullUrl();
        $authUrl = env("AUTH_URL", "/") . 'login?' . http_build_query([
            'return_url' => $request->fullUrl(),
	]);

        $authType = @$_COOKIE['sso_auth'];
        $token = @$_COOKIE['sso_token'];

        if (!$token) {
            return redirect($authUrl);
        }

        if (!empty($authType) ) {

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

        if (!empty($authType) && !empty($tokenRow['user_id'])) {
            if ($authType == 'auth-0') {
                $authType = 'user';
                Auth::guard('web')->login(User::find($tokenRow['user_id']));
            } else if ($authType == 'auth-1') {
                $authType = 'employee';
                Auth::guard('web2')->login(Employee::find($tokenRow['user_id']));
            }

            $request->merge(['auth_type' => $authType]);
	}
	else {

           return redirect($authUrl);
	}


        $request->merge(['db_name' => $dbName]);

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
                
                Auth::guard('web')->login($user);
            }else {
                $authType = 'employee';
                $user = Employee::find($authUser->authenticable_id);
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
