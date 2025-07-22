<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\Helper;

class SalesAccountRequest extends FormRequest
{
    public function authorize()
    {

        return true; 
    }

    public function rules()
    {

        return [
            'sales_accounts' => 'array',
            'sales_accounts.*.id' => 'nullable',
            'sales_accounts.*.group_id' => 'nullable|exists:organization_groups,id', 
            'sales_accounts.*.company_id' => 'required|exists:organization_companies,id',
            'sales_accounts.*.organization_id' => 'required|exists:organizations,id', 
            'sales_accounts.*.customer_category_id' => 'nullable|exists:erp_categories,id', 
            'sales_accounts.*.customer_sub_category_id' => 'nullable|exists:erp_categories,id',
            'sales_accounts.*.customer_id' => 'nullable|exists:erp_customers,id', 
            'sales_accounts.*.item_category_id' => 'nullable|exists:erp_categories,id', 
            'sales_accounts.*.item_sub_category_id' => 'nullable|exists:erp_categories,id', 
            'sales_accounts.*.item_id' => 'nullable|array',
            'sales_accounts.*.item_id.*' => 'exists:erp_items,id', 
            'sales_accounts.*.book_id' => 'nullable|exists:erp_books,id',
            'sales_accounts.*.book_code' => 'nullable|string',
            'sales_accounts.*.ledger_group_id' => 'required|exists:erp_groups,id', 
            'sales_accounts.*.ledger_id' => 'required|exists:erp_ledgers,id',
            'sales_accounts.*.status' => 'nullable|in:active,inactive', 
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $salesAccounts = $this->input('sales_accounts', []); 
            $existingCombinations = [];
            $usedCustomerCombinations = [];
            $usedItemCombinations = [];
            $usedBookCombinations = [];
    
            foreach ($salesAccounts as $index => $salesAccount) {

                $companyId = $salesAccount['company_id'] ?? null;
                $organizationId = $salesAccount['organization_id'] ?? null;
                $ledgerId = $salesAccount['ledger_id'] ?? null;
                $ledgerGroupId = $salesAccount['ledger_group_id'] ?? null;
                $customerCategoryId = $salesAccount['customer_category_id'] ?? null;
                $customerSubCategoryId = $salesAccount['customer_sub_category_id'] ?? null;
                $customerId = $salesAccount['customer_id'] ?? null; 
                $itemCategoryId = $salesAccount['item_category_id'] ?? null;
                $itemSubCategoryId = $salesAccount['item_sub_category_id'] ?? null;
                $itemIds = $salesAccount['item_id'] ?? [];  
                $bookIds = $salesAccount['book_id'] ?? [];
    
                $customerIds = is_array($customerId) ? $customerId : [$customerId];
                $itemIds = is_array($itemIds) ? $itemIds : [$itemIds];
                $bookIds = is_array($bookIds) ? $bookIds : [$bookIds]; 
    
                $combinationKey = $companyId . '-' . $organizationId . '-' . $ledgerId . '-' . $ledgerGroupId .
                    '-' . $customerCategoryId . '-' . $customerSubCategoryId;
                if (empty($customerIds) && empty($itemIds)) {
                    if (isset($existingCombinations[$combinationKey])) {
                        $validator->errors()->add("sales_accounts.{$index}.company_id", 'These combination already exists.');
                    } else {
                        $existingCombinations[$combinationKey] = true;
                    }
                }
                if($customerIds) {
                foreach ($customerIds as $customer) {
                    $customerCombinationKey = $combinationKey . '-' . $customer;
                    if (isset($usedCustomerCombinations[$customerCombinationKey])) {
                        $validator->errors()->add("sales_accounts.{$index}.company_id", "These combination already exists.");
                    } else {
                        $usedCustomerCombinations[$customerCombinationKey] = true;
                    }
                }
               }
               if($itemIds){
                foreach ($itemIds as $itemId) {
                    $itemCombinationKey = $combinationKey . '-' . $itemId;
                    if (isset($usedItemCombinations[$itemCombinationKey])) {
                        $validator->errors()->add("sales_accounts.{$index}.company_id", "These combination already exists.");
                    } else {
                        $usedItemCombinations[$itemCombinationKey] = true;
                    }
                }
              }

              if ($bookIds) {
                foreach ($bookIds as $bookId) {
                    $bookCombinationKey = $combinationKey . '-' . $bookId;
                    if (isset($usedBookCombinations[$bookCombinationKey])) {
                        $validator->errors()->add("sales_accounts.{$index}.company_id", "These combination already exists.");
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
            'sales_accounts.array' => 'Sales accounts should be provided as an array.',
            'sales_accounts.*.group_id.exists' => 'The selected group does not exist.',
            'sales_accounts.*.company_id.required' => 'The company field is required.',
            'sales_accounts.*.company_id.exists' => 'The selected company does not exist.',
            'sales_accounts.*.organization_id.required' => 'The organization field is required.',
            'sales_accounts.*.organization_id.exists' => 'The selected organization does not exist.',
            'sales_accounts.*.customer_category_id.exists' => 'The selected customer category does not exist.',
            'sales_accounts.*.customer_sub_category_id.exists' => 'The selected customer group does not exist.',
            'sales_accounts.*.customer_id.exists' => 'The selected customer does not exist.',
            'sales_accounts.*.item_category_id.exists' => 'The selected item category does not exist.',
            'sales_accounts.*.item_sub_category_id.exists' => 'The selected item group does not exist.',
            'sales_accounts.*.item_id.exists' => 'The selected item does not exist.',
            'sales_accounts.*.item_id.*.exists' => 'One of the selected items does not exist.',
            'sales_accounts.*.book_id.exists' => 'The selected book does not exist.',
            'sales_accounts.*.book_code.string' => 'The book code must be a string.',
            'sales_accounts.*.ledger_group_id.exists' => 'The selected ledger group does not exist.',
            'sales_accounts.*.ledger_group_id.required' => 'The ledger group field is required.',
            'sales_accounts.*.ledger_id.required' => 'The ledger field is required.',
            'sales_accounts.*.ledger_id.exists' => 'The selected ledger does not exist.',
            'sales_accounts.*.status.in' => 'The status must be either "active" or "inactive".',
        ];
        
    }

}
