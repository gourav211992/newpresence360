<?php
namespace App\Http\Requests;

use Auth;
use App\Helpers\Helper;
use App\Helpers\ConstantHelper;
use Illuminate\Foundation\Http\FormRequest;

use App\Models\Item;
use App\Models\PoItem;
use App\Models\MrnDetail;
use App\Models\NumberPattern;
use App\Models\ItemAttribute;
use Illuminate\Validation\Rule;
use App\Traits\ProcessesComponentJson;

class EditMaterialReceiptRequest extends FormRequest
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

    protected $organization_id;
    protected $group_id;

    protected function prepareForValidation(): void
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization ? $organization->id : null;
        $this->group_id = $organization ? $organization->group_id : null;
        $this->processComponentJson('components_json');
    }

    public function rules(): array
    {
        // $bomId = $this->route('id');
        $mrnId = $this->route('id');
        $rules = [
            'book_id' => 'required',
            'document_number' => 'nullable|max:50', // Default rule for document_number
            'header_store_id' => 'required',
            'sub_store_id' => 'required',
            'vendor_id' => 'nullable',
            'currency_id' => 'nullable',
            'payment_term_id' => 'nullable',
            // 'gate_entry_no' => [
            //     'nullable',
            //     'max:50',
            //     Rule::unique('erp_mrn_headers')
            //         ->where(function ($query) {
            //             return $query
            //                 ->where('group_id', $this->group_id)
            //                 ->where('organization_id', $this->organization_id)
            //                 ->whereNull('deleted_at');
            //         })
            //         ->ignore($mrnId), // ignore when updating
            // ],
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
            'remarks' => 'nullable|max:500',
            'vehicle_no' => [
                'nullable',
                'regex:/^[A-Z]{2}[0-9]{2}[A-Z]{0,3}[0-9]{4}$/'
            ],
        ];

        // Check the condition only if book_id is present
        if ($this->filled('book_id')) {
            $user = Helper::getAuthenticatedUser();
            $numPattern = NumberPattern::where('organization_id', $user->organization_id)
                        ->where('book_id', $this->book_id)
                        ->orderBy('id', 'DESC')
                        ->first();

            // Update document_number rule based on the condition
            if ($numPattern && $numPattern->series_numbering == 'Manually') {
                $rules['document_number'] = 'nullable|unique:erp_mrn_headers,document_number';
            }
        }

        $rules['components.*.attr_group_id.*.attr_name'] = 'required';
        $rules['component_item_name.*'] = 'required';
        $rules['components.*.order_qty'] = 'required|numeric|min:0.01';
        // if ($this->input('components.*.is_inspection') === 0) {
        //     $rules['components.*.accepted_qty'] = 'required|numeric|min:0.01';
        // }
        $rules['components.*.rate'] = 'required|numeric|min:0.01';
        $rules['components.*.remark'] = 'nullable|max:250';

        foreach ($this->input('components', []) as $index => $component) {
            $item_id = $component['item_id'] ?? null;
            $item = Item::find($item_id);
            // $index = $index + 1;
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
            'header_store_id.required' => 'Location is required',
            'sub_store_id.required' => 'Store is required',
            'gate_entry_no.required' => 'Gate Entry No is required.',
            'gate_entry_date.required' => 'Gate Entry Date is required.',
            'eway_bill_no.required' => 'Eway Bill No is required.',
            'consignment_no.required' => 'Consignment No is required.',
            'supplier_invoice_no.required' => 'Supplier Invoice No is required.',
            'supplier_invoice_date.required' => 'Supplier Invoice Date is required.',
            'transporter_name.required' => 'Transporter Name is required.',
            'vehicle_no.required' => 'Vehicle number is required.',
            'vehicle_no.regex' => 'Invalid vehicle number format. Example: MH12AB1234',
            'remarks.required' => 'Remark is required.',
            'item_code.required' => 'The product code is required.',
            'status.required' => 'The status field is required.',
            'uom_id' => 'The unit of measure must be a string.',
            'component_item_name.*.required' => 'Required',
            'components.*.order_qty.required' => 'Order Qty is required',
            // 'components.*.accepted_qty.required' => 'Accepted Qty is required',
            'components.*.rate.required' => 'Rate is required',
            'components.*.attr_group_id.*.attr_name.required' => 'Select Attribute',
            'components.*.order_qty.numeric' => 'Order Qty must be integer',
            // 'components.*.accepted_qty.numeric' => 'Accepted Qty must be integer',
            'components.*.rate.numeric' => 'Rate must be integer',
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
            $referenceType = $this->input('reference_type');
            $items = [];
            foreach ($components as $key => $component) {
                $itemValue = floatval($component['item_total_cost'] ?? 0);
                if ($itemValue < 0) {
                    $validator->errors()->add("components.$key.item_name", "Item total can't be negative.");
                }

                $itemId = $component['item_id'] ?? null;
                $uomId = $component['uom_id'] ?? null;
                $soId = $component['so_id'] ?? null;

                $poId = match ($referenceType) {
                    'po' => $component['purchase_order_id'] ?? null,
                    'jo' => $component['job_order_id'] ?? null,
                    default => null,
                };

                $attributes = [];
                foreach ($component['attr_group_id'] ?? [] as $groupId => $attrName) {
                    $attr_id = $groupId;
                    $attr_value = $attrName['attr_name'] ?? null;
                    if ($attr_id && $attr_value !== null) {
                        $attributes[] = [
                            'attr_id' => $attr_id,
                            'attr_value' => $attr_value,
                        ];
                    }
                }

                $currentItem = [
                    'item_id' => $itemId,
                    'uom_id' => $uomId,
                    'so_id' => $soId,
                    'po_id' => $poId,
                    'attributes' => $attributes,
                ];

                foreach ($items as $existingItem) {
                    if (
                        $existingItem['item_id'] === $currentItem['item_id'] &&
                        $existingItem['uom_id'] === $currentItem['uom_id'] &&
                        $existingItem['so_id'] === $currentItem['so_id'] &&
                        $existingItem['po_id'] === $currentItem['po_id'] &&
                        $this->attributesEqual($existingItem['attributes'], $currentItem['attributes'])
                    ) {
                        $validator->errors()->add("components.$key.item_id", "Duplicate item!");
                        return;
                    }
                }

                $items[] = $currentItem;
            }
        });
    }

    protected function attributesEqual(array $a, array $b): bool
    {
        if (count($a) !== count($b)) {
            return false;
        }

        // Sort by attr_id and attr_value for consistent comparison
        usort($a, fn($x, $y) => [$x['attr_id'], $x['attr_value']] <=> [$y['attr_id'], $y['attr_value']]);
        usort($b, fn($x, $y) => [$x['attr_id'], $x['attr_value']] <=> [$y['attr_id'], $y['attr_value']]);

        foreach ($a as $i => $attrA) {
            $attrB = $b[$i] ?? null;
            if (
                !$attrB ||
                $attrA['attr_id'] != $attrB['attr_id'] ||
                $attrA['attr_value'] != $attrB['attr_value']
            ) {
                return false;
            }
        }

        return true;
    }
}
