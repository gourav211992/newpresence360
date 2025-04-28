<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\AtLeastOneRequired;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use Auth;


class ItemRequest extends FormRequest
{
   
    public function authorize(): bool
    {
        return true;
    }

    protected $organization_id;

    protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization ? $organization->id : null;
        $this->group_id = $organization ? $organization->group_id : null; 
    }

    public function rules(): array
    {
        $itemId = $this->route('id'); 


        return [
            'type' => 'required|string|in:Goods,Service',
            'hsn_id' => 'required|exists:erp_hsns,id',
            'category_id' => 'required|exists:erp_categories,id',
            'subcategory_id' => 'required|exists:erp_categories,id',
            'group_id' => 'nullable|exists:groups,id', 
            'company_id' => 'nullable', 
            'organization_id' => 'nullable|exists:organizations,id', 
            'service_type' => 'nullable',
            'item_code_type'=>'nullable',
            'book_id'=>'nullable',
            'book_code'=>'nullable',
            'item_code' => [
            'required',
            'string',
            'max:255',
            Rule::unique('erp_items', 'item_code')
                ->ignore($itemId)  
                ->where('group_id',  $this->group_id) 
                ->whereNull('deleted_at'), 
           ],
           'item_name' => [
                'required',
                'string',
                'max:200',
                Rule::unique('erp_items', 'item_name')
                    ->ignore($itemId)  
                    ->where('group_id', $this->group_id) 
                    ->whereNull('deleted_at'),
            ],
            'item_initial'=>[
                'required',
                'string',
                'max:10',
            ],

            'uom_id' => 'required|max:255',
            'item_remark' => 'nullable', 
            'cost_price' =>'nullable|regex:/^[0-9,]*(\.[0-9]{1,2})?$/|min:0',
            'sell_price' =>'nullable|regex:/^[0-9,]*(\.[0-9]{1,2})?$/|min:0',
            'sub_types' => 'required_if:type,Goods|array',
            'storage_type' => 'nullable',
            'sub_types.*' => 'integer|exists:mysql_master.erp_sub_types,id',
            'min_stocking_level' => 'nullable|integer|min:0',
            'max_stocking_level' => 'nullable|integer|min:0|gte:min_stocking_level',
            'reorder_level' => 'nullable|integer|min:0',
            'minimum_order_qty' => 'nullable|integer|min:1',
            'lead_days' => 'nullable|integer|min:1|max:365|gte:safety_days',
            'safety_days' => 'nullable|integer|min:1|max:365|lte:shelf_life_days',
            'shelf_life_days' => 'nullable|integer|min:1|max:365',
            'po_positive_tolerance' => 'nullable|numeric|gt:0|max:100',
            'po_negative_tolerance' => 'nullable|numeric|gt:0|max:100',
            'so_positive_tolerance' => 'nullable|numeric|gt:0|max:100',
            'so_negative_tolerance' => 'nullable|numeric|gt:0|max:100',
            'status' => 'nullable',

            'alternate_uoms' => 'nullable|array',
            'alternate_uoms.*.id' => 'nullable',
            'alternate_uoms.*.uom_id' => 'nullable|exists:erp_units,id',
            'alternate_uoms.*.conversion_to_inventory' => 'nullable|numeric',
            'alternate_uoms.*.is_selling' => 'nullable',
            'alternate_uoms.*.is_purchasing' => 'nullable',
            'alternate_uoms.*.cost_price' =>'nullable|regex:/^[0-9,]*(\.[0-9]{1,2})?$/|min:0', 
            'alternate_uoms.*.sell_price' => 'nullable|regex:/^[0-9,]*(\.[0-9]{1,2})?$/|min:0', 

            'approved_customer' => 'nullable|array',
            'approved_customer.*.id' => 'nullable',
            'approved_customer.*.customer_id' => 'nullable|exists:erp_customers,id',
            'approved_customer.*.customer_code' => 'nullable|string|max:255',
            'approved_customer.*.item_code' => 'nullable|string|max:255',
            'approved_customer.*.item_name' => 'nullable|string|max:100',
            'approved_customer.*.part_number' => 'nullable|string|max:255',
            'approved_customer.*.item_details' => 'nullable|string',
            'approved_customer.*.sell_price' => 'nullable|regex:/^[0-9,]*(\.[0-9]{1,2})?$/|min:0',
            'approved_customer.*.uom_id' => 'nullable',
    
            'approved_vendor' => 'nullable|array',
            'approved_vendor.*.id' => 'nullable',
            'approved_vendor.*.vendor_id' => 'nullable|exists:erp_vendors,id',
            'approved_vendor.*.vendor_code' => 'nullable|string|max:255',
            'approved_vendor.*.item_code' => 'nullable|string|max:255',
            'approved_vendor.*.item_name' => 'nullable|string|max:100',
            'approved_vendor.*.part_number' => 'nullable|string|max:255',
            'approved_vendor.*.item_details' => 'nullable|string',
            'approved_vendor.*.cost_price' => 'nullable|regex:/^[0-9,]*(\.[0-9]{1,2})?$/|min:0', 
            'approved_vendor.*.uom_id' => 'nullable',

            'notes' => 'nullable|array',
            'notes.remark' => 'nullable|string|max:255',

            'attributes' => 'nullable|array',
            'attributes.*.id' => 'nullable',
            'attributes.*.attribute_group_id' => 'nullable|exists:erp_attribute_groups,id',
            'attributes.*.attribute_id' => 'nullable|exists:erp_attributes,id',
            'attributes.*.all_checked' => 'nullable|boolean', 
            'attributes.*.required_bom' => 'nullable|boolean',

            'alternateItems' => 'nullable|array',
            'alternateItems.*.id' => 'nullable',
            'alternateItems.*.item_code' => [
                'nullable',
                'string',
                'max:255',
            ],
            'alternateItems.*.item_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'item_specifications' => 'nullable|array',
            'item_specifications.*.id' => 'nullable',
            'item_specifications.*.item_id' => 'nullable|exists:erp_items,id',
            'item_specifications.*.group_id' => 'nullable|exists:erp_product_specifications,id',
            'item_specifications.*.specification_id' => 'nullable|exists:erp_product_specification_details,id',
            'item_specifications.*.specification_name' => 'nullable|string',
            'item_specifications.*.value' => 'nullable|string', 
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $attributes = $this->input('attributes', []);
            foreach ($attributes as $index => $attribute) {
                $attributeGroupId = $attribute['attribute_group_id'] ?? null;
                $allChecked = $attribute['all_checked'] ?? null;
                $attributeId = $attribute['attribute_id'] ?? null;
                if (!is_null($attributeGroupId) && (is_null($allChecked) || $allChecked === false) && is_null($attributeId)) {
                    $validator->errors()->add("attributes.{$index}.attribute_id", 'The attribute is required when attribute group is selected.');
                }
            }

            $alternateUoms = $this->input('alternate_uoms', []);
            foreach ($alternateUoms as $index => $alternateUom) {
                $uomId = $alternateUom['uom_id'] ?? null;
                $conversionToInventory = $alternateUom['conversion_to_inventory'] ?? null;
    
                if (!is_null($uomId) && is_null($conversionToInventory)) {
                    $validator->errors()->add("alternate_uoms.{$index}.conversion_to_inventory", 'The conversion  is required when UOM is selected.');
                }
            }
        });

        
    }

    public function messages(): array
    {
        return [
            'type.required' => 'The item type is required.',
            'type.in' => 'The item type must be either Goods or Service.',
            'hsn_id.required' => 'The HSN is required.',
            'hsn_id.exists' => 'The selected HSN is invalid.',
            'category_id.exists' => 'The selected category is invalid.',
            'subcategory_id.exists' => 'The selected subcategory is invalid.',
            'group_id.exists' => 'The selected group is invalid.',
            'organization_id.exists' => 'The selected organization is invalid.',
            'item_code.required' => 'The item code is required.',
            'item_code.unique' => 'The item code has already been taken.',
            'item_name.required' => 'The item name is required.',
            'uom_id.max' => 'The unit of measure must not exceed 255 characters.',
            'min_stocking_level.integer' => 'The minimum stocking level must be an integer.',
            'min_stocking_level.min' => 'The minimum stocking level must be at least 0.',
            'max_stocking_level.integer' => 'The maximum stocking level must be an integer.',
            'max_stocking_level.min' => 'The maximum stocking level must be at least 0.',
            'reorder_level.integer' => 'The reorder level must be an integer.',
            'reorder_level.min' => 'The reorder level must be at least 0.',
            'minimum_order_qty.integer' => 'The minimum order quantity must be an integer.',
            'minimum_order_qty.min' => 'The minimum order quantity must be at least 0.',
            'lead_days.integer' => 'The lead days must be an integer.',
            'lead_days.min' => 'The lead days must be at least 0.',
            'safety_days.integer' => 'The safety days must be an integer.',
            'safety_days.min' => 'The safety days must be at least 0.',
            'shelf_life_days.integer' => 'The shelf life days must be an integer.',
            'shelf_life_days.min' => 'The shelf life days must be at least 0.',
            'po_positive_tolerance.min' => 'Po positive tolerance must be greater than 0.',
            'po_negative_tolerance.min' => 'Po negative tolerance must be greater than 0.',
            'so_positive_tolerance.min' => 'So positive tolerance must be greater than 0.',
            'so_negative_tolerance.min' => 'So negative tolerance must be greater than 0.',

            'alternate_uoms.array' => 'Alternate UOMs must be an array.',
            'alternate_uoms.*.uom_id.exists' => 'The selected alternate UOM is invalid.',
            'alternate_uoms.*.conversion_to_inventory.numeric' => 'The conversion to inventory must be a number.',
            'approved_customer.customer_id.exists' => 'The selected customer is invalid.',
            'approved_customer.item_code.max' => 'The customer item code must not exceed 255 characters.',
            'approved_customer.item_name.max' => 'The customer item name must not exceed 100 characters.',
            'approved_vendor.vendor_id.exists' => 'The selected vendor is invalid.',
            'approved_vendor.item_code.max' => 'The vendor item code must not exceed 255 characters.',
            'approved_vendor.item_name.max' => 'The vendor item name must not exceed 100 characters.',
           
            'attributes.array' => 'Attributes must be an array.',
            'attributes.*.attribute_group_id.required' => 'The attribute group is required.',
            'attributes.*.attribute_group_id.exists' => 'The selected attribute group is invalid.',
            'attributes.*.attribute_id.required' => 'The attribute value is required.',
            'attributes.*.attribute_id.array' => 'The attribute value must be an array.',
            'attributes.*.attribute_id.min' => 'Please select at least one attribute value.',
            'attributes.*.attribute_id.*.integer' => 'Each attribute value must be a valid integer.',
            'attributes.*.attribute_id.*.exists' => 'One or more selected attribute values are invalid.',
            'attributes.*.required_bom.boolean' => 'The required BOM value must be true or false.',
       
            'alternateItems.array' => 'Alternate items must be an array.',
            'alternateItems.*.item_code.max' => 'The alternate item code must not exceed 255 characters.',
            'alternateItems.*.item_name.max' => 'The alternate item name must not exceed 100 characters.',
            'item_specifications.array' => 'Item specifications must be an array.',
            'item_specifications.*.item_id.exists' => 'The selected item specification item is invalid.',
            'item_specifications.*.group_id.exists' => 'The selected item specification group is invalid.',
            'item_specifications.*.specification_id.exists' => 'The selected specification is invalid.',
        ];
 
    }
}
