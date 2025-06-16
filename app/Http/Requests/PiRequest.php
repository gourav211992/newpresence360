<?php

namespace App\Http\Requests;

use App\Helpers\BookHelper;
use App\Helpers\Helper;
use App\Models\ErpSoItem;
use App\Models\Item;
use App\Models\NumberPattern;
use App\Models\PiItem;
use App\Models\ItemAttribute;
use App\Models\PiSoMapping;
use App\Models\PiSoMappingItem;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ProcessesComponentJson;

class PiRequest extends FormRequest
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
        $parameters = [];
        $response = BookHelper::fetchBookDocNoAndParameters($this->input('book_id'), $this->input('document_date'));
        if ($response['status'] === 200) {
            $parameters = json_decode(json_encode($response['data']['parameters']), true);
        }
        // $piId = $this->route('id');
        $poId = $this->route('id');

        //Requester Type
        $requesterType = @$parameters['requester_type'] ?? 'Department';
        if ($requesterType && is_array($requesterType) && count($requesterType) > 0) {
            $this -> merge(['requester_type' => $requesterType[0]]);
        }

        $rules = [
            'book_id' => 'required',
            'document_number' => 'required',
            'document_date' => 'required|date',
            'store_id' => 'required',
            'department_id' => 'nullable',
            'requester_type' => 'required|string',
            'user_id' => 'nullable|integer'
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
        if($isFeature && $isPast) {
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
                if($poId) {
                    $rules['document_number'] = 'required|unique:erp_purchase_orders,document_number,' . $poId;
                } else {
                    $rules['document_number'] = 'required|unique:erp_purchase_orders,document_number';
                }
            }
        }
        $rules['components.*.attr_group_id.*.attr_name'] = 'required';
        $rules['component_item_name.*'] = 'required';
        $rules['components.*.qty'] = 'required|numeric|min:0.000001';
        $rules['components.*.uom_id'] = 'required';

        foreach ($this->input('components', []) as $index => $component) {
            $item_id = $component['item_id'] ?? null;
            $item = Item::find($item_id);
            if ($item && $item->itemAttributes->count() > 0) {
                $rules["components.$index.attr_group_id.*.attr_name"] = 'required';
            } else {
                $rules["components.$index.attr_group_id.*.attr_name"] = 'nullable';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'book_id.required' => 'The series is required.',
            'item_code.required' => 'The product code is required.',
            'status.required' => 'The status field is required.',
            'uom_id' => 'The unit of measure must be a string.',
            'components.*.uom_id.required' => 'Required',
            'component_item_name.*.required' => 'Required',
            'components.*.qty.required' => 'Required',
            'components.*.qty.required' => 'Required',
            'components.*.attr_group_id.*.attr_name.required' => 'Select Attribute',
            'document_date.in' => 'The document date must be today.',
            'document_date.required' => 'The document date is required.',
            'document_date.date' => 'Please enter a valid date for the document date.',
            'document_date.after_or_equal' => 'The document date cannot be in the past.',
            'document_date.before_or_equal' => 'The document date cannot be in the future.',
        ];
 
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $components = $this->input('components', []);
            $items = [];
            foreach ($components as $key => $component) {

                $reqQty = floatval($component['qty'] ?? 0);
                $adjQty = floatval($component['adj_qty'] ?? 0);
                $avlStock = floatval($component['avl_stock'] ?? 0);

                if ($adjQty > $reqQty) {
                    $validator->errors()->add("components.$key.item_name", "Adj qty more than req qty.");
                }
                if ($adjQty > $avlStock) {
                    $validator->errors()->add("components.$key.item_name", "Adj qty more than Avl Stock.");
                }

                $soId = $component['so_id'] ?? null;
                $itemId = $component['item_id'] ?? null;
                $uomId = $component['uom_id'] ?? null;
                $attributes = [];
                foreach ($component['attr_group_id'] ?? [] as $groupId => $attrName) {
                    $attr_id = $groupId;
                    $attr_value = $attrName['attr_name'] ?? null;
                    if ($attr_id && $attr_value) {
                        $attributes[] = [
                            'attr_id' => $attr_id,
                            'attr_value' => $attr_value,
                        ];
                    }
                }
                $currentItem = [
                    'item_id' => $itemId,
                    'uom_id' => $uomId,
                    'attributes' => $attributes,
                    'so_id' => $soId
                ];
                foreach ($items as $existingItem) {
                    if (
                        $existingItem['item_id'] === $currentItem['item_id'] &&
                        $existingItem['uom_id'] === $currentItem['uom_id'] &&
                        $existingItem['attributes'] === $currentItem['attributes'] &&
                        $existingItem['so_id'] === $currentItem['so_id']
                    ) {
                        // $item = Item::find($itemId);
                        // $uom = Unit::find($uomId);
                        $validator->errors()->add(
                            "components.$key.item_id",
                            "Duplicate item!"
                            // "Duplicate entry found for item_id: {$itemId}, uom_id: {$uomId}."
                        );
                        return;
                    }
                }
                $items[] = $currentItem;
            }
        });
        

        $piId = $this->route('id');
        if($piId) {
            $validator->after(function ($validator) {
                foreach ($this->input('components', []) as $key => $component) {
                    $piItemId = $component['pi_item_id'] ?? null;
                    $inputQty = $component['qty'] ?? 0;
                    if ($piItemId) {
                        $piItem = PiItem::with('po_items')->find($piItemId);
                        if ($piItem && $piItem->po_items->count()) {
                            $minOrderQty = $piItem->po_items->sum('order_qty');
                            if (round($inputQty,2) < round($minOrderQty,2)) {
                                $validator->errors()->add("components.$key.qty", "Quantity can't be less than PO.");
                            }
                        }
                    }
                    $poSiMappingIds = PiSoMappingItem::where('pi_item_id', $piItemId)
                                    ->pluck('pi_so_mapping_id')
                                    ->toArray();

                    if(count($poSiMappingIds)) {
                        $avlQty = PiSoMapping::whereIn('id', $poSiMappingIds)
                                ->sum('qty');
                        if (round($inputQty,2) > round($avlQty,2)) {
                            $validator->errors()->add("components.$key.qty", "Quantity can't be grater than avl Qty.");
                        }
                    }

                }
            });
        } else {
            # Create Case
            $validator->after(function ($validator) {
                $showAttribute = intval($this->show_attribute) ? true : false;
                $so_item_ids = $this->so_item_ids ? explode(',',$this->so_item_ids) : [];
                if(!$showAttribute) {
                    $itemIds = $this->item_ids ? explode(',',$this->item_ids) : [];
                    $so_item_ids = ErpSoItem::whereIn('sale_order_id', $so_item_ids)
                            ->whereIn('item_id', $itemIds)
                            ->pluck('id')
                            ->toArray();
                }
                if(count($so_item_ids)) {
                    foreach ($this->input('components', []) as $key => $component) {
                        $itemId = $component['item_id'] ?? null;
                        $inputQty = floatval($component['qty']) ?? 0.00;
                        $vendorId = floatval($component['vendor_id']) ?? 0.00;
                        $attributes = [];
                        foreach($component['attr_group_id'] ?? [] as $groupId => $attrName) {
                            $itemAttr = ItemAttribute::where('attribute_group_id', $groupId)
                                        ->where('item_id', $itemId)
                                        ->first();
                            $attributes[] = ['attribute_id' => $itemAttr->id, 'attribute_value' => intval($attrName['attr_name'])];
                        }
                        $qty = PiSoMapping::where('item_id', $itemId)
                                            ->whereIn('so_item_id',$so_item_ids)
                                            ->when($vendorId, function ($query) use ($vendorId) {
                                                return $query->where('vendor_id', $vendorId);
                                            })
                                            ->whereJsonContains('attributes', $attributes)
                                            ->sum('qty');
                        
                        $pi_item_qty =  PiSoMapping::where('item_id', $itemId)
                        ->whereIn('so_item_id',$so_item_ids)
                        ->when($vendorId, function ($query) use ($vendorId) {
                            return $query->where('vendor_id', $vendorId);
                        })
                        ->whereJsonContains('attributes', $attributes)
                        ->sum('pi_item_qty');

                        if ($qty > 0 && round(($inputQty + $pi_item_qty), 2) > round($qty,2)) {
                            $validator->errors()->add("components.$key.qty", "Quantity can't be grater than avl Qty.");
                        }
                    }
                }
            });
        }
    }
}
