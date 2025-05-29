<?php

namespace App\Http\Controllers;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\ServiceParametersHelper;
use App\Http\Requests\ServiceRequest;
use App\Models\Organization;
use App\Models\OrganizationService;
use App\Models\OrganizationServiceParameter;
use App\Models\Service;
use App\Models\ServiceParameter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index()
    {
        //Get all Services (Need to add pagination)
        $services = Service::get();
        return view('service.index', ['services' => $services]);
    }

    public function edit(Request $request, String $id)
    {
        $service = Service::find($request -> id);
        if (!isset($service)) {
            return redirect() -> route('admin.services.index') -> with('Warning', 'Service Not Found');
        }
        //Return the Service parameters from Helper in a formatted way
        $serviceAlias = $service -> alias;
        $parameters = ServiceParametersHelper::getDefinedServiceLevelParameters($serviceAlias);
        $financialService = ServiceParametersHelper::getFinancialService($serviceAlias);
        return view('service.create_edit', ['service' => $service, 'parameters' => $parameters, 'financialService' => $financialService]);
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            $service = Service::find($request -> service_id);
            if (!isset($service)) {
                return response() -> json([
                    'message' => 'Service Not Found',
                ], 404);
            }
            $user = Helper::getAuthenticatedUser();
            //First, Set the data in Service parameter table
            $serviceAlias = $service -> alias;
            //Update Financial Service
            $service -> financial_service_alias = ServiceParametersHelper::getFinancialServiceAlias($serviceAlias);
            $service -> type = isset(ConstantHelper::ERP_SERVICE_ALIAS_TYPE[$serviceAlias]) ? ConstantHelper::ERP_SERVICE_ALIAS_TYPE[$serviceAlias] : ConstantHelper::ERP_TRANSACTION_SERVICE_TYPE;
            $service -> save();
            //Get parameters from Constant Array
            $parameters = ServiceParametersHelper::getDefinedServiceLevelParameters($serviceAlias);
            //Ids array to keep track of inserted parameters
            $insertedServiceParamIds = [];
            foreach ($parameters as $paramIndex => $paramValue) {
                //Set Default value if present else, default it to constant default value
                $defaultVal = (isset($request -> params) && isset($request -> params[$paramIndex])) 
                ? $request -> params[$paramIndex] : $paramValue['default_value'];
                //Create or Update the Parameters (UPDATE ONLY DEFAULT VALUE / STATUS)
                $serviceParam = ServiceParameter::updateOrCreate(
                    ['service_id' => $service -> id, 'name' => $paramValue['name']],
                    ['applicable_values' => $paramValue['applicable_values_database'], 'default_value' => $defaultVal, 'type' => $paramValue['type'], 'status' => ConstantHelper::ACTIVE],
                );
                //Push the newly created or updated service parameters ID
                array_push($insertedServiceParamIds, $serviceParam -> id);
            }
            //Delete the parameters which are not required now
            ServiceParameter::where('service_id', $service -> id) -> whereNotIn('id', $insertedServiceParamIds) -> delete();
            //Assign Service Parameters to each organization with registered service
            $assignedOrgForServices = OrganizationService::select('organization_id', 'service_id') -> where('service_id', $service -> id) -> get();
            if ($assignedOrgForServices -> count() == 0) {
                ServiceParametersHelper::enableServiceParametersForOrganization($service -> id, $user -> organization_id);
            } else {
                foreach ($assignedOrgForServices as $assignedOrgService) {
                    ServiceParametersHelper::enableServiceParametersForOrganization($assignedOrgService -> service_id, $assignedOrgService -> organization_id);
                }
            }
            DB::commit();
            return response() -> json([
                'status' => 'success',
                'message' => 'Service Updated successfully'
            ]);
        } catch(Exception $ex) {
            DB::rollBack();
            return response() -> json([
                'message' => 'Some internal error occured',
                'error' => $ex -> getMessage()
            ], 500);
        }
    }
}
