<?php
namespace App\Http\Requests;

use Auth;

use App\Helpers\Helper;
use App\Helpers\ConstantHelper;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class WhMappingRequest extends FormRequest
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
            'level_id' => ['required', 'integer'],
            'status' => 'nullable|string',
            'details' => 'nullable|array',
            'details.*.storage_point' => 'nullable',
            'details.*.max_weight' => ['nullable', 'numeric'],
            'details.*.max_volume' => ['nullable', 'numeric'],
        ];
    }

    public function messages()
    {
        return [
            'store_id.required' => 'Location is required.',
            'sub_store_id.required' => 'Warehouse is required.',
            'level_id.required' => 'Level is required.',
            'details.*.name.required' => 'Name is required.',
            'details.*.name.unique' => 'Name has already been taken.',
            'details.*.name.max' => 'Name may not be greater than 100 characters.',
            'details.*.parent_id.numeric' => 'Parent Level must be integer.',
            'details.*.max_weight.numeric' => 'Maximum Width must be integer.',
            'details.*.max_volume.numeric' => 'Maximum Volume must be integer.',
        ];
    }

    public function withValidator($validator)
    {
        $details = $this->input('details', []);
        $storeId = $this->input('store_id');
        $subStoreId = $this->input('sub_store_id');
        $levelId = $this->input('level_id');

        foreach ($details as $index => $detail) {
            $rule = Rule::unique('erp_wh_details', 'name')
                ->where(function ($query) use ($storeId) {
                    return $query->where('store_id', $storeId);
                })
                ->where(function ($query) use ($subStoreId) {
                    return $query->where('sub_store_id', $subStoreId);
                })
                ->whereNull('deleted_at');

            if (!empty($detail['detail_id'])) {
                $rule->ignore($detail['detail_id']);
            }

            $validator->addRules([
                "details.$index.name" => [
                    'required',
                    'string',
                    'max:100',
                    $rule,
                ],
            ]);
        }

        $validator->after(function ($validator) {
            $details = $this->input('details', []);
            foreach ($details as $index => $detail) {
                if (isset($detail['is_first_level']) && $detail['is_first_level'] == 0) {
                    if (empty($detail['parent_id'])) {
                        $validator->errors()->add("details.$index.parent_id", "The parent field is required when the item is not first level.");
                    }
                }
            }
        });
    }

    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {
    //         if (isset($this->details) && is_array($this->details)) {
    //             foreach ($this->details as $index => $detail) {
    //                 foreach ($detail as $key => $value) {
    //                     if (!in_array($key, ['name', 'storage_point', 'max_weight', 'max_volume'])) { // known fields
    //                         // Check if the value is null (making it mandatory)
    //                         if (is_null($value)) {
    //                             $validator->errors()->add("details.$index.$key", "The $key field is required.");
    //                         } elseif (!is_numeric($value)) {
    //                             // Check if the value is an integer
    //                             $validator->errors()->add("details.$index.$key", "The $key must be an integer.");
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     });
    // }
}
