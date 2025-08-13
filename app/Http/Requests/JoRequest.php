<?php

namespace App\Http\Requests;

use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Models\Bom;
use App\Models\Item;
use App\Models\JobOrder\JoProduct;
use App\Models\NumberPattern;
use App\Models\Organization;
use App\Models\PwoSoMapping;
use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\FlareClient\Flare;
use App\Traits\ProcessesComponentJson;

class JoRequest extends FormRequest
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
        $poId = $this->route('id');

        $rules = [
            'book_id' => 'required',
            'exchange_rate' => 'required',
            'document_date' => 'required|date',
            'document_number' => 'required',
            'vendor_id' => 'required',
            'currency_id' => 'required',
            'payment_term_id' => 'required',
            'job_order_type' => 'required',
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
                    $rules['document_number'] = 'required|unique:erp_job_orders,document_number,' . $poId;
                } else {
                    $rules['document_number'] = 'required|unique:erp_job_orders,document_number';
                }
            }
        }
        $rules['component_item_name.*'] = 'required';
        $rules['components.*.qty'] = 'required|numeric|min:0.000001';
        $rules['components.*.sow'] = 'required';        
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
        $authUser = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $authUser->organization_id)->first();
        $organizationId = $organization ?-> id ?? null;

        $validator->after(function ($validator) use($organizationId) {
            #check vendor location mapping
            $vendorId = $this->input('vendor_id');
            $locationId = $this->input('store_id');
            if ($vendorId && $locationId) {
                $vendor = Vendor::with(['locations' => function ($query) use ($organizationId, $locationId) {
                    $query->where('organization_id', $organizationId)
                    ->where('location_id', $locationId);
                }])->find($vendorId);
                if (!$vendor || $vendor->locations->isEmpty()) {
                    $validator->errors()->add('vendor_id', 'Vendor is not mapped to the selected store.');
                }
            }

            $components = $this->input('components', []);
            $items = [];
            foreach ($components as $key => $component) {
                
                $itemValue = floatval($component['item_total_cost']);
                if($itemValue < 0) {
                    $validator->errors()->add("components.$key.item_name", "Item total can't be negative.");
                }
                $itemId = $component['item_id'] ?? null;

                $bomExists = Bom::withDefaultGroupCompanyOrg()
                ->where('item_id', $itemId)
                ->where('type', ConstantHelper::BOM_SERVICE_ALIAS)
                ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
                ->exists();

                if(!$bomExists) {
                    $validator->errors()->add("components.$key.item_name", "Bom not exist.");
                }

                $bomExists = Bom::withDefaultGroupCompanyOrg()
                ->where('item_id', $itemId)
                ->where('type', ConstantHelper::BOM_SERVICE_ALIAS)
                ->whereIn('production_type', ['Job Work'])
                ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
                ->exists();
                
                if (!$bomExists) {
                    $validator->errors()->add("components.$key.item_name", "Only products with production type Job Work are allowed.");
                }

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
                        );
                        return;
                    }
                }
                $items[] = $currentItem;

                # Backup update pwosomapping
                $pwoSoMappingId = $component['pwo_so_mapping_id'] ?? null;
                $joProductId = $component['jo_product_id'] ?? null;
                if ($pwoSoMappingId) {
                    $mapping = PwoSoMapping::find($pwoSoMappingId);
                    if ($mapping) {
                        $assignedQty = JoProduct::where('pwo_so_mapping_id', $pwoSoMappingId)
                            ->when($joProductId, fn($q) => $q->where('id', '!=', $joProductId))
                            ->sum('order_qty');
                        $availableQty = $mapping->qty - $assignedQty;
                        $inputQty = $component['qty'] ?? 0;
                        if (floatval($inputQty) > floatval($availableQty)) {
                            $validator->errors()->add("components.$key.item_code", "Assigned quantity exceeds available PWO SO qty ({$availableQty}).");
                        }
                    }
                }


                // Short close resctriction
                // $poItemId = $component['po_item_id'] ?? null;
                // $poItem = JoItem::find($poItemId);
                // if(floatval($component['short_close_qty']) && $poItem) {
                //     if(floatval($poItem->order_qty) < max($poItem->grn_qty,$poItem->invoice_quantity) + floatval($component['short_close_qty'])) {
                //         $validator->errors()->add("components.$key.short_close_qty", "Short close qty less then PO qty");
                //     }
                // }
            }
        });
    }
}
