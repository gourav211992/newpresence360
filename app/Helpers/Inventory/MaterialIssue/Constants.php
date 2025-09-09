<?php
namespace App\Helpers\Inventory\MaterialIssue;

use App\Helpers\ConstantHelper;
use App\Helpers\ServiceParametersHelper;

class Constants
{
    //SERVICE ALIAS
    public const SERVICE_ALIAS = "mi";
    public const SERVICE_NAME = "Material Issue";
    //SERVICE PARAMETERS
    const SERVICE_PARAMETERS = [
        [
            "name" => ServiceParametersHelper::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::MO_SERVICE_ALIAS, ConstantHelper::PI_SERVICE_ALIAS, 
                ConstantHelper::JO_SERVICE_ALIAS, ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::MO_SERVICE_ALIAS, ConstantHelper::PI_SERVICE_ALIAS,
                ConstantHelper::JO_SERVICE_ALIAS, ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
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
            "applicable_values" => ServiceParametersHelper::BACK_DATE_ALLOW_PARAM_VALUES,
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
        [
            "name" => ServiceParametersHelper::ISSUE_TYPE_PARAM,
            "applicable_values" => ServiceParametersHelper::ISSUE_TYPE_VALUES,
            "default_value" => ServiceParametersHelper::ISSUE_TYPE_VALUES,
            'is_multiple' => true,
            'service_level_visibility' => true
        ],
        [
            "name" => ServiceParametersHelper::REQUESTER_TYPE_PARAM,
            "applicable_values" => ServiceParametersHelper::REQUESTER_TYPE_VALUES,
            "default_value" => ['Department'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
    ];
    //ISSUE TYPES
    public const LOCATION_TRANSFER = "Location Transfer";
    public const SUB_LOCATION_TRANSFER = "Sub Location Transfer";
    public const SUB_CONTRACTING = "Sub Contracting";
    public const JOB_ORDER = "Job Work";
    //MI - PSLIP ACCEPTED/ SUB STANDARD QTY KEYS
    public const MI_ACCEPTED_QTY_KEY = "mi_accepted_qty";
    public const MI_SUB_STANDARD_QTY_KEY = "mi_subprime_qty";
}
