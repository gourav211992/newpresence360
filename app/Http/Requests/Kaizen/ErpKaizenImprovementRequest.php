<?php

namespace App\Http\Requests\Kaizen;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use Auth;
class ErpKaizenImprovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

        protected $organization_id;
        protected $company_id;
        protected $group_id;
    public function authorize(): bool
    {
        return true;
    }
    protected function prepareForValidation()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization ? $organization->id : null;
        $this->group_id = $organization ? $organization->group_id : null; 
        $this->company_id = $organization ? $organization->company_id : null;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $discountId = $this->route('id');
            $uniqueScope = function ($query) {
            if ($this->group_id !== null) {
                $query->where('group_id', $this->group_id);
            }

            if ($this->company_id !== null) {
                $query->where(function ($q) {
                    $q->where('company_id', $this->company_id)
                      ->orWhereNull('company_id');
                });
            }

            if ($this->organization_id !== null) {
                $query->where(function ($q) {
                    $q->where('organization_id', $this->organization_id)
                      ->orWhereNull('organization_id');
                });
            }
        };

        return [
            'type' => [
                'required',
                'string',
                'max:255',
                Rule::unique('erp_kaizen_improvements')->where($uniqueScope)->ignore($discountId)
            ],
            'description' => [
                'required',
                'string',
                'max:1000',
                Rule::unique('erp_kaizen_improvements')->where($uniqueScope)->ignore($discountId)
            ], 
            'marks' => [
                'required',
                'string',
                'max:1000',
                Rule::unique('erp_kaizen_improvements')->where($uniqueScope)->ignore($discountId)
            ],
            'status' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Please provide the improvement type.',
            'type.unique'   => 'This type already exists for the selected group, company, or organization.',
            'type.max'      => 'Type may not be greater than 255 characters.',

            'description.required' => 'Please provide a description of the improvement.',
            'description.unique'   => 'This description already exists for the selected group, company, or organization.',
            'description.max'      => 'Description may not be greater than 1000 characters.',

            'marks.required' => 'Marks field is required.',
            'marks.unique'   => 'These marks already exist for the selected group, company, or organization.',
            'marks.max'      => 'Marks may not be greater than 1000 characters.',
        ];
    }

}
