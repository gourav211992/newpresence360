<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;

class GrAccountRequest extends FormRequest
{
    public function rules()
    {
        return [
            'gr_accounts' => 'array', 
            'gr_accounts.*.id' => 'nullable',
            'gr_accounts.*.group_id' => 'nullable|exists:organization_groups,id',
            'gr_accounts.*.company_id' => 'required|exists:organization_companies,id',
            'gr_accounts.*.organization_id' => 'required|exists:organizations,id', 
            'gr_accounts.*.ledger_group_id' => 'required|exists:erp_groups,id',
            'gr_accounts.*.ledger_id' => 'required|exists:erp_ledgers,id',
            'gr_accounts.*.category_id' => 'nullable|exists:erp_categories,id',
            'gr_accounts.*.sub_category_id' => 'nullable|exists:erp_categories,id', 
            'gr_accounts.*.item_id' => 'nullable|array', 
            'gr_accounts.*.item_id.*' => 'exists:erp_items,id', 
            'gr_accounts.*.book_id' => 'nullable|exists:erp_books,id',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $grAccounts = $this->input('gr_accounts', []); 
            $existingCombinations = []; 
            $usedCombinations = [];
            $usedItemCombinations = [];
            $usedBookCombinations = [];
    
            foreach ($grAccounts as $index => $grAccount) {
                $companyId = $grAccount['company_id'] ?? null;
                $organizationId = $grAccount['organization_id'] ?? null;
                $ledgerId = $grAccount['ledger_id'] ?? null;
                $ledgerGroupId = $grAccount['ledger_group_id'] ?? null;
                $categoryId = $grAccount['category_id'] ?? null;
                $subCategoryId = $grAccount['sub_category_id'] ?? null;
                $itemIds = $grAccount['item_id'] ?? [];
                $bookIds = $stockAccount['book_id'] ?? [];
                $itemIds = is_array($itemIds) ? $itemIds : [$itemIds];
                $bookIds = is_array($bookIds) ? $bookIds : [$bookIds]; 
    
                $combinationKey = $companyId . '-' . $organizationId . '-' . $ledgerId . '-' . $ledgerGroupId . '-' . $categoryId . '-' . $subCategoryId;
    
                if (empty($itemIds && $bookIds )) {
                    if (isset($existingCombinations[$combinationKey])) {
                        $validator->errors()->add("gr_accounts.{$index}.company_id", 'These combination already exists.');
                    } else {
                        $existingCombinations[$combinationKey] = true;
                    }
                }
                if($itemIds){
                    foreach ($itemIds as $itemId) {
                        $itemCombinationKey = $combinationKey . '-' . $itemId;
                        if (isset($usedItemCombinations[$itemCombinationKey])) {
                            $validator->errors()->add("gr_accounts.{$index}.company_id", "These combination already exists.");
                        } else {
                            $usedItemCombinations[$itemCombinationKey] = true;
                        }
                    }
                  }

                if ($bookIds) {
                    foreach ($bookIds as $bookId) {
                        $bookCombinationKey = $combinationKey . '-' . $bookId;
                        if (isset($usedBookCombinations[$bookCombinationKey])) {
                            $validator->errors()->add("gr_accounts.{$index}.company_id", "These combination already exists.");
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
            'gr_accounts.*.group_id.exists' => 'The selected group does not exist.',
            'gr_accounts.*.company_id.exists' => 'The selected company does not exist.',
            'gr_accounts.*.organization_id.exists' => 'The selected organization does not exist.',
            'gr_accounts.*.ledger_group_id.exists' => 'The selected ledger group does not exist.',
            'gr_accounts.*.ledger_group_id.required' => 'The ledger group field is required.',
            'gr_accounts.*.ledger_id.exists' => 'The selected ledger does not exist.',
            'gr_accounts.*.category_id.exists' => 'The selected category does not exist.',
            'gr_accounts.*.sub_category_id.exists' => 'The selected item group does not exist.',
            'gr_accounts.*.item_id.exists' => 'The selected item does not exist.',
            'gr_accounts.*.book_id.exists' => 'The selected book does not exist.',
            'gr_accounts.*.company_id.required' => 'The company field is required.',
            'gr_accounts.*.organization_id.required' => 'The organization field is required.',
            'gr_accounts.*.ledger_id.required' => 'The ledger field is required.',
        ];
    }
}
