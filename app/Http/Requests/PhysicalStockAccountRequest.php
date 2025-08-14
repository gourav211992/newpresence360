<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;

class PhysicalStockAccountRequest extends FormRequest
{
    public function rules()
    {
        
        return [
            'physical_stock_accounts' => 'array', 
            'physical_stock_accounts.*.id' => 'nullable',
            'physical_stock_accounts.*.group_id' => 'nullable|exists:organization_groups,id',
            'physical_stock_accounts.*.company_id' => 'required|exists:organization_companies,id',
            'physical_stock_accounts.*.organization_id' => 'required|exists:organizations,id', 
            'physical_stock_accounts.*.ledger_group_id' => 'required|exists:erp_groups,id',
            'physical_stock_accounts.*.ledger_id' => 'required|exists:erp_ledgers,id',
            'physical_stock_accounts.*.category_id' => 'nullable|exists:erp_categories,id',
            'physical_stock_accounts.*.sub_category_id' => 'nullable|exists:erp_categories,id', 
            'physical_stock_accounts.*.item_id' => 'nullable|array', 
            'physical_stock_accounts.*.item_id.*' => 'exists:erp_items,id', 
            'physical_stock_accounts.*.book_id' => 'nullable|exists:erp_books,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $physicalStockAccounts = $this->input('physical_stock_accounts', []); 
            $existingCombinations = []; 
            $usedCombinations = [];
            $usedItemCombinations = [];
            $usedBookCombinations = [];

            foreach ($physicalStockAccounts as $index => $account) {
                $companyId = $account['company_id'] ?? null;
                $organizationId = $account['organization_id'] ?? null;
                $ledgerId = $account['ledger_id'] ?? null;
                $ledgerGroupId = $account['ledger_group_id'] ?? null;
                $categoryId = $account['category_id'] ?? null;
                $subCategoryId = $account['sub_category_id'] ?? null;
                $itemIds = $account['item_id'] ?? [];
                $bookIds = $account['book_id'] ?? [];

                $itemIds = is_array($itemIds) ? $itemIds : [$itemIds];
                $bookIds = is_array($bookIds) ? $bookIds : [$bookIds]; 

                $combinationKey = $companyId . '-' . $organizationId . '-' . $ledgerId . '-' . $ledgerGroupId . '-' . $categoryId . '-' . $subCategoryId;

                if (empty($itemIds) && empty($bookIds)) {
                    if (isset($existingCombinations[$combinationKey])) {
                        $validator->errors()->add("physical_stock_accounts.{$index}.company_id", 'This combination already exists.');
                    } else {
                        $existingCombinations[$combinationKey] = true;
                    }
                }

                if ($itemIds) {
                    foreach ($itemIds as $itemId) {
                        $itemCombinationKey = $combinationKey . '-' . $itemId;
                        if (isset($usedItemCombinations[$itemCombinationKey])) {
                            $validator->errors()->add("physical_stock_accounts.{$index}.company_id", "This item combination already exists.");
                        } else {
                            $usedItemCombinations[$itemCombinationKey] = true;
                        }
                    }
                }

                if ($bookIds) {
                    foreach ($bookIds as $bookId) {
                        $bookCombinationKey = $combinationKey . '-' . $bookId;
                        if (isset($usedBookCombinations[$bookCombinationKey])) {
                            $validator->errors()->add("physical_stock_accounts.{$index}.company_id", "This book combination already exists.");
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
            'physical_stock_accounts.*.group_id.exists' => 'The selected group does not exist.',
            'physical_stock_accounts.*.company_id.exists' => 'The selected company does not exist.',
            'physical_stock_accounts.*.organization_id.exists' => 'The selected organization does not exist.',
            'physical_stock_accounts.*.ledger_group_id.exists' => 'The selected ledger group does not exist.',
            'physical_stock_accounts.*.ledger_group_id.required' => 'The ledger group field is required.',
            'physical_stock_accounts.*.ledger_id.exists' => 'The selected ledger does not exist.',
            'physical_stock_accounts.*.category_id.exists' => 'The selected category does not exist.',
            'physical_stock_accounts.*.sub_category_id.exists' => 'The selected item-group does not exist.',
            'physical_stock_accounts.*.item_id.exists' => 'The selected item does not exist.',
            'physical_stock_accounts.*.book_id.exists' => 'The selected book does not exist.',
            'physical_stock_accounts.*.company_id.required' => 'The company field is required.',
            'physical_stock_accounts.*.organization_id.required' => 'The organization field is required.',
            'physical_stock_accounts.*.ledger_id.required' => 'The ledger field is required.',
        ];
    }
}