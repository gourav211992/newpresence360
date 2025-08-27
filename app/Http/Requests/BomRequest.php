<?php

namespace App\Http\Requests;

use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\ItemHelper;
use App\Models\Bom;
use App\Models\Item;
use App\Models\NumberPattern;
use App\Models\ItemAttribute;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ProcessesComponentJson;

class BomRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        $components = $this->processComponentJson();
        if (!is_null($components)) {
            $this->merge(['components' => $components]);
        }
        $bomId = $this->route('id');
        $parameters = [];
        $response = BookHelper::fetchBookDocNoAndParameters($this->input('book_id'), $this->input('document_date'));
        if ($response['status'] === 200) {
            $parameters = json_decode(json_encode($response['data']['parameters']), true);
        }
        $rules = [
            'book_id' => 'required',
            'document_date' => 'required|date',
            'document_number' => 'required',
            'item_code' => 'required|string|max:255',
            'production_route_id' => 'required',
            'uom_id' => 'required|max:255'
        ];
        $rules['production_type'] = $this->type == ConstantHelper::BOM_SERVICE_ALIAS ? 'required' : 'nullable';
        $rules['customer'] = $this->type == ConstantHelper::BOM_SERVICE_ALIAS ? 'nullable' : 'required';
        $today = now()->toDateString();
        $isPast = false;
        $isFeature = false;
        $futureAllowed = isset($parameters['future_date_allowed']) && is_array($parameters['future_date_allowed']) && in_array('yes', array_map('strtolower', $parameters['future_date_allowed']));
        $backAllowed = isset($parameters['back_date_allowed']) && is_array($parameters['back_date_allowed']) && in_array('yes', array_map('strtolower', $parameters['back_date_allowed']));
        $sectionRequired = isset($parameters['section_required']) && is_array($parameters['section_required']) && in_array('yes', array_map('strtolower', $parameters['section_required']));
        $subSectionRequired = isset($parameters['sub_section_required']) && is_array($parameters['sub_section_required']) && in_array('yes', array_map('strtolower', $parameters['sub_section_required']));
        $stationRequired = true;
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
        if ($this->filled('book_id')) {
            $user = Helper::getAuthenticatedUser();
            $numPattern = NumberPattern::where('organization_id', $user->organization_id)
                        ->where('book_id', $this->book_id)
                        ->orderBy('id', 'DESC')
                        ->first();
            if ($numPattern && $numPattern->series_numbering == 'Manually') {
                if($bomId) {
                    $rules['document_number'] = 'required|unique:erp_boms,document_number,' . $bomId;
                } else {
                    $rules['document_number'] = 'required|unique:erp_boms,document_number';
                }
            }
        }
        $rules['components.*.vendor_id'] = 'nullable';
        $rules['component_item_name.*'] = 'required';
        $rules['components.*.qty'] = 'required|numeric|min:0.000001';
        $rules['components.*.item_cost'] = 'numeric';
        $rules['components.*.station_name'] = $stationRequired ? 'required' : 'nullable';
        $rules['components.*.section_name'] = $sectionRequired ? 'required' : 'nullable';
        if($sectionRequired) {
            $rules['components.*.sub_section_name'] = $subSectionRequired ? 'required' : 'nullable';
        }
        $headItem = Item::find($this->item_id ?? null);

        if ($headItem && $headItem?->itemAttributes?->count() > 0) {
            $rules['attributes.*.attr_group_id.*.attr_name'] = 'required';
        }

        $rules['components.*.uom_id'] = 'required';
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
        if(isset($this->all()['instructions'])) {
            $rules['instructions.*.station_name'] = 'required';
            // $rules['instructions.*.section_id'] = 'required';
            // $rules['instructions.*.sub_section_id'] = 'required';
            $rules['instructions.*.instructions'] = 'required';
        }
        return $rules;
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
        $itemId = $this->input('item_id');
        $itemCustomerId = $this->input('customer_id');
        $attributes = $this->input('attributes') ?? [];
        // $bomId = $this->route('id');

        $moduleType = $this->type ?? null;
        if ($itemId) {
            $selectedAttributes = [];
            if(count($attributes)) {
                foreach($attributes as $k => $attribute) {
                    $attr_group = array_values($attribute['attr_group_id']);
                    $ia = ItemAttribute::where('item_id',$itemId)
                                    ->where('attribute_group_id',@$attr_group[0]['attr_group_id'])
                                    ->first();
                    $selectedAttributes[] = ['attribute_id' => $ia?->id, 'attribute_value' => intval(@$attr_group[0]['attr_name'])];
                }
            }
            if($this->action_type !== 'amendment') {
                $quoteBomId = $this->quote_bom_id ?? null;
                $bomExists = Bom::where('item_id', $itemId)
                ->where('type',$moduleType)
                ->where(function ($query) use ($itemCustomerId,$moduleType) {
                    if ($moduleType == 'qbom') {
                        $query->where('customer_id', $itemCustomerId);
                    }
                })
                ->where('status', ConstantHelper::ACTIVE)
                ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_SUBMITTED)
                ->first();
                // $bomExists = Bom::where('item_id', $itemId)
                //     ->where(function ($query) use ($itemCustomerId, $quoteBomId) {
                //         if ($itemCustomerId) {
                //             $query->where('customer_id', $itemCustomerId);
                //         }
                //         if($quoteBomId) {
                //             $query->whereNotIn('id', [$quoteBomId]);
                //         }
                //     })
                //     ->where('status', ConstantHelper::ACTIVE)
                //     ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_SUBMITTED)
                //     ->first();
                if ($bomExists) {
                    $validator->errors()->add("item_code", "Bom already exists for this item.");
                }
            }
        }
        # For component item
        $type = ['Finished Goods', 'WIP/Semi Finished'];
        foreach ($this->input('components', []) as $index => $component) {
            $item_id = $component['item_id'] ?? null;
            $item = Item::find($item_id);
            $filteredSubTypes = $item?->subTypes->filter(function ($subType) use ($type) {
                return in_array($subType->name, $type);
            });
            if(isset($item_id) && $filteredSubTypes && $filteredSubTypes->isNotEmpty()) {
                if(isset($component['attr_group_id']) && count($component['attr_group_id'])) {
                    $selectedAttributes = [];
                    foreach($component['attr_group_id'] as $k => $attr_group) {
                        $ia = ItemAttribute::where('item_id',$item_id)
                                        ->where('attribute_group_id',$k)
                                        ->first();
                        $selectedAttributes[] = ['attribute_id' => $ia->id, 'attribute_value' => intval($attr_group['attr_name'])];
                    }
                    $bomExists = ItemHelper::checkItemBomExists($item_id, $selectedAttributes,$moduleType);
                    if (!$bomExists['bom_id']) {
                        $validator->errors()->add("component_item_name.".$index, $bomExists['message']);
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
            'components.*.superceeded_cost' => 'Required',
            'components.*.attr_group_id.*.attr_name.required' => 'Select Attribute',
            'attributes.*.attr_group_id.*.attr_name.required' => 'Select Attribute',
            'components.*.station_name.required' => 'Required',
            'components.*.section_name.required' => 'Required',
            'components.*.sub_section_name.required' => 'Required',
            'document_date.in' => 'The document date must be today.',
            'document_date.required' => 'The document date is required.',
            'document_date.date' => 'Please enter a valid date for the document date.',
            'document_date.after_or_equal' => 'The document date cannot be in the past.',
            'document_date.before_or_equal' => 'The document date cannot be in the future.',
        ];
    }
}
