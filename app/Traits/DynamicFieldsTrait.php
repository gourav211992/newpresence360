<?php

namespace App\Traits;
use App\Helpers\DynamicFieldHelper;
use App\Models\DynamicFieldDetail;

trait DynamicFieldsTrait
{
    /* Use carefully -> Only use for model containing dynamic_fields relation and following columns -
        dynamic_field_id INT
        dynamic_field_detail_id INT
        name STRING
        value STRING 
    */
    public function dynamicfieldsUi()
    {
        $dynamicFields = $this -> dynamic_fields;
        $prefilledFieldValues = "";
        foreach ($dynamicFields as $dynamicField) {
            $dynamicFieldObj = DynamicFieldDetail::find($dynamicField -> dynamic_field_detail_id);
            if (isset($dynamicFieldObj)) {
                $prefilledFieldValues .= DynamicFieldHelper::generateFieldUI($dynamicFieldObj, $dynamicField -> value);
            }
        }
        $finalCardHTML = "
        <div class='card quation-card'>
            <div class='card-header newheader'>
                <div>
                    <h4 class='card-title'>Dynamic Fields</h4> 
                </div>
            </div>
            <div class='card-body'> 
                <div class='row'>
                    $prefilledFieldValues
                </div>
            </div>                                                                                                
        </div>
        ";
        return $finalCardHTML;
    }
}
