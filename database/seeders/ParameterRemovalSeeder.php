<?php

namespace Database\Seeders;

use App\Helpers\ServiceParametersHelper;
use App\Models\OrganizationBookParameter;
use App\Models\OrganizationServiceParameter;
use App\Models\ServiceParameter;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParameterRemovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            //Removal of GL POSTING PARAM which were saved as common parameters from parameters table
            OrganizationBookParameter::whereIn('parameter_name', 
            [ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM, ServiceParametersHelper::POST_ON_ARROVE_PARAM, ServiceParametersHelper::GL_POSTING_SERIES_PARAM, ServiceParametersHelper::GL_SEPERATE_DISCOUNT_PARAM]
            ) -> where('type', ServiceParametersHelper::COMMON_PARAMETERS) -> delete();
            OrganizationServiceParameter::whereIn('parameter_name', 
            [ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM, ServiceParametersHelper::POST_ON_ARROVE_PARAM, ServiceParametersHelper::GL_POSTING_SERIES_PARAM, ServiceParametersHelper::GL_SEPERATE_DISCOUNT_PARAM]
            ) -> where('type', ServiceParametersHelper::COMMON_PARAMETERS) -> delete();
            ServiceParameter::whereIn('name', 
            [ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM, ServiceParametersHelper::POST_ON_ARROVE_PARAM, ServiceParametersHelper::GL_POSTING_SERIES_PARAM, ServiceParametersHelper::GL_SEPERATE_DISCOUNT_PARAM]
            ) -> where('type', ServiceParametersHelper::COMMON_PARAMETERS) -> delete();
            
            DB::commit();
        } catch(Exception $ex) {
            DB::rollBack();
            dd("Error :-> " . $ex -> getMessage());
        }
    }
}
