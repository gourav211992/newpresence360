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
            'status' => 'nullable|string',
            'details' => ['required', 'array'],
            'details.*.category_id' => ['required', 'array', 'min:1'],
            'details.*.category_id.*' => ['required', 'integer'],
            'details.*.sub_category_id' => ['nullable', 'array', 'min:1'],
            'details.*.sub_category_id.*' => ['nullable', 'integer'],
            'details.*.item_id' => ['nullable', 'array', 'min:1'],
            'details.*.item_id.*' => ['nullable', 'integer'],
        ];
    }

    public function withValidator($validator)
    {
        $details = $this->input('details', []);
        $storeId = $this->input('store_id');
        $subStoreId = $this->input('sub_store_id');

        $combinations = [];

        foreach ($details as $index => $detail) {
            // Dynamically add validation rules for structure fields
            foreach ($detail as $key => $value) {
                if (is_array($value) && in_array($key, ['category_id', 'sub_category_id', 'item_id'])) {
                    continue;
                }

                $validator->addRules([
                    "details.$index.$key" => ['required', 'array', 'min:1'],
                    "details.$index.$key.*" => ['required', 'integer'],
                ]);
            }

            // Normalize keys: Sort the arrays to avoid issues with different ordering
            $categoryIds = collect($detail['category_id'] ?? [])->sort()->values()->all();
            $subCategoryIds = collect($detail['sub_category_id'] ?? [])->sort()->values()->all();
            $itemIds = collect($detail['item_id'] ?? [])->sort()->values()->all();

            // Extract structure_details (dynamic fields) and sort them
            $structureDetails = collect($detail)
                ->reject(function ($val, $key) {
                    return in_array($key, ['category_id', 'sub_category_id', 'item_id']);
                })
                ->map(fn($val) => collect($val)->sort()->values()->all()) // sort inner arrays
                ->sortKeys()
                ->toArray();

            // Build combination key with store_id, sub_store_id, category_id, sub_category_id, item_id, and structure details
            $comboKey = json_encode([
                'store_id' => $storeId,
                'sub_store_id' => $subStoreId,
                'category_id' => $categoryIds,
                'sub_category_id' => $subCategoryIds,
                'item_id' => $itemIds,
                'structure_details' => $structureDetails,
            ]);

            // Debugging log: check the combination key
            \Log::info('Combination Key: ' . $comboKey);

            if (in_array($comboKey, $combinations)) {
                // If the combination already exists, add error
                $validator->errors()->add("details.$index", 'Duplicate combination of store, sub-store, category, sub-category, item, and structure details is not allowed.');
            } else {
                // Add the comboKey to the list to track
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
