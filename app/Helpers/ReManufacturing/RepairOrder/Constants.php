<?php
namespace App\Helpers\ReManufacturing\RepairOrder;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\RGR\Constants as RGRConstants;
//Repair Order Service
class Constants
{
    const SERVICE_ALIAS = "rep";
    const SERVICE_NAME = "Repair Order";
    const PARAMETERS = [
       [
            "name" => ServiceParametersHelper::REFERENCE_FROM_SERVICE_PARAM, 
            "applicable_values" => [RGRConstants::SERVICE_ALIAS], 
            "default_value" => [RGRConstants::SERVICE_ALIAS], 
            'is_multiple' => true,
            'service_level_visibility' => true
        ],
        [
            "name" => ServiceParametersHelper::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => ServiceParametersHelper::BACK_DATE_ALLOW_PARAM,
            "applicable_values" =>ServiceParametersHelper::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => ServiceParametersHelper::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => ServiceParametersHelper::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
    ];
}
