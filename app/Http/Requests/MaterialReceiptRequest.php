<?php
namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

use App\Helpers\Helper;
use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;

use App\Models\Item;
use App\Models\PoItem;
use App\Models\MrnDetail;
use App\Models\NumberPattern;
use App\Models\ItemAttribute;
use Illuminate\Validation\Rule;


class MaterialReceiptRequest extends FormRequest
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

    protected $organization_id;
    protected $group_id; 

     protected function prepareForValidation()
     {
         $user = Helper::getAuthenticatedUser();
         $organization = $user->organization;
         $this->organization_id = $organization ? $organization->id : null;
         $this->group_id = $organization ? $organization->group_id : null;
     } 

    public function rules(): array
    {
        $parameters = [];
        $response = BookHelper::fetchBookDocNoAndParameters($this->input('book_id'), $this->input('document_date'));
        if ($response['status'] === 200) {
            $parameters = json_decode(json_encode($response['data']['parameters']), true);
        }
        $mrnId = $this->route('id');
        $rules = [
            'book_id' => 'required',
            'document_number' => 'required',
            'document_date' => 'required|date',
            'header_store_id' => 'required',
            'sub_store_id' => 'required',
            'vendor_id' => 'required',
            'currency_id' => 'required',
            'payment_term_id' => 'required',
            'gate_entry_no' => [
                'nullable',
                'max:50',
                Rule::unique('erp_mrn_headers')
                    ->where(function ($query) {
                        return $query
                            ->where('group_id', $this->group_id)
                            ->where('organization_id', $this->organization_id)
                            ->whereNull('deleted_at');
                    })
                    ->ignore($mrnId), // ignore when updating
            ],
            'gate_entry_date' => 'nullable|date',
            'eway_bill_no' => 'nullable|max:50',
            'consignment_no' => 'nullable|max:50',
            'supplier_invoice_no' => [
                'nullable',
                'max:50',
                Rule::unique('erp_mrn_headers')
                    ->where(function ($query) {
                        return $query
                            ->where('group_id', $this->group_id)
                            ->where('organization_id', $this->organization_id)
                            ->whereNull('deleted_at');
                    })
                    ->ignore($mrnId), // ignore when updating
            ],
            'supplier_invoice_date' => 'nullable|date',
            'transporter_name' => 'nullable|max:50',
            'vehicle_no' => [
                'nullable',
                'regex:/^[A-Z]{2}[0-9]{2}[A-Z]{0,3}[0-9]{4}$/'
            ],
            'remarks' => 'nullable|max:500',
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
                if($mrnId) {
                    $rules['document_number'] = 'required|unique:erp_mrn_headers,document_number,' . $mrnId;
                } else {
                    $rules['document_number'] = 'required|unique:erp_mrn_headers,document_number';
                }
            }
        }
        $rules['components.*.attr_group_id.*.attr_name'] = 'required';
        $rules['component_item_name.*'] = 'required';
        $rules['components.*.order_qty'] = 'required|numeric|min:0.01';
        if ($this->input('components.*.is_inspection') === 0) {
            $rules['components.*.accepted_qty'] = 'required|numeric|min:0.01';
        }
        $rules['components.*.rate'] = 'required|numeric|min:0.01';
        $rules['components.*.remark'] = 'nullable|max:250';
        
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
            'document_date.in' => 'The document date must be today.',
            'document_date.required' => 'The document date is required.',
            'document_date.date' => 'Please enter a valid date for the document date.',
            'document_date.after_or_equal' => 'The document date cannot be in the past.',
            'document_date.before_or_equal' => 'The document date cannot be in the future.',
            'header_store_id.required' => 'Location is required',
            'sub_store_id.required' => 'Store is required',
            'gate_entry_no.required' => 'Gate Entry No is required.',
            'gate_entry_no.unique' => 'Gate Entry No is unique.',
            'gate_entry_date.required' => 'Gate Entry Date is required.',
            'eway_bill_no.required' => 'Eway Bill No is required.',
            'consignment_no.required' => 'Consignment No is required.',
            'supplier_invoice_no.required' => 'Supplier Invoice No is required.',
            'supplier_invoice_no.unique' => 'Supplier Invoice No is unique.',
            'supplier_invoice_date.required' => 'Supplier Invoice Date is required.',
            'transporter_name.required' => 'Transporter Name is required.',
            'vehicle_no.required' => 'Vehicle number is required.',
            'vehicle_no.regex' => 'Invalid vehicle number format. Example: MH12AB1234',
            'remarks.required' => 'Remark is required.',
            'item_code.required' => 'The product code is required.',
            'uom_id' => 'The unit of measure must be a string.',
            'component_item_name.*.required' => 'Required',
            'components.*.order_qty.required' => 'Order Qty is required',
            'components.*.order_qty.numeric' => 'Order Qty must be a number.',
            'components.*.order_qty.gt' => 'Order Qty must be greater than zero.',
            'components.*.accepted_qty.required' => 'Accepted Qty is required',
            'components.*.accepted_qty.numeric' => 'Accepted Qty must be a number.',
            'components.*.accepted_qty.gt' => 'Accepted Qty must be greater than zero.',
            'components.*.rate.required' => 'Rate is required',
            'components.*.rate.numeric' => 'Rate must be a number.',
            'components.*.rate.gt' => 'Rate must be greater than zero.',
            'components.*.attr_group_id.*.attr_name.required' => 'Select Attribute',
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
            }
        });


        $validator->after(function ($validator) {
            $components = $this->input('components', []);
            $items = [];
            foreach ($components as $key => $component) {
                $itemId = $component['item_id'] ?? null;
                $uomId = $component['uom_id'] ?? null;
                $mrnItemId = $component['mrn_detail_id'] ?? null;

                // $piItemId = $component['pi_item_id'] ?? null;
                $mrnItemId = $component['mrn_detail_id'] ?? null;
                if ($itemId) {
                    $mrnItem = MrnDetail::find($mrnItemId);
                    if ($mrnItemId) {
                        $mrnItem = MrnDetail::find($mrnItemId);
                        if ($mrnItem) {
                            $minOrderQty = $mrnItem->purchase_bill_qty;
                            $inputQty = $component['accepted_qty'] ?? 0;
                            if ($inputQty < $minOrderQty) {
                                $validator->errors()->add("components.$key.accepted_qty", "Quantity can't be less than Purchase Bill.");
                            }
                        }
                    }

                    $selectedAttributes = [];
                    if(isset($component['attr_group_id']) && count($component['attr_group_id'])) {
                        foreach($component['attr_group_id'] as $k => $attr_group) {
                            $ia = ItemAttribute::where('item_id',$itemId)
                                            ->where('attribute_group_id',$k)
                                            ->first();
                            $selectedAttributes[] = ['attribute_id' => @$ia->id, 'attribute_value' => intval(@$attr_group['attr_name'])];
                        }
                    }

                    $balanceQty = PoItem::where('id',$mrnItem->purchase_order_item_id ?? 0)
                        ->where('item_id',$itemId)
                        ->where('uom_id',operator: $uomId)
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
                        ->selectRaw('SUM(order_qty - grn_qty) as balance_qty')
                        ->value('balance_qty') ?? 0;
                    
                    if($mrnItem) {
                        $inputQty = (floatval($component['accepted_qty']) - $mrnItem->accepted_qty) ?? 0;
                    } else {
                        $inputQty = floatval($component['accepted_qty']) ?? 0;
                    }
                    if($mrnItem && $mrnItem->purchase_order_item_id){
                        if($inputQty > $balanceQty) {
                            $validator->errors()->add("components.$key.accepted_qty", "MRN is more than PO qty.");
                        }
                    }
                }
            }
        });
    }
}
