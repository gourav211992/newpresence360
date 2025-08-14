<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public static function authUserId(){
        $user = Helper::getAuthenticatedUser();
        return $user->id ?? 1;
    }
}
