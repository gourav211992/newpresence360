<?php

namespace App\Http\Middleware;

use App\Models\AuthDevice;
use DB;
use Auth;
use Closure;
use Session;
use App\Models\AuthUser;
use Illuminate\Contracts\Auth\Guard;

class SwitchDatabaseConnection
{

	/**
	 * The Guard implementation.
	 * @author Deepak Kr.
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$authUser = null;

		$authData = $request->header('Authorization');

		if ($authData) {
			$row = explode(" ", $authData);
			if (isset($row[1]) && !empty($row[1])) {
				$authData = urldecode($row[1]);
				$row = explode("|", $authData);

				if (!empty($row[0])) {
					$token = $row[0];
					$request->headers->set('Authorization', "Bearer " . $token);

					if (!empty($row[1])) {
						Session::put('organization_id', $row[1]);
					}

					if (!empty($row[2])) {
						$dbName = $row[2];
						Session::put('DB_DATABASE', $dbName);
						config(['database.connections.mysql.database' => $dbName]);
						DB::reconnect('mysql');
					}

					if (!empty($row[3]) && in_array($row[3], ['auth-0', 'auth-1'])) {
						if ($row[3] == 'auth-0') {
							$request->request->set('auth_is', 'user');
						} else if ($row[3] == 'auth-1') {
							$request->request->set('auth_is', 'employee');
						}
					}
				}

				return $next($request);
			}
		}

		$dbName = config('database.connections.mysql.database');

		if (Session::get('DB_DATABASE')) {
			$dbName = Session::get('DB_DATABASE');
			config(['database.connections.mysql.database' => $dbName]);
			DB::reconnect('mysql');
			return $next($request);
		}


		$authUser = $this->getAuthUser($request);

		if ($authUser && $authUser->db_name) {
			$dbName = $authUser->db_name;
			Session::put('DB_DATABASE', $authUser->db_name);
			Session::put('ORG_ALIAS', $authUser->organization_alias);
		} else {
			$authDevice = $this->getAuthDevice($request);
			if ($authDevice && $authDevice->db_name) {
				$dbName = $authDevice->db_name;
				Session::put('DB_DATABASE', $authDevice->db_name);
				Session::put('ORG_ALIAS', $authDevice->organization_alias);
			}
		}
		// dd($authUser, $dbName);

		config(['database.connections.mysql.database' => $dbName]);
		$connect = DB::reconnect('mysql');
		return $next($request);
	}

	private function getAuthDevice($request)
	{
		if (!in_array($request->getPathInfo(), [
			'/api/DevHeartBeat',
			'/api/TaskInfo',
			'/api/TaskResult',
			'/api/createperson',
			'/api/deleteperson',
			'/api/ImgRegCallback',
			'/api/SetIdentify',
			'/api/setqridentify',
			'/api/calculate',
			'/api/v1/device-attendance',
			'/api/v1/validate-device',
			'/api/CardRegCallback'
		])) {
			return null;
		}

		$authDevice = null;
		if ($request->deviceKey ?: $request->serial_number) {
			$authDevice = AuthDevice::where('serial_number', $request->deviceKey ?: $request->serial_number)
				->first();
		}
		return $authDevice;
	}

	private function getAuthUser($request)
	{
		if (Auth::guard('web2')->check()) {
			return AuthUser::where('email', '=', Auth::guard('web2')->user()->email)->first();
		}

		if (Auth::guard('web')->check()) {
			return AuthUser::where('email', '=', Auth::guard('web')->user()->email)->first();
		}

		if (!in_array($request->getPathInfo(), [
			'/admin/login',
			'/login',
			'/login-otp',
			'/admin/login/otp',
			'/employee/password/reset',
			'/login/phone',
			'/login/otp',
			'/admin/login/authentication',
			'/admin/login/phone',
			'/admin/login/email-otp',
			'/employee-login',
			'/admin/login/validate-account',
			'/admin/login/forget-password',
			'/admin/login/send-otp',
			'/admin/login/otp-send',
			'/admin/login/otp-varify',
			'/admin/login/resetpassword',
			'/admin/login/password-update',
			'/employee/emailer',
			'/set-layout-cookie',
			'/api/v1/emaillogin',
			'/api/v1/admin-login',
			'/api/v1/mobilelogin',
			'/api/v1/kiosk-login',
			'/api/v1/kiosk-mobilelogin',
			'/api/v1/change-password',
			'/api/v1/generate-otp',
			'/api/v1/sendotp',
			'/api/v1/validate-otp',
			'/api/v2/loginauthentication',
			'/api/v2/login',
			'/api/v2/sendotp',

		])) {
			return null;
		}

		if ($request->username) {
			return AuthUser::where('email', '=', $request->username)
				->orWhere('mobile', '=', $request->username)
				->first();
		}

		if ($request->email) {
			return AuthUser::where('email', '=', $request->email)->first();
		}

		if ($request->mobile) {
			return AuthUser::where('mobile', '=', $request->mobile)->first();
		}

		if ($request->user) {
			return AuthUser::where('email', '=', $request->user)
				->orWhere('mobile', '=', $request->user)->first();
		}
		if ($request->phone) {
			return AuthUser::where('mobile', '=', $request->phone)->first();
		}

		if ($request->auth) {
			return AuthUser::where('email', '=', $request->auth)
				->orWhere('mobile', '=', $request->auth)
				->first();
		}

		return null;
	}
}
