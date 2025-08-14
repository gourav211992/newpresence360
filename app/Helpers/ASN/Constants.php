<?php
namespace App\Helpers\ASN;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\ConstantHelper;
//Advanced Shipment Notification Service
class Constants
{
    const SERVICE_ALIAS = "asn";
    const SERVICE_NAME = "Advance Shipment Notification";
    const PARAMETERS = [
        [
            "name" => ServiceParametersHelper::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => [ConstantHelper::PO_SERVICE_ALIAS, ConstantHelper::JO_SERVICE_ALIAS], //All possible values
            "default_value" => [ConstantHelper::PO_SERVICE_ALIAS], //Default selected value(s)
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
    ];
}
