<?php
namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

use App\Helpers\Helper;
use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;

use App\Models\Item;
use App\Models\PoItem;
use App\Models\PbDetail;
use App\Models\MrnDetail;
use App\Models\NumberPattern;
use App\Models\ItemAttribute;

class PbRequest extends FormRequest
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
            'vendor_id' => 'required',
            'currency_id' => 'required',
            'payment_term_id' => 'required',
            'supplier_invoice_no' => 'nullable|max:50',
            'supplier_invoice_date' => 'nullable|date',
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
                    $rules['document_number'] = 'required|unique:erp_pb_headers,document_number,' . $mrnId;
                } else {
                    $rules['document_number'] = 'required|unique:erp_pb_headers,document_number';
                }
            }
        }
        $rules['components.*.attr_group_id.*.attr_name'] = 'required';
        $rules['component_item_name.*'] = 'required';
        $rules['components.*.accepted_qty'] = 'required|numeric|min:0.01';
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
            'supplier_invoice_no.required' => 'Supplier Invoice No is required.',
            'supplier_invoice_date.required' => 'Supplier Invoice Date is required.',
            'remarks.required' => 'Remark is required.',
            'item_code.required' => 'The product code is required.',
            'uom_id' => 'The unit of measure must be a string.',
            'component_item_name.*.required' => 'Required',
            'components.*.accepted_qty.required' => 'Accepted Qty is required',
            'components.*.accepted_qty.numeric' => 'Accepted Qty must be a number.',
            'components.*.rate.required' => 'Rate is required',
            'components.*.rate.numeric' => 'Rate must be a number.',
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
                $pbItemId = $component['detail_id'] ?? null;

                $pbItemId = $component['detail_id'] ?? null;
                if ($itemId) {
                    $pbItem = PbDetail::find($pbItemId);
                    $selectedAttributes = [];
                    if(isset($component['attr_group_id']) && count($component['attr_group_id'])) {
                        foreach($component['attr_group_id'] as $k => $attr_group) {
                            $ia = ItemAttribute::where('item_id',$itemId)
                                            ->where('attribute_group_id',$k)
                                            ->first();
                            $selectedAttributes[] = ['attribute_id' => @$ia->id, 'attribute_value' => intval($attr_group['attr_name'])];
                        }
                    }

                    $balanceQty = MrnDetail::where('id',$pbItem->mrn_detail_id ?? 0)
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
                        ->selectRaw('SUM(accepted_qty - purchase_bill_qty) as balance_qty')
                        ->value('balance_qty') ?? 0;
                    
                    if($pbItem) {
                        $inputQty = (floatval($component['accepted_qty']) - $pbItem->accepted_qty) ?? 0;
                    } else {
                        $inputQty = floatval($component['accepted_qty']) ?? 0;
                    }
                    if($pbItem && $pbItem->mrn_detail_id){
                        if($inputQty > $balanceQty) {
                            $validator->errors()->add("components.$key.accepted_qty", "PB is more than MRN qty.");
                        }
                    }
                }
            }
        });
    }
}
