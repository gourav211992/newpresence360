<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\BookHelper;
use App\Helpers\Helper;
use App\Models\NumberPattern;

class RgrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // ===================== BASIC RULES =====================
        $rules = [
            'book_id'         => 'required|integer|exists:erp_books,id',
            'document_date'   => 'required|date',
            'document_number' => 'required|string|max:100',
            'store_id'        => 'required|integer|exists:erp_stores,id',

            'rgr_items'                   => 'required|array|min:1',
            'rgr_items.*.item_id'         => 'required|integer|exists:erp_items,id',
            'rgr_items.*.category_id'     => 'nullable|integer|exists:erp_categories,id',
            'rgr_items.*.hsn_id'          => 'nullable|integer|exists:erp_hsns,id',
            'rgr_items.*.sub_store_id'    => 'nullable|integer|exists:erp_sub_stores,id',
            'rgr_items.*.item_uid'        => 'nullable|string|max:100',
            'rgr_items.*.item_code'       => 'nullable|string|max:100',
            'rgr_items.*.item_name'       => 'nullable|string|max:255',
            'rgr_items.*.uom_id'          => 'required|integer|exists:erp_units,id',
            'rgr_items.*.uom_name'        => 'required|string|max:50',
            'rgr_items.*.qty'             => 'required|numeric|min:0.01',
            'rgr_items.*.remarks'         => 'nullable|string|max:500',
            'rgr_items.*.customer_id'     => 'nullable|integer|exists:erp_customers,id',
            'rgr_items.*.customer_name'   => 'nullable|string|max:255',

            'rgr_items.*.rgr_item_attributes'                             => 'nullable|array',
            'rgr_items.*.rgr_item_attributes.*.item_attribute_id'         => 'nullable|integer|exists:erp_item_attributes,id',
            'rgr_items.*.rgr_item_attributes.*.attribute_name'            => 'nullable|string|max:100',
            'rgr_items.*.rgr_item_attributes.*.attr_name'                 => 'nullable|integer|exists:erp_attribute_groups,id',
            'rgr_items.*.rgr_item_attributes.*.attribute_value'           => 'nullable|string|max:255',
            'rgr_items.*.rgr_item_attributes.*.attr_value'                => 'nullable|integer|exists:erp_attributes,id',
        ];

        // ===================== DOCUMENT DATE RULES =====================
        $parameters = [];
        $response = BookHelper::fetchBookDocNoAndParameters($this->input('book_id'), $this->input('document_date'));
        if ($response['status'] === 200) {
            $parameters = json_decode(json_encode($response['data']['parameters']), true);
        }

        $today = now()->toDateString();
        $futureAllowed = isset($parameters['future_date_allowed']) && in_array('yes', array_map('strtolower', (array)$parameters['future_date_allowed']));
        $backAllowed   = isset($parameters['back_date_allowed']) && in_array('yes', array_map('strtolower', (array)$parameters['back_date_allowed']));

        if (!$futureAllowed && !$backAllowed) {
            $rules['document_date'] = "required|date|in:$today";
        } elseif ($futureAllowed && $backAllowed) {
            $rules['document_date'] = "required|date";
        } elseif ($futureAllowed) {
            $rules['document_date'] = "required|date|after_or_equal:$today";
        } elseif ($backAllowed) {
            $rules['document_date'] = "required|date|before_or_equal:$today";
        }

        // ===================== DOCUMENT NUMBER UNIQUE RULE =====================
        if ($this->filled('book_id')) {
            $user = Helper::getAuthenticatedUser();
            $numPattern = NumberPattern::where('organization_id', $user->organization_id)
                ->where('book_id', $this->book_id)
                ->latest()
                ->first();
                
            $rgrId = $this->route('id'); 

            if ($numPattern && $numPattern->series_numbering === 'Manually') {
                $rules['document_number'] = 'required|unique:erp_rgrs,document_number,' . ($rgrId ?? 'NULL') . ',id';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'book_id.required' => 'Book is required.',
            'book_id.integer'  => 'Book must be valid.',
            'book_id.exists'   => 'Book does not exist.',

            'document_date.required' => 'Document date is required.',
            'document_date.date'     => 'Document date must be valid.',
            'document_date.in'       => 'Document date must be today.',
            'document_date.after_or_equal' => 'Future dates not allowed.',
            'document_date.before_or_equal'=> 'Back dates not allowed.',

            'document_number.required' => 'Document number is required.',
            'document_number.unique'   => 'Document number already exists.',

            'store_id.required' => 'Store is required.',
            'store_id.exists'   => 'Store not found.',

            'rgr_items.required' => 'At least one RGR item is required.',
            'rgr_items.*.item_id.required' => 'Item is required.',
            'rgr_items.*.item_id.exists'   => 'Item not found.',
            'rgr_items.*.uom_id.required'  => 'UOM is required.',
            'rgr_items.*.uom_id.exists'    => 'UOM not found.',
            'rgr_items.*.qty.required'     => 'Quantity is required.',
            'rgr_items.*.qty.min'          => 'Quantity must be greater than zero.',

            'rgr_items.*.rgr_item_attributes.*.item_attribute_id.exists' => 'Invalid item attribute.',
            'rgr_items.*.rgr_item_attributes.*.attr_name.exists'         => 'Invalid attribute group.',
            'rgr_items.*.rgr_item_attributes.*.attr_value.exists'        => 'Invalid attribute value.',
        ];
    }
}
