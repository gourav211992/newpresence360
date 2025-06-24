<?php

namespace App\Http\Requests;

use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\ItemHelper;
use App\Models\Item;
use App\Models\NumberPattern;
use App\Models\ItemAttribute;
use App\Models\PwoSoMapping;
use App\Models\PwoStationConsumption;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ProcessesComponentJson;

class MoRequest extends FormRequest
{
    use ProcessesComponentJson;
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
    protected function prepareForValidation(): void
    {
        $this->processComponentJson('components_json');
    }
    
    public function rules(): array
    {
        $moId = $this->route('id');
        $parameters = [];
        $response = BookHelper::fetchBookDocNoAndParameters($this->input('book_id'), $this->input('document_date'));
        if ($response['status'] === 200) {
            $parameters = json_decode(json_encode($response['data']['parameters']), true);
        }
        $rules = [
            'book_id' => 'required',
            'item_id' => 'required',
            'document_date' => 'required|date',
            'document_number' => 'required',
            'store_id' => 'required',
            // 'station_id' => 'nullable',
            'quantity' => 'nullable|max:255',
        ];
        $today = now()->toDateString();
        $isPast = false;
        $isFeature = false;
        $futureAllowed = isset($parameters['future_date_allowed']) && is_array($parameters['future_date_allowed']) && in_array('yes', array_map('strtolower', $parameters['future_date_allowed']));
        $backAllowed = isset($parameters['back_date_allowed']) && is_array($parameters['back_date_allowed']) && in_array('yes', array_map('strtolower', $parameters['back_date_allowed']));

        if (!$futureAllowed && !$backAllowed) {
            $rules['document_date'] = "required|date|in:$today";
        } else {
            if ($futureAllowed) {
                $rules['document_date'] = "after_or_equal:$today";
                $isFeature = true;
            } else {
                $rules['document_date'] = "before_or_equal:$today";
                $isFeature = false;
            }
            if ($backAllowed) {
                $rules['document_date'] = "before_or_equal:$today";
                $isPast = true;
            } else {
                $rules['document_date'] = "after_or_equal:$today";
                $isPast = false;
            }
        }
        if ($isFeature && $isPast) {
            $rules['document_date'] = "required|date";
        }

        // Check the condition only if book_id is present
        if ($this->filled('book_id')) {
            $user = Helper::getAuthenticatedUser();
            $numPattern = NumberPattern::where('organization_id', $user->organization_id)
                ->where('book_id', $this->book_id)
                ->orderBy('id', 'DESC')
                ->first();

            // Update document_number rule based on the condition
            if ($numPattern && $numPattern->series_numbering == 'Manually') {
                if ($moId) {
                    $rules['document_number'] = 'required|unique:erp_mfg_orders,document_number,' . $moId;
                } else {
                    $rules['document_number'] = 'required|unique:erp_mfg_orders,document_number';
                }
            }
        }
        $rules['component_item_name.*'] = 'required';
        $rules['components.*.qty'] = 'required|numeric|min:0.000001';
        // $rules['attributes.*.attr_group_id.*.attr_name'] = 'required';
        $rules['components.*.attr_group_id.*.attr_name'] = 'required';
        $rules['components.*.uom_id'] = 'required';
        foreach ($this->input('components', []) as $index => $component) {
            if (!empty($component['is_pi_item_id'])) {
                $item_id = $component['item_id'] ?? null;
                $item = Item::find($item_id);
                $index = $index + 1;
                if ($item && $item->itemAttributes->count() > 0) {
                    $rules["components.$index.attr_group_id.*.attr_name"] = 'required';
                } else {
                    $rules["components.$index.attr_group_id.*.attr_name"] = 'nullable';
                }
            }
        }
        return $rules;
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $attributes = $this->input('components') ?? [];
            $stationId = $this->station_id;
            $moId = $this->route('id');
            $selectedAttributes = [];
            if (count($attributes)) {
                foreach ($attributes as $k => $attribute) {
                    if (!empty($component['is_pi_item_id'])) {
                        $itemId = $attribute['item_id'] ?? null;
                        $attr_group = isset($attribute['attr_group_id']) ? array_values($attribute['attr_group_id']) : [];
                        if(count($attr_group)) {
                            $itemAttr = ItemAttribute::where('item_id', $itemId)->first();
                            if(!$itemAttr->all_checked) {
                                $itemAttr = ItemAttribute::where('item_id', $itemId)
                                    ->where('attribute_group_id', @$attr_group[0]['attr_name'])
                                    ->first();
                            }
                            $selectedAttributes[] = ['attribute_id' => $itemAttr?->id, 'attribute_value' => intval(@$attr_group[0]['attr_name'])];   
                        }
                        # Check bom not exist in header item
                        $bomExists = ItemHelper::checkItemBomExists($itemId, $selectedAttributes);
                        if (!$bomExists['bom_id']) {
                            $validator->errors()->add("components.$k.item_code", $bomExists['message']);
                        }
                        # So Item Qty Check
                        if(isset($attribute['pwo_mapping_id']) && $attribute['pwo_mapping_id']) {
                            $pwoMappingId = $attribute['pwo_mapping_id'] ?? null;

                            $pwoMapping = PwoSoMapping::find($pwoMappingId);
                            
                            $pwoStation = PwoStationConsumption::where('pwo_mapping_id',$pwoMapping?->id)
                                                    ->where('station_id',$stationId)
                                                    ->first();
                            if($pwoStation) {
                                $inputQty = floatval($attribute['qty']);
                                $pwoSoMapping = PwoSoMapping::find($pwoMappingId); 
                                if(floatval($inputQty) != (floatval($pwoSoMapping->inventory_uom_qty))) {
                                    $validator->errors()->add("components.$k.qty", "Quantity can't be changed.");
                                } 
                                // if($moId) {
                                //     $existingQty = $pwoStation ? floatval($pwoStation->mo_product_qty) : 0;
                                //     $availableQty = floatval($pwoSoMapping->inventory_uom_qty) - floatval($pwoStation->mo_product_qty) + $existingQty;
                                //     if ($inputQty > $availableQty) {
                                //         $validator->errors()->add("components.$k.qty", "Quantity can't be greater than Order Qty.");
                                //     }
                                // } else {
                                //     // if(floatval($inputQty) > (floatval($pwoSoMapping->inventory_uom_qty) - floatval($pwoStation->mo_product_qty))) {
                                //     if(floatval($inputQty) != (floatval($pwoSoMapping->inventory_uom_qty))) {
                                //         $validator->errors()->add("components.$k.qty", "Quantity must be equal to PWO Qty.");
                                //     } 
                                // }
                            }
                        }
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'book_id.required' => 'The series is required.',
            'item_code.required' => 'The product code is required.',
            'uom_id' => 'The unit of measure must be a string.',
            'component_item_name.*.required' => 'Required',
            'components.*.uom_id.required' => 'Required',
            'components.*.qty.required' => 'Required',
            'components.*.attr_group_id.*.attr_name.required' => 'Select Attribute',
            // 'attributes.*.attr_group_id.*.attr_name.required' => 'Select Attribute',
            'document_date.in' => 'The document date must be today.',
            'document_date.required' => 'The document date is required.',
            'document_date.date' => 'Please enter a valid date for the document date.',
            'document_date.after_or_equal' => 'The document date cannot be in the past.',
            'document_date.before_or_equal' => 'The document date cannot be in the future.',
        ];
    }
}
