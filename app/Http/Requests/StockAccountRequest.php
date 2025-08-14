<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;

class StockAccountRequest extends FormRequest
{

    public function rules()
    {
        
        return [
            'stock_accounts' => 'array', 
            'stock_accounts.*.group_id' => 'nullable|exists:organization_groups,id',
            'stock_accounts.*.company_id' => 'required|exists:organization_companies,id',
            'stock_accounts.*.organization_id' => 'required|exists:organizations,id',
            'stock_accounts.*.ledger_group_id' => 'required|exists:erp_groups,id',
            'stock_accounts.*.ledger_id' => 'required|exists:erp_ledgers,id',
            'stock_accounts.*.category_id' => 'nullable|exists:erp_categories,id',
            'stock_accounts.*.sub_category_id' => 'nullable|exists:erp_categories,id',
            'stock_accounts.*.item_id' => 'nullable|array',
            'stock_accounts.*.id' => 'nullable',
            'stock_accounts.*.item_id.*' => 'exists:erp_items,id',
            'stock_accounts.*.book_id' => 'nullable|exists:erp_books,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $stockAccounts = $this->input('stock_accounts', []); 
            $existingCombinations = []; 
            $usedCombinations = [];
            $usedItemCombinations = [];
            $usedBookCombinations = [];

            foreach ($stockAccounts as $index => $stockAccount) {
                $companyId = $stockAccount['company_id'] ?? null;
                $organizationId = $stockAccount['organization_id'] ?? null;
                $ledgerId = $stockAccount['ledger_id'] ?? null;
                $ledgerGroupId = $stockAccount['ledger_group_id'] ?? null;
                $categoryId = $stockAccount['category_id'] ?? null;
                $subCategoryId = $stockAccount['sub_category_id'] ?? null;
                $itemIds = $stockAccount['item_id'] ?? [];
                $bookIds = $stockAccount['book_id'] ?? [];

                $itemIds = is_array($itemIds) ? $itemIds : [$itemIds];
                $bookIds = is_array($bookIds) ? $bookIds : [$bookIds]; 
    
                $combinationKey = $companyId . '-' . $organizationId . '-' . $ledgerId . '-' . $ledgerGroupId . '-' . $categoryId . '-' . $subCategoryId;
    
                if (empty($itemIds && $bookIds)) {
                    if (isset($existingCombinations[$combinationKey])) {
                        $validator->errors()->add("stock_accounts.{$index}.company_id", 'These combination already exists.');
                    } else {
                        $existingCombinations[$combinationKey] = true;
                    }
                } 
                
                if($itemIds){
                    foreach ($itemIds as $itemId) {
                        $itemCombinationKey = $combinationKey . '-' . $itemId;
                        if (isset($usedItemCombinations[$itemCombinationKey])) {
                            $validator->errors()->add("stock_accounts.{$index}.company_id", "These combination already exists.");
                        } else {
                            $usedItemCombinations[$itemCombinationKey] = true;
                        }
                    }
                  }

                if ($bookIds) {
                    foreach ($bookIds as $bookId) {
                        $bookCombinationKey = $combinationKey . '-' . $bookId;
                        if (isset($usedBookCombinations[$bookCombinationKey])) {
                            $validator->errors()->add("stock_accounts.{$index}.company_id", "These combination already exists.");
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
            'stock_accounts.array' => 'The stock accounts must be an array.',
            'stock_accounts.*.group_id.exists' => 'The selected group does not exist.',
            'stock_accounts.*.company_id.required' => 'The company field is required.',
            'stock_accounts.*.company_id.exists' => 'The selected company does not exist.',
            'stock_accounts.*.organization_id.required' => 'The organization field is required.',
            'stock_accounts.*.organization_id.exists' => 'The selected organization does not exist.',
            'stock_accounts.*.ledger_group_id.exists' => 'The selected ledger group does not exist.',
            'stock_accounts.*.ledger_group_id.required' => 'The ledger group field is required.',
            'stock_accounts.*.ledger_id.required' => 'The ledger field is required.',
            'stock_accounts.*.ledger_id.exists' => 'The selected ledger does not exist.',
            'stock_accounts.*.category_id.exists' => 'The selected category does not exist.',
            'stock_accounts.*.sub_category_id.exists' => 'The selected item group does not exist.',
            'stock_accounts.*.item_id.exists' => 'The selected item does not exist.',
            'stock_accounts.*.item_id.*.exists' => 'One of the selected items does not exist.',
            'stock_accounts.*.book_id.exists' => 'The selected book does not exist.',
        ];
        
    }
}
