<?php
namespace App\Helpers;
use App\Models\StockAccount;
use App\Models\Organization;
use App\Models\Item;
use App\Models\Book;
use Illuminate\Support\Facades\Log;

class StockAccountHelper
{
    public static function getLedgerGroupAndLedgerId($organizationId = null, $itemId = null, $bookId = null)
    {
        $query = StockAccount::query();
        if ($organizationId !== null) {
            $organization = Organization::find($organizationId);
            if ($organization && $organization->group_id !== null) {
                $query->where('group_id', $organization->group_id);
                $stockAccounts = $query->get();
                if ($stockAccounts->isEmpty()) {
                    return ['message' => 'Record not found for the given Group ID.'];
                }
            } else {
                return ['message' => 'Organization not found or does not have a group ID.'];
            }
        } else {
            return ['message' => 'Organization ID is required to proceed.'];
        }
    
        if ($organization && $organization->company_id !== null) {
            $query->where(function ($query) use ($organization) {
                $query->where('company_id', $organization->company_id)
                      ->orWhereNull('company_id');
            });
            $stockAccounts = $query->get();
            if ($stockAccounts->isEmpty()) {
                return ['message' => 'Record not found for the given Company ID.'];
            }
        } else {
            return ['message' => 'Organization does not have a valid Company ID.'];
        }
    
        if ($organization && $organization->id !== null) {
            $query->where(function ($query) use ($organization) {
                $query->where('organization_id', $organization->id)
                      ->orWhereNull('organization_id');
            });
            $stockAccounts = $query->get();
            if ($stockAccounts->isEmpty()) {
                return ['message' => 'Record not found for the given Organization ID.'];
            }
        } else {
            return ['message' => 'Organization ID is required to filter stock accounts.'];
        }
    
        if ($itemId !== null) {
            $itemIds = is_array($itemId) ? $itemId : [$itemId];
            $items = Item::whereIn('id', $itemIds)->get();
            
            if ($items->isEmpty()) {
                return ['message' => 'Record not found for the given item IDs.'];
            }

            foreach ($items as $item) {
                if ($item->category_id !== null) {
                    $query->where(function ($query) use ($item) {
                        $query->where('category_id', $item->category_id)
                              ->orWhereNull('category_id');
                    });
                    $stockAccounts = $query->get();
                    if ($stockAccounts->isEmpty()) {
                        return ['message' => 'Record not found for the given category ID.'];
                    }
                }
    
                if ($item->subcategory_id !== null) {
                    $query->where(function ($query) use ($item) {
                        $query->where('sub_category_id', $item->subcategory_id)
                              ->orWhereNull('sub_category_id');
                    });
                    $stockAccounts = $query->get();
                    if ($stockAccounts->isEmpty()) {
                        return ['message' => 'Record not found for the given subcategory ID.'];
                    }
                }
            }
            $query->where(function ($query) use ($itemIds) {
                foreach ($itemIds as $id) {
                    $query->orWhereRaw("JSON_CONTAINS(item_id, ?)", [json_encode([$id])])
                          ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(item_id, '$[*]')) LIKE ?", ['%' . $id . '%'])
                          ->orWhereNull('item_id');
                }
            });
    
            $stockAccounts = $query->get();
            if ($stockAccounts->isEmpty()) {
                return ['message' => 'Record not found for the given item IDs in stock accounts.'];
            }
        }
    
        if ($bookId !== null) {
            $query->where(function ($query) use ($bookId) {
                $query->where('book_id', $bookId)
                      ->orWhereNull('book_id');
            });
            $stockAccounts = $query->get();
            if ($stockAccounts->isEmpty()) {
                return ['message' => 'Record not found for the given Book ID.'];
            }
        }
    
        $stockAccounts = $query->get();
        if ($stockAccounts->isEmpty()) {
            return ['message' => 'Record not found with the applied filters.'];
        }
    
        return $stockAccounts->map(function ($stockAccount) {
            return [
                'ledger_group' => $stockAccount->ledgerGroup ? $stockAccount->ledgerGroup->id : null,
                'ledger_id' => $stockAccount->ledger ? $stockAccount->ledger->id : null,
            ];
        });
    }

}
