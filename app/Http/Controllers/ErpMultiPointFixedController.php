<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuthUser;
use App\Helpers\Helper; 
use App\Http\Requests\MultiFixedPricingRequest;
use App\Models\ErpVehicleType;
use App\Helpers\ConstantHelper;
use App\Models\Customer;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\ErpLogisticsMultiFixedLocation;
use App\Models\ErpLogisticsMultiFixedPricing;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\Organization;

class ErpMultiPointFixedController extends Controller
{
    public function create(){
        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        $organization = Organization::with('addresses')->find($organizationId);
        $countryId = optional($organization->addresses->first())->country_id;
        $states = State::where('country_id',$countryId)->get();
        $customers = Customer::withDefaultGroupCompanyOrg()->get();
        $vehicleTypes = ErpVehicleType::withDefaultGroupCompanyOrg()->get();

        return view('multi-point-pricing.fixed.create', compact('states','customers', 'vehicleTypes'));
    }
}
