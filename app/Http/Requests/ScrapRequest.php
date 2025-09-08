<?php

namespace App\Http\Requests;

use App\Models\Item;
use App\Helpers\Helper;
use App\Helpers\BookHelper;
use App\Models\NumberPattern;
use App\Traits\ProcessesComponentJson;
use Illuminate\Foundation\Http\FormRequest;

class ScrapRequest extends FormRequest
{
    /**
     * Prepare input if needed.
     */
    use ProcessesComponentJson;

    protected $organization_id;
    protected $group_id;
    protected function prepareForValidation(): void
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $this->organization_id = $organization ? $organization->id : null;
        $this->group_id = $organization ? $organization->group_id : null;
        $this->processComponentJson('components_json');
    }

    /**
     * Rules
     */
    public function rules(): array
    {
        $rules = [
            'book_id'          => 'required|integer|exists:erp_books,id',
            'book_code'        => 'required|string',
            'document_number'  => 'required|string',
            'document_date'    => 'required|date',
            'store_id'         => 'required|integer|exists:erp_stores,id',
            'sub_store_id'     => 'required|integer|exists:erp_sub_stores,id',

            'pull_item_ids'    => 'nullable|array',
            'pull_item_ids.*'  => 'integer',

            'item_ids'         => 'nullable|string',

            'component_item_name'   => 'required|array|min:1',
            'component_item_name.*' => 'required|string',

            'components'             => 'required|array|min:1',
            'components.*.item_id'   => 'required|integer|exists:erp_items,id',
            'components.*.item_code' => 'required|string',
            'components.*.item_name' => 'required|string',
            'components.*.hsn_id'    => 'nullable|integer|exists:erp_hsn,id',
            'components.*.uom_id'    => 'required|integer|exists:erp_units,id',
            'components.*.qty'       => 'required|numeric|min:0.000001',
            'components.*.remark'    => 'nullable|string|max:255',
        ];

        /**
         * Handle date rules based on book parameters
         */
        $parameters = [];
        $response = BookHelper::fetchBookDocNoAndParameters($this->input('book_id'), $this->input('document_date'));
        if ($response['status'] === 200) {
            $parameters = json_decode(json_encode($response['data']['parameters']), true);
        }

        $today = now()->toDateString();
        $futureAllowed = !empty($parameters['future_date_allowed']) && in_array('yes', array_map('strtolower', (array) $parameters['future_date_allowed']));
        $backAllowed = !empty($parameters['back_date_allowed']) && in_array('yes', array_map('strtolower', (array) $parameters['back_date_allowed']));

        if (!$futureAllowed && !$backAllowed) {
            $rules['document_date'] = "required|date|in:$today";
        } elseif ($futureAllowed && !$backAllowed) {
            $rules['document_date'] = "required|date|after_or_equal:$today";
        } elseif (!$futureAllowed && $backAllowed) {
            $rules['document_date'] = "required|date|before_or_equal:$today";
        }

        /**
         * Check numbering pattern (manual vs auto)
         */
        if ($this->filled('book_id')) {
            $user = Helper::getAuthenticatedUser();
            $numPattern = NumberPattern::where('organization_id', $user->organization_id)
                ->where('book_id', $this->book_id)
                ->latest('id')
                ->first();

            if ($numPattern && $numPattern->series_numbering === 'Manually') {
                $scrapId = $this->route('id');
                $rules['document_number'] = $scrapId
                    ? "required|unique:erp_scraps,document_number,$scrapId"
                    : "required|unique:erp_scraps,document_number";
            }
        }

        /**
         * Attribute validation per item
         */
        foreach ($this->input('components', []) as $index => $component) {
            $item = Item::find($component['item_id'] ?? null);
            $rules["components.$index.attr_group_id.*.attr_name"] = $item && $item->itemAttributes->count() > 0
                ? 'required|string'
                : 'nullable|string';
        }

        return $rules;
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'book_id.required' => 'The series (book) is required.',
            'book_code.required' => 'The book code is required.',
            'document_number.required' => 'The document number is required.',
            'document_number.unique' => 'This document number already exists.',
            'document_date.required' => 'The document date is required.',
            'document_date.in' => 'The document date must be today.',
            'document_date.after_or_equal' => 'The document date cannot be in the past.',
            'document_date.before_or_equal' => 'The document date cannot be in the future.',
            'store_id.required' => 'The store is required.',
            'sub_store_id.required' => 'The sub store is required.',

            'component_item_name.*.required' => 'Component item name is required.',
            'components.required' => 'At least one component is required.',
            'components.*.item_id.required' => 'Component item is required.',
            'components.*.uom_id.required' => 'Unit of measurement is required.',
            'components.*.qty.required' => 'Quantity is required.',
            'components.*.qty.numeric' => 'Quantity must be a valid number.',
            'components.*.qty.min' => 'Quantity must be greater than zero.',
            'components.*.attr_group_id.*.attr_name.required' => 'Select Attribute.',
        ];
    }
}
