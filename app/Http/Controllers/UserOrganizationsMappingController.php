<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserOrganizationMapping;
use Auth;

class UserOrganizationsMappingController extends Controller
{
    public function index()
    {
        $user = Auth::guard('web')->user();
        $mappings = UserOrganizationMapping::where("user_id", $user->id)::with('organization')->get();

        return response()->json($mappings);
    }

}
