<?php

namespace App\Helpers\Configuration;

use App\Models\Configuration;
use App\Helpers\Configuration\Constants as ConfigConstant;

class Helper
{
    public static function getConfigurationValueOfOrg(string $key, int $orgId) : string
    {
        $config = Configuration::select('id', 'config_value') -> where('type', ConfigConstant::ORG_MORPH_TYPE) 
        -> where('type_id', $orgId) -> where('config_key', $key) -> first();
        $value = isset($config -> config_value) ? $config -> config_value : "";
        return $value;
    }
}
