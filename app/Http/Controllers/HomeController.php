<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttributeRequest;
use App\Models\AttributeGroup;
use App\Models\Attribute;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use Auth;

class HomeController extends Controller
{

    public function index(Request $request)
    {
        return view('dashboard');
    }
    
}
