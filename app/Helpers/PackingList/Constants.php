<?php

namespace App\Helpers\PackingList;

use App\Helpers\ConstantHelper;
use App\Helpers\ServiceParametersHelper;
use App\Models\Legal;

class Constants
{
    const SERVICE_ALIAS = "plist";
    const MODULE_NAME = "Packing List";
    const PARAMETERS = [
        [
            "name" => ServiceParametersHelper::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => [ConstantHelper::SO_SERVICE_ALIAS], //All possible values
            "default_value" => [ConstantHelper::SO_SERVICE_ALIAS], //Default selected value(s)
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
        ]
    ];
}
