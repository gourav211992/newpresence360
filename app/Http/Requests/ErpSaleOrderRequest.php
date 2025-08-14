<?php

namespace App\Http\Requests;

use App\Helpers\ConstantHelper;
use App\Helpers\ItemHelper;
use App\Models\ErpItemAttribute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ErpSaleOrderRequest extends FormRequest
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
            'sale_order_id' => 'numeric|integer',
            'book_id' => 'required|numeric|integer|exists:erp_books,id',
            'document_no' => ['required'],
            'document_date' => 'required|date',
            'reference_no' => 'nullable|string',
            'customer_id' => 'required|numeric|integer|exists:erp_customers,id',
            'currency_id' => 'required|numeric|integer|exists:mysql_master.currency,id',
            'payment_terms_id' => 'required|numeric|integer|exists:erp_payment_terms,id',
            // 'billing_address' => 'required_without:sale_order_id',
            // 'shipping_address' => 'required_without:sale_order_id',
            'item_id.*' => 'required|numeric|integer',
            'item_qty.*' => 'required|numeric|min:1',
            'item_rate.*' => 'required|numeric|min:1',
            'final_remarks' => 'nullable|string|max:255',
            'customer_phone_no' => 'nullable|string|regex:/^[0-9]{10}$/',
            'customer_email' => 'nullable|email',
            'customer_gstin' => 'nullable|string|size:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'
        ];
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            //Check atleast one item exists
            $itemIds = $this -> input('item_id', []);
            $itemsQty = $this -> input('item_qty', []);
            $itemRate = $this -> input('item_id', []);

            if (empty($itemIds) || empty($itemsQty) || empty($itemRate))
            {
                $validator->errors()->add("custom_error", "Alteast one item is required with all fields");
            }
            if ((count($itemIds) !== count($itemsQty)) || (count($itemIds) !== count($itemRate)))
            {
                $validator->errors()->add("custom_error", "Please specify all details for each item");
            }
            $itemAttributesCombination = [];
            foreach ($itemIds as $itemKey => $itemId) {
                $attributes = [];
                $requestAttributesForHelper = json_decode($this -> item_attributes[$itemKey], true);
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
                if (!isset($this -> sale_order_id)) { //Only for creation                    
                    $requestAttributes = json_decode($this -> item_attributes[$itemKey], true);
                    $itemAttributes = ErpItemAttribute::where('item_id', $itemId) -> get() -> pluck('attribute_group_id')->toArray();
                    if (count($itemAttributes) > 0) { // Attributes present
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
                if ($this -> type !== ConstantHelper::SQ_SERVICE_ALIAS) {
                    //Check if Bom exists 
                    if ($itemId) {
                        $bomExists = ItemHelper::checkItemBomExists($itemId, []);
                        if ($bomExists['status'] == 'item_not_found' || $bomExists['status'] == 'bom_not_exists') {
                            $validator->errors()->add("item_code." . $itemKey, $bomExists['message']);
                        }
                        if (isset($bomExists['bom_id'])) {
                            $this->merge([
                                'item_bom_id' => array_merge($this->input('item_bom_id', []), [$itemKey => $bomExists['bom_id']])
                            ]);
                        }
                    }
                    $itemQty = $this -> item_qty[$itemKey];
                    $totalDeliveryQty = 0;
                    $deliverySchedule = isset($this -> item_delivery_schedule_qty[$itemKey]) ? $this -> item_delivery_schedule_qty[$itemKey] : [];
                    foreach ($deliverySchedule as $delvSchedule) {
                        $totalDeliveryQty += is_numeric($delvSchedule) ?  $delvSchedule : 0;
                    }
                    if (count($deliverySchedule) > 0) {
                        if ($totalDeliveryQty > $itemQty || $totalDeliveryQty < $itemQty) {
                            $validator->errors()->add("item_code." . $itemKey, "Delivery schedule not available");
                        }
                    }
                }
                // //Check same item and attributes
                $requestAttributesForHelper = json_decode($this -> item_attributes[$itemKey], true);
                $currentItemAttribute = '';
                foreach ($requestAttributesForHelper as $attributeIndex => $attribute) {
                    foreach ($attribute['values_data'] as $valData) {
                        if ($valData['selected'] == 'true') {
                            $newAttrVal = ($attributeIndex == 0 ? '' : ',') . $attribute['id']. ":" . $valData['id'];
                            isset($itemAttributesCombination[$itemKey]['attributes']) ? $itemAttributesCombination[$itemKey]['attributes'] .= $newAttrVal : $itemAttributesCombination[$itemKey]['attributes'] = $newAttrVal;
                            $itemAttributesCombination[$itemKey]['item_id'] = $itemId;
                            $itemAttributesCombination[$itemKey]['item_key'] = $itemKey;
                        }
                    }
                }
                $itemAttributesCombination[$itemKey]['attributes'] = isset($itemAttributesCombination[$itemKey]['attributes']) ? $itemAttributesCombination[$itemKey]['attributes'] : '';
                $itemAttributesCombination[$itemKey]['item_id'] = $itemId;
                $itemAttributesCombination[$itemKey]['item_key'] = $itemKey;
                $currentItemAttribute = $itemAttributesCombination[$itemKey]['attributes'];
                $existingItem = array_filter($itemAttributesCombination, function ($itemAttribute) use($itemId, $currentItemAttribute, $itemKey) {
                    return ($itemAttribute['item_id'] == $itemId && $itemAttribute['attributes'] == $currentItemAttribute && $itemKey !== $itemAttribute['item_key']);
                });
                if (isset($existingItem) && count($existingItem) > 0) {
                    $validator->errors()->add("item_code." . $itemKey, "Item with same attributes already exists");
                    return;
                }
            }
        });
    }
}
