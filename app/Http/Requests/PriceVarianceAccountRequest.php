<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;

class PriceVarianceAccountRequest extends FormRequest
{
    public function rules()
    {

        return [
            'price_variance_accounts' => 'array', 
            'price_variance_accounts.*.id' => 'nullable',
            'price_variance_accounts.*.group_id' => 'nullable|exists:organization_groups,id', 
            'price_variance_accounts.*.company_id' => 'required|exists:organization_companies,id',
            'price_variance_accounts.*.organization_id' => 'required|exists:organizations,id',
            'price_variance_accounts.*.ledger_group_id' => 'required|exists:erp_groups,id', 
            'price_variance_accounts.*.ledger_id' => 'required|exists:erp_ledgers,id', 
            'price_variance_accounts.*.category_id' => 'nullable|exists:erp_categories,id',
            'price_variance_accounts.*.sub_category_id' => 'nullable|exists:erp_categories,id', 
            'price_variance_accounts.*.item_id' => 'nullable|array',
            'price_variance_accounts.*.item_id.*' => 'exists:erp_items,id',
            'price_variance_accounts.*.book_id' => 'nullable|exists:erp_books,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $priceVarianceAccounts = $this->input('price_variance_accounts', []); 
            $existingCombinations = []; 
            $usedCombinations = [];
            $usedItemCombinations = [];
            $usedBookCombinations = [];

            foreach ($priceVarianceAccounts as $index => $priceVarianceAccount) {
                $companyId = $priceVarianceAccount['company_id'] ?? null;
                $organizationId = $priceVarianceAccount['organization_id'] ?? null;
                $ledgerId = $priceVarianceAccount['ledger_id'] ?? null;
                $ledgerGroupId = $priceVarianceAccount['ledger_group_id'] ?? null;
                $categoryId = $priceVarianceAccount['category_id'] ?? null;
                $subCategoryId = $priceVarianceAccount['sub_category_id'] ?? null;
                $itemIds = $priceVarianceAccount['item_id'] ?? [];
                $bookIds = $priceVarianceAccount['book_id'] ?? [];

                $itemIds = is_array($itemIds) ? $itemIds : [$itemIds];
                $bookIds = is_array($bookIds) ? $bookIds : [$bookIds]; 

                $combinationKey = $companyId . '-' . $organizationId . '-' . $ledgerId . '-' . $ledgerGroupId . '-' . $categoryId . '-' . $subCategoryId;
                    if (empty($itemIds && $bookIds )) {
                        if (isset($existingCombinations[$combinationKey])) {
                            $validator->errors()->add("price_variance_accounts.{$index}.company_id", 'These combination already exists.');
                        } else {
                            $existingCombinations[$combinationKey] = true;
                        }
                    }
                    if($itemIds){
                        foreach ($itemIds as $itemId) {
                            $itemCombinationKey = $combinationKey . '-' . $itemId;
                            if (isset($usedItemCombinations[$itemCombinationKey])) {
                                $validator->errors()->add("price_variance_accounts.{$index}.company_id", "These combination already exists.");
                            } else {
                                $usedItemCombinations[$itemCombinationKey] = true;
                            }
                        }
                    }
                    if ($bookIds) {
                        foreach ($bookIds as $bookId) {
                            $bookCombinationKey = $combinationKey . '-' . $bookId;
                            if (isset($usedBookCombinations[$bookCombinationKey])) {
                                $validator->errors()->add("price_variance_accounts.{$index}.company_id", "These combination already exists.");
                            } else {
                                $usedBookCombinations[$bookCombinationKey] = true;
                            }
                        }
                    }
            }
        });
    }

    public function messages()
    {
        return [
            'price_variance_accounts.*.group_id.exists' => 'The selected group does not exist.',
            'price_variance_accounts.*.company_id.exists' => 'The selected company does not exist.',
            'price_variance_accounts.*.organization_id.exists' => 'The selected organization does not exist.',
            'price_variance_accounts.*.ledger_group_id.exists' => 'The selected ledger group does not exist.',
            'price_variance_accounts.*.ledger_group_id.required' => 'The ledger group field is required.',
            'price_variance_accounts.*.ledger_id.exists' => 'The selected ledger does not exist.',
            'price_variance_accounts.*.category_id.exists' => 'The selected category does not exist.',
            'price_variance_accounts.*.sub_category_id.exists' => 'The selected item group does not exist.',
            'price_variance_accounts.*.item_id.exists' => 'The selected item does not exist.',
            'price_variance_accounts.*.book_id.exists' => 'The selected book does not exist.',
            'price_variance_accounts.*.company_id.required' => 'The company field is required.',
            'price_variance_accounts.*.organization_id.required' => 'The organization field is required.',
            'price_variance_accounts.*.ledger_id.required' => 'The ledger field is required.',
        ];
    }
}
