<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\Helper;

class PurchaseReturnAccountRequest extends FormRequest
{

    public function rules()
    {
        return [
            'purchase_return_accounts' => 'array', 
            'purchase_return_accounts.*.id' => 'nullable',
            'purchase_return_accounts.*.group_id' => 'nullable|exists:organization_groups,id', 
            'purchase_return_accounts.*.company_id' => 'required|exists:organization_companies,id',
            'purchase_return_accounts.*.organization_id' => 'required|exists:organizations,id',
            'purchase_return_accounts.*.ledger_group_id' => 'required|exists:erp_groups,id', 
            'purchase_return_accounts.*.ledger_id' => 'required|exists:erp_ledgers,id', 
            'purchase_return_accounts.*.category_id' => 'nullable|exists:erp_categories,id',
            'purchase_return_accounts.*.sub_category_id' => 'nullable|exists:erp_categories,id', 
            'purchase_return_accounts.*.item_id' => 'nullable|array',
            'purchase_return_accounts.*.item_id.*' => 'exists:erp_items,id',
            'purchase_return_accounts.*.book_id' => 'nullable|exists:erp_books,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $purchaseReturnAccounts = $this->input('purchase_return_accounts', []); 
            $existingCombinations = []; 
            $usedItemCombinations = [];
            $usedBookCombinations = [];

            foreach ($purchaseReturnAccounts as $index => $purchaseReturnAccount) {
                $companyId = $purchaseReturnAccount['company_id'] ?? null;
                $organizationId = $purchaseReturnAccount['organization_id'] ?? null;
                $ledgerId = $purchaseReturnAccount['ledger_id'] ?? null;
                $ledgerGroupId = $purchaseReturnAccount['ledger_group_id'] ?? null;
                $categoryId = $purchaseReturnAccount['category_id'] ?? null;
                $subCategoryId = $purchaseReturnAccount['sub_category_id'] ?? null;
                $itemIds = $purchaseReturnAccount['item_id'] ?? [];
                $bookIds = $purchaseReturnAccount['book_id'] ?? [];

                $itemIds = is_array($itemIds) ? $itemIds : [$itemIds];
                $bookIds = is_array($bookIds) ? $bookIds : [$bookIds]; 

                $combinationKey = $companyId . '-' . $organizationId . '-' . $ledgerId . '-' . $ledgerGroupId . '-' . $categoryId . '-' . $subCategoryId;
                if (empty($itemIds) && empty($bookIds)) {
                    if (isset($existingCombinations[$combinationKey])) {
                        $validator->errors()->add("purchase_return_accounts.{$index}.company_id", 'These combination already exists.');
                    } else {
                        $existingCombinations[$combinationKey] = true;
                    }
                }
                if ($itemIds) {
                    foreach ($itemIds as $itemId) {
                        $itemCombinationKey = $combinationKey . '-' . $itemId;
                        if (isset($usedItemCombinations[$itemCombinationKey])) {
                            $validator->errors()->add("purchase_return_accounts.{$index}.company_id", "These combination already exists.");
                        } else {
                            $usedItemCombinations[$itemCombinationKey] = true;
                        }
                    }
                }
                if ($bookIds) {
                    foreach ($bookIds as $bookId) {
                        $bookCombinationKey = $combinationKey . '-' . $bookId;
                        if (isset($usedBookCombinations[$bookCombinationKey])) {
                            $validator->errors()->add("purchase_return_accounts.{$index}.company_id", "These combination already exists.");
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
            'purchase_return_accounts.*.group_id.exists' => 'The selected group does not exist.',
            'purchase_return_accounts.*.company_id.exists' => 'The selected company does not exist.',
            'purchase_return_accounts.*.organization_id.exists' => 'The selected organization does not exist.',
            'purchase_return_accounts.*.ledger_group_id.exists' => 'The selected ledger group does not exist.',
            'purchase_return_accounts.*.ledger_group_id.required' => 'The ledger group field is required.',
            'purchase_return_accounts.*.ledger_id.exists' => 'The selected ledger does not exist.',
            'purchase_return_accounts.*.category_id.exists' => 'The selected category does not exist.',
            'purchase_return_accounts.*.sub_category_id.exists' => 'The selected item group does not exist.',
            'purchase_return_accounts.*.item_id.exists' => 'The selected item does not exist.',
            'purchase_return_accounts.*.book_id.exists' => 'The selected book does not exist.',
            'purchase_return_accounts.*.company_id.required' => 'The company field is required.',
            'purchase_return_accounts.*.organization_id.required' => 'The organization field is required.',
            'purchase_return_accounts.*.ledger_id.required' => 'The ledger field is required.',
        ];
    }
}