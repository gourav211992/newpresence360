<?php

namespace App\Http\Requests;

use App\Helpers\ConstantHelper;
use App\Helpers\ItemHelper;
use App\Models\ErpItemAttribute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ErpMaterialReturnRequest extends FormRequest
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
            'material_return_id' => 'nullable|numeric|integer',
            
            'book_id' => 'required|numeric|integer|exists:erp_books,id',
            'document_no' => [
                'required'
            ],
            
            'document_date' => 'required|date',
            'reference_no' => 'nullable|string',
            'issue_type' => 'required|string',
            'store_from_id' => 'required|numeric|integer|exists:erp_stores,id',
    
            // Ensure arrays are properly validated
            'item_id' => 'required|array',
            'item_id.*' => 'required|numeric|integer',
    
            'item_qty' => 'required|array',
            'item_qty.*' => 'required|numeric|min:0.01',
    
            'item_rate' => 'required|array',
            'item_rate.*' => 'required|numeric|min:0.01',
    
            // 'user_id' => 'nullable|array',
            // 'user_id.*' => 'nullable|integer',
    
            // 'department_id' => 'nullable|array',
            // 'department_id.*' => 'nullable|integer',
    
            'final_remarks' => 'nullable|string|max:255'
        ];
    }
    
    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $itemIds = $this->input('item_id', []);
            $userIds = $this->input('user_id', []);
            $departmentIds = $this->input('department_id', []);
            $itemsQty = $this -> input('item_qty', []);
            $itemRate = $this -> input('item_rate', []);
            $issue_type = $this -> input('issue_type', []);
            
            // Ensure at least one item exists
            if (empty($itemIds) || count($itemIds) === 0) {
                $validator->errors()->add('item_id', 'At least one item must be selected.');
            }
    
            // Ensure that for each entry, either user_id or department_id is provided
            foreach ($itemIds as $index => $itemId) {
                $userId = $userIds[$index] ?? null;
                $departmentId = $departmentIds[$index] ?? null;
                if($issue_type == 'Consumption')
                {

                    if (!$userId && !$departmentId) {
                        $validator->errors()->add("user_id.$index", 'Either user_id or department_id is required.');
                        $validator->errors()->add("department_id.$index", 'Either user_id or department_id is required.');
                    }
                }
            }

            if (empty($itemIds) || empty($itemsQty) || empty($itemRate))
            {
                $validator->errors()->add("custom_error", "Alteast one item is required with all fields");
            }
            if ((count($itemIds) !== count($itemsQty)) || (count($itemIds) !== count($itemRate)))
            {
                $validator->errors()->add("custom_error", "Please specify all details for each item");
            }
            // $itemAttributesCombination = [];
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

                // Request checker for same item name , attribute and requester type
                $requestAttributesForHelper = json_decode($this -> item_attributes[$itemKey], true);
                $userId = $userIds ? $userIds[$itemKey] : null;
                $departmentId = $departmentIds ? $departmentIds[$itemKey] : null;
                $currentItemAttribute = '';
                foreach ($requestAttributesForHelper as $attributeIndex => $attribute) {
                    foreach ($attribute['values_data'] as $valData) {
                        if ($valData['selected'] == 'true') {
                            $itemAttributesCombination[$itemKey]['attributes'] = ($attributeIndex == 0 ? '' : ',') . $attribute['id']. ":" . $valData['id'];
                            $itemAttributesCombination[$itemKey]['item_id'] = $itemId;
                            $itemAttributesCombination[$itemKey]['item_key'] = $itemKey;
                            $itemAttributesCombination[$itemKey]['user_id'] = $userId;
                            $itemAttributesCombination[$itemKey]['department_id'] = $departmentId;
                        }
                    }
                }
                $itemAttributesCombination[$itemKey]['attributes'] = isset($itemAttributesCombination[$itemKey]['attributes']) ? $itemAttributesCombination[$itemKey]['attributes'] : '';
                $itemAttributesCombination[$itemKey]['item_id'] = $itemId;
                $itemAttributesCombination[$itemKey]['item_key'] = $itemKey;
                $currentItemAttribute = $itemAttributesCombination[$itemKey]['attributes'];
                $existingItem = array_filter($itemAttributesCombination, function ($itemAttribute) use($itemId, $userId , $departmentId ,$currentItemAttribute,$itemAttributesCombination, $itemKey) {
                    return (
                        (!$itemAttribute['item_id'] == $itemId) &&
                        ( $itemAttribute['attributes'] == $currentItemAttribute) &&
                        ($itemAttribute['item_key']) &&
                        (!isset($itemAttribute['user_id']) || $itemAttribute['user_id'] == $userId) &&
                        (!isset($itemAttribute['department_id']) || $itemAttribute['department_id'] == $departmentId) &&
                        $itemAttribute['item_id'] == $itemId &&
                        ($userId === null || $itemAttribute['user_id'] == $userId) && 
                        ($departmentId === null || $itemAttribute['department_id'] == $departmentId) &&
                        $itemAttribute['attributes'] == $currentItemAttribute &&
                        $itemKey !== $itemAttribute['item_key']
                    );});
                if (isset($existingItem) && count($existingItem) > 0) {
                    // $validator->errors()->add("item_code." . $itemKey, "Item with same attributes or requester already exists");
                    $validator->errors()->add("item_code." . $itemKey, "Duplicate Record !");
                    return;
                }
            }
        });
    }
}
