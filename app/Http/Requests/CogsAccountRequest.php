<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;

class CogsAccountRequest extends FormRequest
{

    public function rules()
    {

        return [
            'cogs_accounts' => 'array', 
            'cogs_accounts.*.id' => 'nullable',
            'cogs_accounts.*.group_id' => 'nullable|exists:organization_groups,id', 
            'cogs_accounts.*.company_id' => 'required|exists:organization_companies,id',
            'cogs_accounts.*.organization_id' => 'required|exists:organizations,id',
            'cogs_accounts.*.ledger_group_id' => 'required|exists:erp_groups,id', 
            'cogs_accounts.*.ledger_id' => 'required|exists:erp_ledgers,id', 
            'cogs_accounts.*.category_id' => 'nullable|exists:erp_categories,id',
            'cogs_accounts.*.sub_category_id' => 'nullable|exists:erp_categories,id', 
            'cogs_accounts.*.item_id' => 'nullable|array',
            'cogs_accounts.*.item_id.*' => 'exists:erp_items,id',
            'cogs_accounts.*.book_id' => 'nullable|exists:erp_books,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cogsAccounts = $this->input('cogs_accounts', []); 
            $existingCombinations = []; 
            $usedCombinations = [];
            $usedItemCombinations = [];
            $usedBookCombinations = [];

            foreach ($cogsAccounts as $index => $cogsAccount) {
                $companyId = $cogsAccount['company_id'] ?? null;
                $organizationId = $cogsAccount['organization_id'] ?? null;
                $ledgerId = $cogsAccount['ledger_id'] ?? null;
                $ledgerGroupId = $cogsAccount['ledger_group_id'] ?? null;
                $categoryId = $cogsAccount['category_id'] ?? null;
                $subCategoryId = $cogsAccount['sub_category_id'] ?? null;
                $itemIds = $cogsAccount['item_id'] ?? [];
                $bookIds = $stockAccount['book_id'] ?? [];

                $itemIds = is_array($itemIds) ? $itemIds : [$itemIds];
                $bookIds = is_array($bookIds) ? $bookIds : [$bookIds]; 

                $combinationKey = $companyId . '-' . $organizationId . '-' . $ledgerId . '-' . $ledgerGroupId . '-' . $categoryId . '-' . $subCategoryId;
                    if (empty($itemIds && $bookIds )) {
                        if (isset($existingCombinations[$combinationKey])) {
                            $validator->errors()->add("cogs_accounts.{$index}.company_id", 'These combination already exists.');
                        } else {
                            $existingCombinations[$combinationKey] = true;
                        }
                    }
                    if($itemIds){
                        foreach ($itemIds as $itemId) {
                            $itemCombinationKey = $combinationKey . '-' . $itemId;
                            if (isset($usedItemCombinations[$itemCombinationKey])) {
                                $validator->errors()->add("cogs_accounts.{$index}.company_id", "These combination already exists.");
                            } else {
                                $usedItemCombinations[$itemCombinationKey] = true;
                            }
                        }
                      }
                    if ($bookIds) {
                        foreach ($bookIds as $bookId) {
                            $bookCombinationKey = $combinationKey . '-' . $bookId;
                            if (isset($usedBookCombinations[$bookCombinationKey])) {
                                $validator->errors()->add("cogs_accounts.{$index}.company_id", "These combination already exists.");
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
            'cogs_accounts.*.group_id.exists' => 'The selected group does not exist.',
            'cogs_accounts.*.company_id.exists' => 'The selected company does not exist.',
            'cogs_accounts.*.organization_id.exists' => 'The selected organization does not exist.',
            'cogs_accounts.*.ledger_group_id.exists' => 'The selected ledger group does not exist.',
            'cogs_accounts.*.ledger_group_id.required' => 'The ledger group field is required.',
            'cogs_accounts.*.ledger_id.exists' => 'The selected ledger does not exist.',
            'cogs_accounts.*.category_id.exists' => 'The selected category does not exist.',
            'cogs_accounts.*.sub_category_id.exists' => 'The selected item-group does not exist.',
            'cogs_accounts.*.item_id.exists' => 'The selected item does not exist.',
            'cogs_accounts.*.book_id.exists' => 'The selected book does not exist.',
            'cogs_accounts.*.company_id.required' => 'The company field is required.',
            'cogs_accounts.*.organization_id.required' => 'The organization field is required.',
            'cogs_accounts.*.ledger_id.required' => 'The ledger field is required.',
        ];
    }
}
