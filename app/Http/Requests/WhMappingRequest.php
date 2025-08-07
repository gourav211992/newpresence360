<?php

namespace App\Http\Requests;

use Auth;

use App\Helpers\Helper;
use App\Models\WhDetail;

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
            'details.*.name' => 'required|string|max:10',
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
            'details.*.name.max' => 'Name may not be greater than 10 characters.',
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
        $parentIds = collect($details)->pluck('parent_id')->filter()->unique()->toArray();
        $parentNames = WhDetail::whereIn('id', $parentIds)->pluck('name', 'id')
            ->map(fn($name) => strtolower(trim($name)));

        $requestNameParentMap = [];
        $requestHeirarchyNameMap = [];

        foreach ($details as $index => $detail) {
            $name = strtolower(trim($detail['name'] ?? ''));
            $parentId = $detail['parent_id'] ?? null;
            $isFirstLevel = $detail['is_first_level'] ?? null;

            if (!$isFirstLevel && empty($parentId)) {
                $validator->errors()->add("details.$index.parent_id", "The parent field is required when the item is not first level.");
            }
            $parentName = $parentNames[$parentId] ?? null;
            $heirarchyName = $parentName ? "{$parentName}-{$name}" : $name;
            $nameParentKey = $parentId . '|' . $name;
            if (isset($requestNameParentMap[$nameParentKey])) {
                $validator->errors()->add("details.$index.name", "The name '{$detail['name']}' is duplicated under the same parent.");
            } else {
                $requestNameParentMap[$nameParentKey] = true;
            }
            if (isset($requestHeirarchyNameMap[$heirarchyName])) {
                $validator->errors()->add("details.$index.name", "The hierarchy name '{$heirarchyName}' is duplicated in request.");
            } else {
                $requestHeirarchyNameMap[$heirarchyName] = true;
            }
            $rule = Rule::unique('erp_wh_details', 'name')
                ->where(function ($query) use ($storeId, $subStoreId, $parentId, $name, $heirarchyName) {
                    $query->where('store_id', $storeId)
                        ->where('sub_store_id', $subStoreId)
                        ->whereNull('deleted_at')
                        ->where(function ($q) use ($parentId, $name, $heirarchyName) {
                            $q->whereRaw('LOWER(name) = ?', [$name])
                                ->where('parent_id', $parentId)
                                ->orWhereRaw('LOWER(heirarchy_name) = ?', [$heirarchyName]);
                        });
                });

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
    }
}
