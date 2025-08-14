<?php

namespace App\Http\Requests;

use App\Helpers\ConstantHelper;
use App\Helpers\ItemHelper;
use App\Models\ErpItemAttribute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ErpMaterialIssueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // public function authorize(): bool
    // {
    //     return false;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'material_issue_id' => 'numeric|integer',
            'book_id' => 'required|numeric|integer|exists:erp_books,id',
            'document_no' => ['required'],
            'document_date' => 'required|date',
            'reference_no' => 'nullable|string',
            'issue_type' => 'required|string',
            'store_from_id' => 'required|numeric|integer',
            'sub_store_from_id' => 'required|numeric|integer',
            'item_id.*' => 'required|numeric|integer',
            'item_qty.*' => 'required|numeric|min:0.000001',
            'item_rate.*' => 'required|numeric',
            'final_remarks' => 'nullable|string|max:255'
        ];
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            //Check atleast one item exists
            $itemIds = $this -> input('item_id', []);
            $itemsQty = $this -> input('item_qty', []);
            $itemRate = $this -> input('item_rate', []);

            if (empty($itemIds) || empty($itemsQty) || empty($itemRate))
            {
                $validator->errors()->add("custom_error", "Alteast one item is required with all fields");
            }
            if ((count($itemIds) !== count($itemsQty)) || (count($itemIds) !== count($itemRate)))
            {
                $validator->errors()->add("custom_error", "Please specify all details for each item");
            }
            // $itemAttributesCombination = [];
            $fromStoreId = $this -> store_from_id;
            $toStoreId = $this -> store_to_id;
            
            foreach ($itemIds as $itemKey => $itemId) {
                $attributes = [];
                $requestAttributesForHelper = json_decode($this -> item_attributes[$itemKey], true);
                if ($requestAttributesForHelper) {
                    foreach ($requestAttributesForHelper as $attribute) {
                        $selectedAttributeValue = null;
                        foreach ($attribute['values_data'] as $valData) {
                            if ($valData['selected'] == 'true') {
                                $selectedAttributeValue = $valData['id'];
                                break;
                            }
                        }
                        array_push($attributes, [
                            'attribute_id' => $attribute['id'],
                            'attribute_value' => $selectedAttributeValue
                        ]);
                    }
                }
                if (!isset($this -> material_issue_id)) { //Only for creation                    
                    $requestAttributes = json_decode($this -> item_attributes[$itemKey], true);
                    $itemAttributes = ErpItemAttribute::where('item_id', $itemId) -> get() -> pluck('attribute_group_id')->toArray();
                    if (count($itemAttributes) > 0 && $requestAttributesForHelper) { // Attributes present
                        foreach ($requestAttributes as $requestedAttribute) {
                            $seletedData = false;
                            foreach ($requestedAttribute['values_data'] as $valData) {
                                if ($valData['selected']) {
                                    $seletedData = true;
                                    break;
                                }
                            }
                            if (!$seletedData) {
                                $validator->errors()->add("attribute_value_" . $itemKey, "*Required");
                            }
                        }
                    }
                }              

                $fromSubStoreId = isset($this -> item_sub_store_from[$itemKey]) ? $this -> item_sub_store_from[$itemKey] : null;
                $toSubStoreId = isset($this -> item_sub_store_to[$itemKey]) ? $this -> item_sub_store_to[$itemKey] : null;

                $fromStationId = isset($this -> item_station_from[$itemKey]) ? $this -> item_station_from[$itemKey] : null;
                $toStationId = isset($this -> item_station_to[$itemKey]) ? $this -> item_station_to[$itemKey] : null;

                if ($fromStoreId == $toStoreId && $fromSubStoreId == $toSubStoreId && $fromStationId === $toStationId)
                {
                    $validator->errors()->add("item_code." . $itemKey, "To and From location cannot be same");
                }

                //Qty and delivery schedule check
                    // $itemQty = $this -> item_qty[$itemKey];
                    // $totaltoLocationQty = 0;
                    // $toLocationSchedule = isset($this -> item_locations_to[$itemKey]) ? json_decode($this -> item_locations_to[$itemKey], true) : [];
                    // foreach ($toLocationSchedule as $delvSchedule) {
                    //     $totaltoLocationQty += is_numeric($delvSchedule['qty']) ?  $delvSchedule['qty'] : 0;
                    // }
                    // if ($totaltoLocationQty > $itemQty || $totaltoLocationQty < $itemQty) {
                    //     $validator->errors()->add("item_code." . $itemKey, "To Location Qty does not match item qty");
                    // }
                
                // // //Check same item and attributes
                // $requestAttributesForHelper = json_decode($this -> item_attributes[$itemKey], true);
                // $currentItemAttribute = '';
                // foreach ($requestAttributesForHelper as $attributeIndex => $attribute) {
                //     foreach ($attribute['values_data'] as $valData) {
                //         if ($valData['selected'] == 'true') {
                //             $itemAttributesCombination[$itemKey]['attributes'] = ($attributeIndex == 0 ? '' : ',') . $attribute['id']. ":" . $valData['id'];
                //             $itemAttributesCombination[$itemKey]['item_id'] = $itemId;
                //             $itemAttributesCombination[$itemKey]['item_key'] = $itemKey;
                //         }
                //     }
                // }
                // $itemAttributesCombination[$itemKey]['attributes'] = isset($itemAttributesCombination[$itemKey]['attributes']) ? $itemAttributesCombination[$itemKey]['attributes'] : '';
                // $itemAttributesCombination[$itemKey]['item_id'] = $itemId;
                // $itemAttributesCombination[$itemKey]['item_key'] = $itemKey;
                // $currentItemAttribute = $itemAttributesCombination[$itemKey]['attributes'];
                // $existingItem = array_filter($itemAttributesCombination, function ($itemAttribute) use($itemId, $currentItemAttribute, $itemKey) {
                //     return ($itemAttribute['item_id'] == $itemId && $itemAttribute['attributes'] == $currentItemAttribute && $itemKey !== $itemAttribute['item_key']);
                // });
                // if (isset($existingItem) && count($existingItem) > 0) {
                //     $validator->errors()->add("item_code." . $itemKey, "Item with same attributes already exists");
                //     return;
                // }
            }
        });
    }
}
