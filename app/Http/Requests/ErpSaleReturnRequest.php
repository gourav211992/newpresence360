<?php

namespace App\Http\Requests;

use App\Helpers\ConstantHelper;
use App\Helpers\ItemHelper;
use App\Models\ErpItemAttribute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ErpSaleReturnRequest extends FormRequest
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
            'sale_return_id' => 'numeric|integer',
            'book_id' => 'required|numeric|integer|exists:erp_books,id',
            'document_no' => ['required'],
            'document_date' => 'required|date',
            'reference_no' => 'nullable|string',
            'customer_id' => 'required|numeric|integer|exists:erp_customers,id',
            'currency_id' => 'required|numeric|integer|exists:mysql_master.currency,id',
            'transporter_name' => 'required|max:255',
            'vehicle_no' => [
                'required',
                'regex:/^[A-Z]{2}[0-9]{2}[A-Z]{0,3}[0-9]{4}$/'
            ],
            'transporter_mode' => 'required|integer',
            // 'payment_terms_id' => 'required|numeric|integer|exists:erp_payment_terms,id',
            'billing_address' => 'required_without:sale_return_id',
            'shipping_address' => 'required_without:sale_return_id',
            'item_id.*' => 'required|numeric|integer',
            'item_qty.*' => 'required|numeric|min:1',
            'item_rate.*' => 'required|numeric|min:1',
            'final_remarks' => 'nullable|string|max:255'
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
            foreach ($itemIds as $itemKey => $itemId) {
                if (!isset($this -> sale_invoice_id)) { //Only for creation
                    //Check if Bom exists 
                    // $attributes = [];
                    // $requestAttributesForHelper = json_decode($this -> item_attributes[$itemKey], true);
                    // foreach ($requestAttributesForHelper as $attribute) {
                    //     $selectedAttributeValue = null;
                    //     foreach ($attribute['values_data'] as $valData) {
                    //         if ($valData['selected'] == 'true') {
                    //             $selectedAttributeValue = $valData['id'];
                    //             break;
                    //         }
                    //     }
                    //     array_push($attributes, [
                    //         'attribute_id' => $attribute['id'],
                    //         'attribute_value' => $selectedAttributeValue
                    //     ]);
                    // }
                    // if ($itemId) {
                    //     $bomExists = ItemHelper::checkItemBomExists($itemId, $attributes);
                    //     if ($bomExists['status'] == 'item_not_found' || $bomExists['status'] == 'bom_not_exists') {
                    //         $validator->errors()->add("item_code." . $itemKey, $bomExists['message']);
                    //     }
                    // }
                    
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
                // if ($this -> type !== ConstantHelper::SQ_SERVICE_ALIAS) {
                //     $itemQty = $this -> item_qty[$itemKey];
                //     $totalDeliveryQty = 0;
                //     $deliverySchedule = isset($this -> item_delivery_schedule_qty[$itemKey]) ? $this -> item_delivery_schedule_qty[$itemKey] : [];
                //     foreach ($deliverySchedule as $delvSchedule) {
                //         $totalDeliveryQty += is_numeric($delvSchedule) ?  $delvSchedule : 0;
                //     }
                //     if ($totalDeliveryQty > $itemQty) {
                //         $itemSno = $itemKey + 1;
                //         $validator->errors()->add("custom_error", "Item No - $itemSno delivery schedule quantity should not exceed Item Quantity");
                //     }
                //     if ($totalDeliveryQty < $itemQty) {
                //         $itemSno = $itemKey + 1;
                //         $validator->errors()->add("custom_error", "Item No - $itemSno delivery schedule quantity should match the order quantity");
                //     }
                // }
            }
        });
    }

    public function messages(): array
    {
        return [
            'vehicle_no.regex' => 'Invalid vehicle number format. Example: MH12AB1234',
        ];
    }
}
