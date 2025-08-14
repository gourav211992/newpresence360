<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use App\Models\WhDetail;
use Illuminate\Foundation\Http\FormRequest;

class WhMultiMappingRequest extends FormRequest
{
    protected $group_id;
    protected $organization_id;

    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization?->id;
        $this->group_id = $organization?->group_id;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['required', 'integer'],
            'sub_store_id' => ['required', 'integer'],
            'level_id' => ['required', 'integer'],
            'status' => 'nullable|string',
            'details' => ['required', 'array', 'min:1'],
            'details.*.name' => ['required', 'string', 'max:10'],
            'details.*.parent_id' => ['nullable', 'array'],
            'details.*.parent_id.*' => ['nullable', 'exists:erp_wh_details,id'],
            'details.*.is_first_level' => ['nullable', 'boolean'],
            'details.*.is_last_level' => ['nullable', 'boolean'],
            'details.*.max_weight' => ['nullable', 'numeric', 'min:0'],
            'details.*.max_volume' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages()
    {
        return [
            'store_id.required' => 'Location is required.',
            'sub_store_id.required' => 'Warehouse is required.',
            'level_id.required' => 'Level is required.',
            'details.required' => 'At least one detail entry is required.',
            'details.*.name.required' => 'Name is required.',
            'details.*.name.max' => 'Name may not be greater than 10 characters.',
            'details.*.parent_id.*.exists' => 'Invalid parent selection.',
            'details.*.max_weight.numeric' => 'Maximum Weight must be numeric.',
            'details.*.max_volume.numeric' => 'Maximum Volume must be numeric.',
        ];
    }

    public function withValidator($validator)
    {
        $levelId = $this->input('level_id');
        $storeId = $this->input('store_id');
        $subStoreId = $this->input('sub_store_id');
        $details = $this->input('details', []);

        $validator->after(function ($validator) use ($details, $levelId, $storeId, $subStoreId) {
            foreach ($details as $index => $detail) {
                $parentIds = $detail['parent_id'] ?? [null];
                $detailIds = $detail['detail_ids'] ?? [];

                if (($detail['is_first_level'] ?? 0) == 0 && empty($parentIds)) {
                    $validator->errors()->add("details.$index.parent_id", "Parent is required for non-first level.");
                }

                foreach ($parentIds as $parentId) {
                    $parentWh = $parentId ? WhDetail::find($parentId) : null;
                    $parentName = $parentWh?->heirarchy_name;
                    $heirarchyName = $parentName && $detail['name'] ? $parentName . '-' . $detail['name'] : ($parentName ?? $detail['name']);

                    $duplicate = WhDetail::where('wh_level_id', $levelId)
                        ->where('store_id', $storeId)
                        ->where('sub_store_id', $subStoreId)
                        ->where(function ($query) use ($detail, $heirarchyName) {
                            $query->whereRaw('LOWER(name) = ?', [strtolower($detail['name'])])
                                ->orWhereRaw('LOWER(heirarchy_name) = ?', [strtolower($heirarchyName)]);
                        })
                        ->when($detailIds, fn($q) => $q->whereNotIn('id', $detailIds))
                        ->exists();

                    if ($duplicate) {
                        $validator->errors()->add("details.$index.name", "Duplicate (name/hierarchy) : '$heirarchyName'");
                    }
                }
            }
        });
    }
}
