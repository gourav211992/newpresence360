<?php
namespace App\Http\Requests;

use DB;
use Auth;

use App\Helpers\Helper;
use App\Helpers\ConstantHelper;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class WhStructureRequest extends FormRequest
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
            'store_id' => [
                'required',
                'numeric',
            ],
            'sub_store_id' => [
                'required',
                'numeric',
                Rule::unique('erp_wh_structures')
                    ->where(function ($query) {
                        return $query
                            ->where('store_id', $this->store_id)
                            ->where('sub_store_id', $this->sub_store_id)
                            ->where('group_id', $this->group_id)
                            ->where('organization_id', $this->organization_id)
                            ->whereNull('deleted_at');
                    })
                    ->ignore($this->route('id')), // ignore when updating
            ],
            'name' => [
                'nullable', 
                'string', 
                'max:500'
            ],
            'status' => 'nullable|string',
            'levels' => 'nullable|array',
            'levels.*.level' => [
                'required',
                'numeric'
            ],
            'levels.*.name' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages()
    {
        return [
            'store_id.required' => 'Location is required.',
            'store_id.numeric' => 'Location must be a number.',
            'sub_store_id.required' => 'Warehouse is required.',
            'sub_store_id.numeric' => 'Warehouse must be a number.',
            'sub_store_id.unique' => 'The combination of Location and Warehouse must be unique.',
            'levels.*.required' => 'Station for each level is required.',
            'levels.*.level.required' => 'Level Val is required.',
            'levels.*.name.required' => 'Level is required.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $levels = $this->input('levels', []);
            $storeId = $this->store_id;
            $subStoreId = $this->sub_store_id;
            // dd($storeId, $subStoreId, $levels);
            // Only passed in update (edit)
            $structureId = $this->route('id');

            foreach ($levels as $index => $level) {
                if (!isset($level['name'], $level['level'])) {
                    continue;
                }

                $query = \DB::table('erp_wh_levels')
                    ->where('store_id', $storeId)
                    ->where('sub_store_id', $subStoreId)
                    ->where('name', $level['name'])
                    ->whereNull('deleted_at');

                // Check by structure_id in update
                if ($structureId) {
                    $query->where('wh_structure_id', $structureId);
                }    

                // Ignore current row if l_id exists
                if (!empty($level['l_id'])) {
                    $query->where('id', '!=', $level['l_id']);
                }
                // dd($query->toSql(), $query->getBindings());
                // dd($query->exists());
                if ($query->exists()) {
                    $validator->errors()->add("levels.{$index}.name", "The name '{$level['name']}' already exists for level {$level['level']} in this structure.");
                }
            }
        });
    }
}
