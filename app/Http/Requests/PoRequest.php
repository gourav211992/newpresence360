<?php

namespace App\Http\Requests;

use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Models\Item;
use App\Models\NumberPattern;
use App\Models\PiItem;
use App\Models\PoItem;
use App\Models\ItemAttribute;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\PiPoMapping;
use App\Traits\ProcessesComponentJson;

class PoRequest extends FormRequest
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
        $poId = $this->route('id');
        // $departmentRequired = false;
        $storeRequired = false;
        if($this->segments()[0] != ConstantHelper::SUPPLIER_INVOICE_SERVICE_ALIAS) {
            // $departmentRequired = true;
            $storeRequired = true;
        }
        $vendor = auth()->user()?->vendor_portal;
        if($vendor) {
            // $departmentRequired = false;
            $storeRequired = false;
        }
        $rules = [
            'book_id' => 'required',
            'exchange_rate' => 'required',
            'document_date' => 'required|date',
            'document_number' => 'required',
            'vendor_id' => $vendor ? 'nullable' : 'required',
            'currency_id' => $vendor ? 'nullable' : 'required',
            'payment_term_id' => $vendor ? 'nullable' : 'required',
        ];
        // if($departmentRequired) {
        //     $rules['department_id'] = 'required';
        // }
        if($storeRequired) {
            $rules['store_id'] = 'required';
        }
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
        $rules['component_item_name.*'] = 'required';
        $rules['components.*.qty'] = 'required|numeric|min:0.000001';
        $rules['components.*.rate'] = 'required|numeric|min:0.01';        
        $rules['components.*.attr_group_id.*.attr_name'] = 'required';
        $rules['components.*.uom_id'] = 'required';
        $rules['components.*.delivery_date'] = ['required', 'date'];

        foreach ($this->input('components', []) as $index => $component) {
            $item_id = $component['item_id'] ?? null;
            $item = Item::find($item_id);
            $index = $index + 1;
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
            'component_item_name.*.required' => 'Required',
            'components.*.qty.required' => 'Required',
            'components.*.rate.required' => 'Required',
            'components.*.qty.required' => 'Required',
            'components.*.attr_group_id.*.attr_name.required' => 'Select Attribute',
            'document_date.in' => 'The document date must be today.',
            'document_date.required' => 'The document date is required.',
            'document_date.date' => 'Please enter a valid date for the document date.',
            'document_date.after_or_equal' => 'The document date cannot be in the past.',
            'document_date.before_or_equal' => 'The document date cannot be in the future.',
        ];
 
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $components = $this->input('components', []);
            $items = [];
            foreach ($components as $key => $component) {
                $itemValue = floatval($component['item_total_cost']);
                if($itemValue < 0) {
                    $validator->errors()->add("components.$key.item_name", "Item total can't be negative.");
                }
                $itemId = $component['item_id'] ?? null;
                $uomId = $component['uom_id'] ?? null;
                $soId = $component['so_id'] ?? null;
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
                    'so_id' => $soId,
                ];
                foreach ($items as $existingItem) {
                    if (
                        $existingItem['item_id'] === $currentItem['item_id'] &&
                        $existingItem['uom_id'] === $currentItem['uom_id'] &&
                        $existingItem['attributes'] === $currentItem['attributes'] &&
                        $existingItem['so_id'] === $currentItem['so_id']
                    ) {
                        $validator->errors()->add(
                            "components.$key.item_id",
                            "Duplicate item!"
                            // "Duplicate entry found for item_id: {$itemId}, uom_id: {$uomId}."
                        );
                        return;
                    }
                }
                $items[] = $currentItem;

                // Shor close resctriction
                // $poItemId = $component['po_item_id'] ?? null;
                // $poItem = PoItem::find($poItemId);
                // if(floatval($component['short_close_qty']) && $poItem) {
                //     if(floatval($poItem->order_qty) < max($poItem->grn_qty,$poItem->invoice_quantity) + floatval($component['short_close_qty'])) {
                //         $validator->errors()->add("components.$key.short_close_qty", "Short close qty less then PO qty");
                //     }
                // }
            }
        });
        
        $vendor = auth()->user()?->vendor_portal;
        if(!$vendor) {
            $validator->after(function ($validator) {
                foreach ($this->input('components', []) as $key => $component) {
                    $itemId = $component['item_id'] ?? null;
                    $uomId = $component['uom_id'] ?? null;
                    $poItemId = $component['po_item_id'] ?? null;

                    // $piItemId = $component['pi_item_id'] ?? null;
                    $poItemId = $component['po_item_id'] ?? null;
                    if($this->route('type') == 'supplier-invoice') {
                            $poItem = PoItem::find($poItemId);
                            
                    } else {                    
                        if ($itemId) {

                            $poItem = PoItem::find($poItemId);
                            if ($poItemId) {
                                $poItem = PoItem::find($poItemId);
                                if ($poItem) {
                                    $minOrderQty = $poItem->grn_qty;
                                    $inputQty = $component['qty'] ?? 0;
                                    if ($inputQty < $minOrderQty) {
                                        $validator->errors()->add("components.$key.qty", "Quantity can't be less than MRN.");
                                    }
                                }
                            }

                            $selectedAttributes = [];
                            if(isset($component['attr_group_id']) && count($component['attr_group_id'])) {
                                foreach($component['attr_group_id'] as $k => $attr_group) {
                                    $ia = ItemAttribute::where('item_id',$itemId)
                                                    ->where('attribute_group_id',$k)
                                                    ->first();
                                    if(isset($ia->id) && $ia->id) {
                                        $selectedAttributes[] = ['attribute_id' => $ia->id, 'attribute_value' => intval($attr_group['attr_name'])];
                                    }
                                }
                            }

                            $pi_item_ids = @$component['pi_item_hidden_ids'] ? explode(',',$component['pi_item_hidden_ids']) : [];
                            $balanceQty = PiItem::whereIn('id',$pi_item_ids)
                                ->where('item_id',$itemId)
                                ->where('uom_id',$uomId)
                                // ->when(count($selectedAttributes), function ($query) use ($selectedAttributes) {
                                //     $query->whereHas('attributes', function ($piAttributeQuery) use ($selectedAttributes) {
                                //         $piAttributeQuery->where(function ($subQuery) use ($selectedAttributes) {
                                //             foreach ($selectedAttributes as $piAttribute) {
                                //                 $subQuery->orWhere(function ($q) use ($piAttribute) {
                                //                     $q->where('item_attribute_id', $piAttribute['attribute_id'])
                                //                     ->where('attribute_value', $piAttribute['attribute_value']);
                                //                 });
                                //             }
                                //         });
                                //     }, '=', count($selectedAttributes));
                                // })
                                ->where(function($piItemQuery) use($selectedAttributes) {
                                    if(count($selectedAttributes)) {
                                        $piItemQuery->whereHas('attributes',function($piAttributeQuery) use($selectedAttributes) {
                                            foreach($selectedAttributes as $piAttribute) {
                                                $piAttributeQuery->where('item_attribute_id',$piAttribute['attribute_id'])
                                                ->where('attribute_value',$piAttribute['attribute_value']);
                                            }
                                        });
                                    }
                                })
                                ->selectRaw('SUM(indent_qty - order_qty) as balance_indent_qty')
                                ->value('balance_indent_qty') ?? 0;

                            if($poItem) {
                                $inputQty = (floatval($component['qty']) - $poItem->order_qty) ?? 0;
                            } else {
                                $inputQty = floatval($component['qty']) ?? 0;
                            }
                            // if(count($pi_item_ids)) {
                            //     if($inputQty > $balanceQty) {
                            // Commented as for discuss inder sir 
                            //         $validator->errors()->add("components.$key.qty", "Po is more than indent qty.");
                            //     }
                            // }
                        }
                    }
                }
            });
        } else {
            $validator->after(function ($validator) {
                foreach ($this->input('components', []) as $key => $component) {
                    $poItemId = $component['si_po_item_id'] ?? null;
                    $poItem = PoItem::find($poItemId);
                    $inputQty = $component['qty'] ?? 0;
                    if(floatval($poItem->grn_qty)) {
                        if(floatval($inputQty) < floatval($poItem->grn_qty)) {
                            $validator->errors()->add("components.$key.qty", "Quantity can't be less than MRN.");
                        }
                        if(floatval($inputQty) > (floatval($poItem->order_qty) - floatval($poItem->invoice_quantity))) {
                            $validator->errors()->add("components.$key.qty", "Quantity can't be greater than Order Qty.");
                        }
                    } else {
                        // $remaingQty = floatval($poItem->order_qty) - floatval($poItem->invoice_quantity);
                        // $minOrderQty = $remaingQty + $inputQty;
                        // if($minOrderQty > $poItem->order_qty) {
                        if(floatval($inputQty) > (floatval($poItem->order_qty) - floatval($poItem->invoice_quantity))) {
                            $validator->errors()->add("components.$key.qty", "Quantity can't be greater than Order Qty.");
                        } 
                    }
                }
            });
        }
    }
}
