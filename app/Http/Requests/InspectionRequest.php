<?php
namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

use App\Helpers\Helper;
use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;

use App\Models\Item;
use App\Models\MrnDetail;
use App\Models\NumberPattern;
use App\Models\ItemAttribute;
use App\Models\InspectionDetail;

class InspectionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
    */

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
            'supplier_invoice_no' => 'nullable|max:50',
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
                    $rules['document_number'] = 'required|unique:erp_purchase_return_headers,document_number,' . $mrnId;
                } else {
                    $rules['document_number'] = 'required|unique:erp_purchase_return_headers,document_number';
                }
            }
        }
        $rules['components.*.attr_group_id.*.attr_name'] = 'required';
        $rules['component_item_name.*'] = 'required';
        $rules['components.*.order_qty'] = 'required|numeric|min:0.01';
        $rules['components.*.accepted_qty'] = 'required|numeric|min:0.01';
        $rules['components.*.remark'] = 'nullable|max:250';
        $rules['components.*.attr_group_id.*.attr_name'] = 'required';
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
            'sub_store_id.required' => 'Main Store is required',
            'supplier_invoice_no.required' => 'Supplier Invoice No is required.',
            'supplier_invoice_date.required' => 'Supplier Invoice Date is required.',
            'vehicle_no.required' => 'Vehicle number is required.',
            'vehicle_no.regex' => 'Invalid vehicle number format. Example: MH12AB1234',
            'remarks.required' => 'Remark is required.',
            'item_code.required' => 'The product code is required.',
            'uom_id' => 'The unit of measure must be a string.',
            'component_item_name.*.required' => 'Required',
            'components.*.order_qty.required' => 'Inspection Qty is required',
            'components.*.order_qty.numeric' => 'Inspection Qty must be a number.',
            'components.*.order_qty.gt' => 'Inspection Qty must be greater than zero.',
            'components.*.accepted_qty.required' => 'Accepted Qty is required',
            'components.*.accepted_qty.numeric' => 'Accepted Qty must be a number.',
            'components.*.accepted_qty.gt' => 'Accepted Qty must be greater than zero.',
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
            $isRejectedQty = 0;
            foreach ($components as $key => $component) {
                if (!empty($component['rejected_qty']) && $component['rejected_qty'] > 0) {
                    $isRejectedQty = 1;
                }

                $itemId = $component['item_id'] ?? null;
                $uomId = $component['uom_id'] ?? null;
                $inspectionItemId = $component['detail_id'] ?? null;

                $inspectionItemId = $component['detail_id'] ?? null;
                if ($itemId) {
                    $inspectionItem = InspectionDetail::find($inspectionItemId);
                    $selectedAttributes = [];
                    if(isset($component['attr_group_id']) && count($component['attr_group_id'])) {
                        foreach($component['attr_group_id'] as $k => $attr_group) {
                            $ia = ItemAttribute::where('item_id',$itemId)
                                            ->where('attribute_group_id',$k)
                                            ->first();
                            $selectedAttributes[] = ['attribute_id' => @$ia->id, 'attribute_value' => intval(@$attr_group['attr_name'])];
                        }
                    }

                    $balanceQty = MrnDetail::where('id',$inspectionItem->mrn_detail_id ?? 0)
                        ->where('item_id',$itemId)
                        ->where('uom_id',operator: $uomId)
                        ->where(function($piItemQuery) use($selectedAttributes) {
                            if(count($selectedAttributes)) {
                                $piItemQuery->whereHas('attributes',function($piAttributeQuery) use($selectedAttributes) {
                                    foreach($selectedAttributes as $piAttribute) {
                                        $piAttributeQuery->where('item_attribute_id',$piAttribute['attribute_id'])
                                        ->where('attr_value',$piAttribute['attribute_value']);
                                    }
                                });
                            }
                        })
                        ->selectRaw('SUM(order_qty - inspection_qty) as balance_qty')
                        ->value('balance_qty') ?? 0;
                    if($inspectionItem) {
                        $inputQty = (floatval($component['order_qty']) - $inspectionItem->order_qty) ?? 0;
                    } else {
                        $inputQty = floatval($component['order_qty']) ?? 0;
                    }
                    if($inspectionItem && $inspectionItem->mrn_detail_id){
                        if($inputQty > $balanceQty) {
                            $validator->errors()->add("components.$key.order_qty", "Inspection qty. is more than MRN qty.");
                        }
                    }
                }
            }
            if($isRejectedQty){
                if (!$this->filled('rejected_sub_store_id')) {
                    $validator->errors()->add("rejected_sub_store_id", "Rejected store should be mandatory for rejected qty.");
                }
            }
            
        });
    }
}
