<?php

namespace App\Http\Middleware;

use DB;
use Closure;
use App\Models\AuthUser;
use PeterPetrus\Auth\PassportToken;
class SsoAuthApi
{

	/**
	 * The SSO Auth API implementation.
	 * @author Ashish Chauhan.
	 * @var PassportToken
	 */



	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{

        $token = request()->bearerToken();
		if (!$token) {
			return response()->json(['message' => 'Auth token not found.'], 401);
		}

		$tokenRow = PassportToken::dirtyDecode($token);

		if (empty($tokenRow) || empty($tokenRow['user_id'])) {
			return response()->json(['message' => 'Invalid auth token.'], 401);
		}

		$authUser = AuthUser::find($tokenRow['user_id']);
		if (!$authUser) {
			return response()->json(['message' => 'Invalid auth user token.'], 401);
		}
		$dbName = $authUser->db_name;
		$authType = $authUser->user_type;

		if ($authUser->db_name) {
			config(['database.connections.mysql.database' => $dbName]);
			$connect = DB::reconnect('mysql');
		}

		$user = $authUser->authUser();
		if (!$user) {
			return response()->json(['message' => 'Invalid user token.'], 401);
		}
		$user->auth_user_id = $authUser->id;
		$user->authenticable_type = $authUser->authenticable_type;
		$user->auth_type = $authType;
		$user->db_name = $dbName;

		// $request->merge(['db_name' => $dbName]);
        $request->setUserResolver(fn() => $user);


		return $next($request);
	}

}
