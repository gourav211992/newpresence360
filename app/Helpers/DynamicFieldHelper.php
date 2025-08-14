<?php

namespace App\Helpers;
use App\Models\Book;
use App\Models\BookDynamicField;
use App\Models\DynamicFieldDetail;
use App\Models\Service;
class DynamicFieldHelper
{
    public static function generateFieldUI(DynamicFieldDetail $dynamicFieldDetail, null|string $value = "") : string
    {
        $ui = "";
        if ($dynamicFieldDetail -> data_type === ConstantHelper::DATA_TYPE_TEXT) {
            $ui = "
            <div class='col-md-3'>
                <div class='mb-1'>
                    <label class='form-label'>$dynamicFieldDetail->name</label> 
                    <input type='text' value = '$value' class='form-control mw-100' name = 'dynamic_field[$dynamicFieldDetail->header_id-$dynamicFieldDetail->id]'>
                </div>
            </div>
            ";
        } else if ($dynamicFieldDetail -> data_type === ConstantHelper::DATA_TYPE_NUMBER) {
            $ui = "
            <div class='col-md-3'>
                <div class='mb-1'>
                    <label class='form-label'>$dynamicFieldDetail->name</label> 
                    <input type='text' value = '$value' class='form-control mw-100 numberonly' name = 'dynamic_field[$dynamicFieldDetail->header_id-$dynamicFieldDetail->id]'>
                </div>
            </div>
            ";
        } else if ($dynamicFieldDetail -> data_type === ConstantHelper::DATA_TYPE_DATE) {
            $ui = "
            <div class='col-md-3'>
                <div class='mb-1'>
                    <label class='form-label'>$dynamicFieldDetail->name</label> 
                    <input type='date' value = '$value' class='form-control mw-100' name = 'dynamic_field[$dynamicFieldDetail->header_id-$dynamicFieldDetail->id]'>
                </div>
            </div>
            ";
        } else if ($dynamicFieldDetail -> data_type === ConstantHelper::DATA_TYPE_BOOLEAN) {
            $defaultSelected = $value ? '' : 'selected';
            $yesSelected = ($value == 'yes') ? 'selected' : '';
            $noSelected = ($value == 'no') ? 'selected' : '';
            $ui = "
            <div class='col-md-3'>
                <div class='mb-1'>
                    <label class='form-label'>$dynamicFieldDetail->name</label> 
                    <select class='form-control mw-100' name = 'dynamic_field[$dynamicFieldDetail->header_id-$dynamicFieldDetail->id]'>
                    <option value = '' $defaultSelected >Select</option>
                    <option value = 'yes' $yesSelected >Yes</option>
                    <option value = 'no' $noSelected >No</option>
                    </select>
                </div>
            </div>
            ";
        } else if ($dynamicFieldDetail -> data_type === ConstantHelper::DATA_TYPE_LIST) {
            $defaultSelected = $value ? '' : 'selected';
            $values = $dynamicFieldDetail -> values;
            $options = "";
            foreach ($values as $listValue) {
                $currentValue = $listValue -> value;
                $selected = ($value == $currentValue ? 'selected' : '');
                $options .= "<option value = '$currentValue' $selected >$currentValue</option>";
            }
            $ui = "
            <div class='col-md-3'>
                <div class='mb-1'>
                    <label class='form-label'>$dynamicFieldDetail->name</label> 
                    <select class='form-control mw-100' name = 'dynamic_field[$dynamicFieldDetail->header_id-$dynamicFieldDetail->id]'>
                    <option value = '' $defaultSelected >Select</option>
                    $options
                    </select>
                </div>
            </div>
            ";
        } else {
            $ui = "";
        }
        return $ui;
    }

    public static function saveDynamicFields(string $dynamicFieldModel, int $headerId, array $requestData) : array
    {
        $status = true;
        foreach ($requestData as $key => $value) {
            //Retrieve header and detail Id
            $allValues = explode('-', $key);
            //Both Detail and Header ID is found
            if (count($allValues) == 2) {
                $field = DynamicFieldDetail::find($allValues[1]);
                if (isset($field)) {
                    $dynamicFieldModel::updateOrCreate(
                        [
                            'header_id' => $headerId,
                            'dynamic_field_id' => $allValues[0],
                            'dynamic_field_detail_id' => $allValues[1],
                            'name' => $field -> name,
                        ],
                        [
                            'value' => $value
                        ]
                    );
                }
            } else {
                $status = false;
                break;
            }
        }
        return array(
            'status' => $status,
            'message' => $status ? '' : 'Dynamic Field array not setup'
        );
    }

    public static function getServiceDynamicFields(string $serviceAlias)
    {
        $serviceId = Service::where('alias', $serviceAlias) -> first() ?-> id;
        $bookIds = Book::withDefaultGroupCompanyOrg() -> where('service_id', $serviceId) -> get() -> pluck('id') -> toArray();
        $dynamicFieldIds = BookDynamicField::whereIn('book_id', $bookIds) -> get() -> pluck('dynamic_field_id') -> toArray();
        $dynamicFields = DynamicFieldDetail::whereIn('header_id', $dynamicFieldIds) -> get();
        return $dynamicFields;
    }
}
