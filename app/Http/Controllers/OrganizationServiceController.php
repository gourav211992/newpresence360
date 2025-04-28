<?php

namespace App\Http\Controllers;

use App\Helpers\ConstantHelper;
use App\Helpers\ServiceParametersHelper;
use App\Models\OrganizationService;
use App\Models\OrganizationServiceParameter;
use App\Models\Service;
use DB;
use Illuminate\Http\Request;

class OrganizationServiceController extends Controller
{
    public function index(Request $request)
    {
        $orgServices = OrganizationService::withDefaultGroupCompanyOrg()-> get();      
        return view('organizationService.index', ['services' => $orgServices]);
    }

    public function edit(Request $request, String $id)
    {
        $organizationService = OrganizationService::with('parameters') -> find($id);
        if (isset($organizationService)) {
            foreach ($organizationService -> parameters as &$orgParam) {
                // if ($orgParam -> parameter_name === ServiceParametersHelper::REFERENCE_FROM_SERVICE_PARAM) {
                //     $paramArray = [];
                //     foreach ($orgParam -> service_parameter -> applicable_values as $paramValue) {
                //         if ($paramValue == 0) {
                //             array_push($paramArray, [
                //                 'value' => 0,
                //                 'label' => 'Direct'
                //             ]);
                //         } else {
                //             $service = Service::find($paramValue);
                //             if (isset($service)) {
                //                 array_push($paramArray, [
                //                     'value' => $paramValue,
                //                     'label' => $service -> name
                //                 ]);
                //             }
                //         }
                //     }
                //     $orgParam -> param_array = $paramArray;
                //     $orgParam -> is_multiple = true;
                // } else {
                //     $formattedValues = [];
                //     foreach ($orgParam -> service_parameter -> applicable_values as $appValue) {
                //         array_push($formattedValues, [
                //             'label' => ucfirst($appValue),
                //             'value' => $appValue
                //         ]);
                //     }
                //     $orgParam -> param_array = $formattedValues;
                //     $orgParam -> is_multiple = false;
                // }

                $formattedValues = [];
                foreach ($orgParam -> service_parameter -> applicable_values as $appValue) {
                    array_push($formattedValues, [
                        'label' => ucfirst($appValue),
                        'value' => $appValue
                    ]);
                }
                $orgParam -> param_array = $formattedValues;
                $orgParam -> is_multiple = $orgParam -> parameter_name === ServiceParametersHelper::REFERENCE_FROM_SERIES_PARAM ? true : false;
            }
            return view('organizationService.edit', ['service' => $organizationService]);
        } else {
            return redirect() -> back() -> with('error', 'Service not found');
        }
    }
    public function update(Request $request, String $id)
    {
        $request->validate([
            'params' => 'array',
            'param_ids' => 'array',
            'param_names' => 'array',
        ]);

        DB::beginTransaction();
        $orgService = OrganizationService::find($id);
        if (isset($orgService)) {
            if (isset($request -> params) && isset($request -> param_names) && isset($request -> param_ids)) {
                foreach ($request -> params as $orgServiceParamKey => $orgServiceParam) {
                    $existingServiceParam = OrganizationServiceParameter::where('parameter_name', $request -> param_names[$orgServiceParamKey]) -> where('service_id', $orgService -> service_id) -> first();
                    if (isset($existingServiceParam)) {
                        $existingServiceParam -> parameter_value = $orgServiceParam;
                        $existingServiceParam -> save();
                    } else {
                        OrganizationServiceParameter::create([
                            'group_id' => $orgService -> group_id,
                            'company_id' => null, //Need to change
                            'organization_id' => null, //Need to change
                            'service_id' => $orgService -> service_id,
                            'service_param_id' => $request -> param_ids[$orgServiceParamKey],
                            'parameter_name' => $request -> param_names[$orgServiceParamKey],
                            'parameter_value' => $orgServiceParam,
                            'status' => ConstantHelper::ACTIVE,
                        ]);
                    }
                }
            }
            DB::commit();
            return redirect()->route('org-services.index')->with('success', 'Service updated successfully');
        } else {
            DB::rollBack();
            return redirect() -> back() -> with('error', 'Service not foound');
        }
    }
}
