<?php
namespace App\Http\Requests;

use Auth;

use App\Helpers\Helper;
use App\Helpers\ConstantHelper;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class WhItemMappingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

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
        return [
            'store_id' => ['required', 'integer'],
            'sub_store_id' => ['required', 'integer'],
            'status' => ['nullable', 'string'],
            'details' => ['required', 'array', 'min:1'],

            // Allow update by ID (nullable means it's a new row)
            'details.*.detail_id' => ['nullable', 'integer', 'exists:erp_wh_item_mappings,id'],

            // Required category array
            'details.*.category_id' => ['required', 'array', 'min:1'],
            'details.*.category_id.*' => ['required', 'integer', 'exists:erp_categories,id'],

            // Optional sub-category array
            'details.*.sub_category_id' => ['nullable', 'array'],
            'details.*.sub_category_id.*' => ['nullable', 'integer', 'exists:erp_categories,id'],

            // Optional item array
            'details.*.item_id' => ['nullable', 'array'],
            'details.*.item_id.*' => ['nullable', 'integer', 'exists:erp_items,id'],
        ];
    }


    public function withValidator($validator)
    {
        $details = $this->input('details', []);
        $storeId = $this->input('store_id');
        $subStoreId = $this->input('sub_store_id');

        $combinations = [];

        foreach ($details as $index => $detail) {
            // 1. Add rules for structure levels (anything that's not category/item/detail_id)
            foreach ($detail as $key => $value) {
                if (is_array($value) && !in_array($key, ['category_id', 'sub_category_id', 'item_id', 'detail_id'])) {
                    $validator->addRules([
                        "details.$index.$key" => ['required', 'array', 'min:1'],
                        "details.$index.$key.*" => ['required', 'integer', 'exists:erp_wh_details,id'],
                    ]);
                }
            }

            // 2. Normalize known arrays
            $categoryIds = collect($detail['category_id'] ?? [])->sort()->values()->all();
            $subCategoryIds = collect($detail['sub_category_id'] ?? [])->sort()->values()->all();
            $itemIds = collect($detail['item_id'] ?? [])->sort()->values()->all();

            // 3. Normalize structure_details:
            $structureDetails = collect($detail)
                ->filter(fn($val, $key) => is_array($val) && !in_array($key, ['category_id', 'sub_category_id', 'item_id', 'detail_id']))
                ->map(fn($val) => collect($val)->sort()->values()->all()) // sort array values
                ->sortKeys() // sort the keys alphabetically
                ->toArray();

            // 4. Build stable combination key
            $comboArray = [
                'store_id' => $storeId,
                'sub_store_id' => $subStoreId,
                'category_id' => $categoryIds,
                'sub_category_id' => $subCategoryIds,
                'item_id' => $itemIds,
                'structure_details' => $structureDetails,
            ];

            $comboKey = md5(json_encode($comboArray)); // hashing for safer comparison

            if (in_array($comboKey, $combinations)) {
                $validator->errors()->add("details.$index", 'Duplicate mapping combination is not allowed.');
            } else {
                $combinations[] = $comboKey;
            }
        }
    }

    public function messages()
    {
        return [
            'store_id.required' => 'Location is required.',
            'sub_store_id.required' => 'Warehouse is required.',
            'details.*.category_id.required' => 'Category is required.',
            'details.*.category_id.*.required' => 'Each category ID is required.',
            // 'details.*.sub_category_id.required' => 'Sub-category is required.',
            // 'details.*.sub_category_id.*.required' => 'Each sub-category ID is required.',
            // 'details.*.item_id.required' => 'Item is required.',
            // 'details.*.item_id.*.required' => 'Each item ID is required.',
        ];
    }


}
