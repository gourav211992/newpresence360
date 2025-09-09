<?php
namespace App\Helpers\RGR;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\ConstantHelper;
//Advanced Shipment Notification Service
class Constants
{
    const SERVICE_ALIAS = "rgr";
    const SERVICE_NAME = "Return Goods Receipt";
    const PARAMETERS = [
       [
            "name" => ServiceParametersHelper::REFERENCE_FROM_SERVICE_PARAM, 
            "applicable_values" => [ConstantHelper::PDS_SERVICE_ALIAS], 
            "default_value" => [ConstantHelper::PDS_SERVICE_ALIAS], 
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
        [
            "name" => ServiceParametersHelper::OK_TO_RECIEVE_BOOK_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => false,
            'service_level_visibility' => false
        ],
    ];

    const RGR_SEGREGATION_WRONG_PRODUCT = "Wrong Product";
    const RGR_SEGREGATION_PACK_MISSING = "Package Missing";
    const RGR_SEGREGATION_OK_TO_RECIEVE = "Ok to recieve";

    const DEFECT_SEVERITY_MINOR = 'minor';
    const DEFECT_SEVERITY_MAJOR = 'major';
    const DEFECT_SEVERITY_SCRAP = 'scrap';

    const DEFECT_SEVERITY_LEVELS = [
        ['label' => 'Minor', 'value' => self::DEFECT_SEVERITY_MINOR],
        ['label' => 'Major', 'value' => self::DEFECT_SEVERITY_MAJOR],
        ['label' => 'Scrap', 'value' => self::DEFECT_SEVERITY_SCRAP],
    ];

    const DAMAGE_NATURE_NO_DAMAGE = 'no_damage';
    const DAMAGE_NATURE_CUSTOMER_DAMAGE = 'customer_damage';
    const DAMAGE_NATURE_TRANSIT_HANDLE_DAMAGE = 'transit_handling_damage';
    const DAMAGE_NATURE_WEAR_AND_TEAR = 'wear_tear_damage';

    const DAMAGE_NATURES = [
        ['label' => 'No Damage', 'value' => self::DAMAGE_NATURE_NO_DAMAGE],
        ['label' => 'Customer Damage', 'value' => self::DAMAGE_NATURE_CUSTOMER_DAMAGE],
        ['label' => 'Transit / Handling Damage', 'value' => self::DAMAGE_NATURE_TRANSIT_HANDLE_DAMAGE],
        ['label' => 'Wear and Tear', 'value' => self::DAMAGE_NATURE_WEAR_AND_TEAR],
    ];

    private static $severityMap = [
        'minor' => [['label'=>'Component Missing','value'=>'component_missing']],
        'major' => [['label'=>'Major Damage','value'=>'major_damage']],
        'scrap' => [['label'=>'Full Hardware Missing','value'=>'full_hardware_missing']],
    ];

    public static function getDefectTypesBySeverity($severity)
    {
        $severity = strtolower($severity);
        return self::$severityMap[$severity] ?? [];
    }
}
