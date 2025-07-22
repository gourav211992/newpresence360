<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;

class ServiceAccountRequest extends FormRequest
{
    public function rules()
    {

        return [
            'service_accounts' => 'array', 
            'service_accounts.*.id' => 'nullable',
            'service_accounts.*.group_id' => 'nullable|exists:organization_groups,id',
            'service_accounts.*.company_id' => 'required|exists:organization_companies,id',
            'service_accounts.*.organization_id' => 'required|exists:organizations,id',
            'service_accounts.*.category_id' => 'nullable|exists:erp_categories,id',
            'service_accounts.*.sub_category_id' => 'nullable|exists:erp_categories,id',
            'service_accounts.*.item_id' => 'nullable|array',
            'service_accounts.*.item_id.*' => 'exists:erp_items,id',
            'service_accounts.*.book_id' => 'nullable|exists:erp_books,id',
            'service_accounts.*.ledger_id' => 'required|exists:erp_ledgers,id',
            'service_accounts.*.ledger_group_id' => 'required|exists:erp_groups,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $serviceAccounts = $this->input('service_accounts', []); 
            $existingCombinations = [];
            $usedCombinations = [];
            $usedItemCombinations = [];
            $usedBookCombinations = [];

            foreach ($serviceAccounts as $index => $serviceAccount) { 
                $companyId = $serviceAccount['company_id'] ?? null;
                $organizationId = $serviceAccount['organization_id'] ?? null;
                $ledgerId = $serviceAccount['ledger_id'] ?? null;
                $ledgerGroupId = $serviceAccount['ledger_group_id'] ?? null;
                $categoryId = $serviceAccount['category_id'] ?? null;
                $subCategoryId = $serviceAccount['sub_category_id'] ?? null;
                $itemIds = $serviceAccount['item_id'] ?? [];
                $bookIds = $serviceAccount['book_id'] ?? [];
                $itemIds = is_array($itemIds) ? $itemIds : [$itemIds];
                $bookIds = is_array($bookIds) ? $bookIds : [$bookIds];

                $combinationKey = $companyId . '-' . $organizationId . '-' . $ledgerId . '-' . $ledgerGroupId . '-' . $categoryId . '-' . $subCategoryId;

                  if (empty($itemIds && $bookIds )) {
                    if (isset($existingCombinations[$combinationKey])) {
                        $validator->errors()->add("service_accounts.{$index}.company_id", 'These combination already exists.');
                    } else {
                        $existingCombinations[$combinationKey] = true;
                    }
                }

                if($itemIds){
                    foreach ($itemIds as $itemId) {
                        $itemCombinationKey = $combinationKey . '-' . $itemId;
                        if (isset($usedItemCombinations[$itemCombinationKey])) {
                            $validator->errors()->add("service_accounts.{$index}.company_id", "These combination already exists.");
                        } else {
                            $usedItemCombinations[$itemCombinationKey] = true;
                        }
                    }
                  }

                if ($bookIds) {
                    foreach ($bookIds as $bookId) {
                        $bookCombinationKey = $combinationKey . '-' . $bookId;
                        if (isset($usedBookCombinations[$bookCombinationKey])) {
                            $validator->errors()->add("service_accounts.{$index}.company_id", "These combination already exists.");
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
            'service_accounts.*.group_id.exists' => 'The selected group does not exist.', 
            'service_accounts.*.company_id.exists' => 'The selected company does not exist.',
            'service_accounts.*.organization_id.exists' => 'The selected organization does not exist.',
            'service_accounts.*.category_id.exists' => 'The selected category does not exist.',
            'service_accounts.*.sub_category_id.exists' => 'The selected item-group does not exist.',
            'service_accounts.*.item_id.exists' => 'The selected item does not exist.',
            'service_accounts.*.book_id.exists' => 'The selected book does not exist.',
            'service_accounts.*.ledger_id.exists' => 'The selected ledger does not exist.',
            'service_accounts.*.ledger_group_id.exists' => 'The selected ledger group does not exist.',
            'service_accounts.*.ledger_group_id.required' => 'The ledger group field is required.',
            'service_accounts.*.company_id.required' => 'The company field is required.',
            'service_accounts.*.organization_id.required' => 'The organization field is required.',
            'service_accounts.*.ledger_id.required' => 'The ledger field is required.',
        ];
    }
}