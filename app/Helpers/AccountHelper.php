<?php
namespace App\Helpers;
use App\Models\LandParcel;
use App\Models\StockAccount;
use App\Models\SalesAccount;
use App\Models\CogsAccount;
use App\Models\GrAccount;
use App\Models\WipAccount;
use App\Models\PurchaseReturnAccount;
use App\Models\PriceVarianceAccount;
use App\Models\Organization;
use App\Models\Item;
use App\Models\Book;
use App\Models\ServiceAccount; 
use App\Models\PhysicalStockAccount;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class AccountHelper
{
    public static function getStockLedgerGroupAndLedgerId($organizationId = null, $itemId = null, $bookId = null)
    {
        $query = StockAccount::query();

        if ($organizationId) {
            $organization = Organization::find($organizationId);
            if ($organization && $organization->group_id) {
                $query->where('group_id', $organization->group_id);
            } else {
                return ['message' => 'Organization not found or does not have a group ID.'];
            }
        } else {
            return ['message' => 'Organization ID is required to proceed.'];
        }
    
        if ($organization->company_id) {
            $query->where('company_id', $organization->company_id);
        } else {
            return ['message' => 'Organization does not have a valid Company ID.'];
        }
    
        if ($organization->id) {
            $query->where('organization_id', $organization->id);
        } else {
            return ['message' => 'Organization ID is required to filter stock accounts.'];
        }
    
        if ($bookId) {
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(book_id, '$[*]')) LIKE ?", ['%' . $bookId . '%']);
            $bookQuery = clone $query;
            $stockAccounts = $bookQuery->get();
            if ($stockAccounts->isEmpty()) {
                $query->orWhereNull('book_id');
            }
        }
        
        if ($itemId) {
            $item = Item::find($itemId);
            if (!$item) {
                return ['message' => 'Record not found for the given item IDs.'];
            }
           
    
            if ($item->category_id) {
                $query->where('category_id', $item->category_id);
                $categoryQuery = clone $query;
                $stockAccounts = $categoryQuery->get(); 
                if ($stockAccounts->isEmpty()) {
                    $query->orWhereNull('category_id');
                }
            }
    
            if ($item->subcategory_id) {
                $query->where('sub_category_id', $item->subcategory_id);
                $subCategoryQuery = clone $query;
                $stockAccounts = $subCategoryQuery->get();
                if ($stockAccounts->isEmpty()) {
                    $query->orWhereNull('sub_category_id');
                }
            }
            
    
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(item_id, '$[*]')) LIKE ?", ['%' . $itemId . '%']);
            $itemQuery = clone $query;
            $stockAccounts = $itemQuery->get(); 
            if ($stockAccounts->isEmpty()) {
                $query->orWhereNull('item_id');
            }
        }
    
        $stockAccount = $query->first(); 
        // dd($stockAccount);
        if (!$stockAccount) {
            return ['message' => 'Record not found with the applied filters.'];
        }
    
        return collect([
            [
                'ledger_group' => $stockAccount->ledgerGroup ? $stockAccount->ledgerGroup->id : null,
                'ledger_id' => $stockAccount->ledger ? $stockAccount->ledger->id : null,
            ]
        ]);
        
    }

    public static function getPriceVarianceLedgerGroupAndLedgerId($organizationId = null, $itemId = null, $bookId = null)
    {
        $query = PriceVarianceAccount::query();

        if ($organizationId) {
            $organization = Organization::find($organizationId);
            if ($organization && $organization->group_id) {
                $query->where('group_id', $organization->group_id);
            } else {
                return ['message' => 'Organization not found or does not have a group ID.'];
            }
        } else {
            return ['message' => 'Organization ID is required to proceed.'];
        }

        if ($organization->company_id) {
            $query->where('company_id', $organization->company_id);
        } else {
            return ['message' => 'Organization does not have a valid Company ID.'];
        }

        if ($organization->id) {
            $query->where('organization_id', $organization->id);
        } else {
            return ['message' => 'Organization ID is required to filter price variance accounts.'];
        }

        if ($bookId) {
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(book_id, '$[*]')) LIKE ?", ['%' . $bookId . '%']);
            $bookQuery = clone $query;
            $priceVarianceAccounts = $bookQuery->get();
            if ($priceVarianceAccounts->isEmpty()) {
                $query->orWhereNull('book_id');
            }
        }

        if ($itemId) {
            $item = Item::find($itemId);
            if (!$item) {
                return ['message' => 'Record not found for the given item IDs.'];
            }

            if ($item->category_id) {
                $query->where('category_id', $item->category_id);
                $categoryQuery = clone $query;
                $priceVarianceAccounts = $categoryQuery->get(); 
                if ($priceVarianceAccounts->isEmpty()) {
                    $query->orWhereNull('category_id');
                }
            }

            if ($item->subcategory_id) {
                $query->where('sub_category_id', $item->subcategory_id);
                $subCategoryQuery = clone $query;
                $priceVarianceAccounts = $subCategoryQuery->get();
                if ($priceVarianceAccounts->isEmpty()) {
                    $query->orWhereNull('sub_category_id');
                }
            }

            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(item_id, '$[*]')) LIKE ?", ['%' . $itemId . '%']);
            $itemQuery = clone $query;
            $priceVarianceAccounts = $itemQuery->get(); 
            if ($priceVarianceAccounts->isEmpty()) {
                $query->orWhereNull('item_id');
            }
        }

        $priceVarianceAccount = $query->first(); 
        if (!$priceVarianceAccount) {
            return ['message' => 'Record not found with the applied filters.'];
        }

        return collect([
            [
                'ledger_group' => $priceVarianceAccount->ledgerGroup ? $priceVarianceAccount->ledgerGroup->id : null,
                'ledger_id' => $priceVarianceAccount->ledger ? $priceVarianceAccount->ledger->id : null,
            ]
        ]);
    }

    public static function getPurchaseReturnLedgerGroupAndLedgerId($organizationId = null, $itemId = null, $bookId = null)
    {
        $query = PurchaseReturnAccount::query();

        if ($organizationId) {
            $organization = Organization::find($organizationId);
            if ($organization && $organization->group_id) {
                $query->where('group_id', $organization->group_id);
            } else {
                return ['message' => 'Organization not found or does not have a group ID.'];
            }
        } else {
            return ['message' => 'Organization ID is required to proceed.'];
        }

        if ($organization->company_id) {
            $query->where('company_id', $organization->company_id);
        } else {
            return ['message' => 'Organization does not have a valid Company ID.'];
        }

        if ($organization->id) {
            $query->where('organization_id', $organization->id);
        } else {
            return ['message' => 'Organization ID is required to filter purchase return accounts.'];
        }

        if ($bookId) {
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(book_id, '$[*]')) LIKE ?", ['%' . $bookId . '%']);
            $bookQuery = clone $query;
            $purchaseReturnAccounts = $bookQuery->get();
            if ($purchaseReturnAccounts->isEmpty()) {
                $query->orWhereNull('book_id');
            }
        }

        if ($itemId) {
            $item = Item::find($itemId);
            if (!$item) {
                return ['message' => 'Record not found for the given item IDs.'];
            }

            if ($item->category_id) {
                $query->where('category_id', $item->category_id);
                $categoryQuery = clone $query;
                $purchaseReturnAccounts = $categoryQuery->get(); 
                if ($purchaseReturnAccounts->isEmpty()) {
                    $query->orWhereNull('category_id');
                }
            }

            if ($item->subcategory_id) {
                $query->where('sub_category_id', $item->subcategory_id);
                $subCategoryQuery = clone $query;
                $purchaseReturnAccounts = $subCategoryQuery->get();
                if ($purchaseReturnAccounts->isEmpty()) {
                    $query->orWhereNull('sub_category_id');
                }
            }

            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(item_id, '$[*]')) LIKE ?", ['%' . $itemId . '%']);
            $itemQuery = clone $query;
            $purchaseReturnAccounts = $itemQuery->get(); 
            if ($purchaseReturnAccounts->isEmpty()) {
                $query->orWhereNull('item_id');
            }
        }

        $purchaseReturnAccount = $query->first(); 
        if (!$purchaseReturnAccount) {
            return ['message' => 'Record not found with the applied filters.'];
        }

        return collect([
            [
                'ledger_group' => $purchaseReturnAccount->ledgerGroup ? $purchaseReturnAccount->ledgerGroup->id : null,
                'ledger_id' => $purchaseReturnAccount->ledger ? $purchaseReturnAccount->ledger->id : null,
            ]
        ]);
    }

    
    public static function getLedgerGroupAndLedgerIdForSalesAccount($organizationId = null, $customerId = null, $itemId = null, $bookId = null)
    {
        
        $query = SalesAccount::query();
    
        if ($organizationId) {
            $organization = Organization::find($organizationId);
            if ($organization && $organization->group_id) {
                $query->where('group_id', $organization->group_id);
            } else {
                return ['message' => 'Organization not found or does not have a group ID.'];
            }
        } else {
            return ['message' => 'Organization ID is required to proceed.'];
        }
    
        if ($organization && $organization->company_id) {
            $query->where(function ($query) use ($organization) {
                $query->where('company_id', $organization->company_id);
            });
        } else {
            return ['message' => 'Organization does not have a valid Company ID.'];
        }
    
        if ($organization && $organization->id) {
            $query->where(function ($query) use ($organization) {
                $query->where('organization_id', $organization->id);
            });
        } else {
            return ['message' => 'Organization ID is required to filter sales accounts.'];
        }

        if ($bookId) {
            $query->where('book_id', $bookId);
                $bookQuery = clone $query;
                $salesAccounts = $bookQuery->get();
                if ($salesAccounts->isEmpty()) {
                    $query->orWhereNull('book_id');
                }
        }

        if ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                if ($customer->category_id) {
                    $query->where(function ($query) use ($customer) {
                        $query->where('customer_category_id', $customer->category_id);
                    });
                    $customerCategoryQuery = clone $query;
                    $salesAccounts = $customerCategoryQuery->get();
                    if ($salesAccounts->isEmpty()) {
                        $query->orWhereNull('customer_category_id');
                    }
                }
    
                if ($customer->subcategory_id) {
                    $query->where(function ($query) use ($customer) {
                        $query->where('customer_sub_category_id', $customer->subcategory_id);
                    });
                    $customerSubCategoryQuery = clone $query;
                    $salesAccounts = $customerSubCategoryQuery->get();
                    if ($salesAccounts->isEmpty()) {
                        $query->orWhereNull('customer_sub_category_id');
                    }
                }

               $query->where('customer_id', $customerId);
                $customerQuery = clone $query;
                $salesAccounts = $customerQuery->get();
                if ($salesAccounts->isEmpty()) {
                    $query->orWhereNull('customer_id');
                }
            } else {
                return ['message' => 'Customer not found.'];
            }
        }

        if ($itemId) {
            $item = Item::find($itemId);
            if (!$item) {
                return ['message' => 'Record not found for the given item IDs.'];
            }
            if ($item->category_id) {
                $query->where(function ($query) use ($item) {
                    $query->where('item_category_id', $item->category_id);
                });
                $itemCategoryQuery = clone $query;
                $salesAccounts = $itemCategoryQuery->get();
                if ($salesAccounts->isEmpty()) {
                    $query->orWhereNull('item_category_id');
                }
            }

            if ($item->subcategory_id) {
                $query->where(function ($query) use ($item) {
                    $query->where('item_sub_category_id', $item->subcategory_id);
                });
                $itemSubCategoryQuery = clone $query;
                $salesAccounts = $itemSubCategoryQuery->get();
                if ($salesAccounts->isEmpty()) {
                    $query->orWhereNull('item_sub_category_id');
                }
            }
            $query->where('item_id', $itemId);
               $itemQuery = clone $query;
                $salesAccounts = $itemQuery->get(); 
                if ($salesAccounts->isEmpty()) {
                    $query->orWhereNull('item_id');
                }
        }
    
        $salesAccounts = $query->first(); 

        if (!$salesAccounts) {
            return ['message' => 'Record not found with the applied filters.'];
        }
    
        return collect([
            [
                'ledger_group' => $salesAccounts->ledgerGroup ? $salesAccounts->ledgerGroup->id : null,
                'ledger_id' => $salesAccounts->ledger ? $salesAccounts->ledger->id : null,
            ]
        ]);
        
        
    }

    public static function getLedgerGroupAndLedgerIdForLeaseRevenue(int $landParcelId, string $itemType)
    {
        $ledgerId = null;
        $ledgerGroupId = null;
        $landParcel = LandParcel::find($landParcelId);
        $serviceItems = (json_decode($landParcel -> service_item, true));
        if ($landParcel) {
            foreach ($serviceItems as $serviceItem) {
                if (($serviceItem["'servicetype'"] == $itemType)) {
                    $ledgerId = $serviceItem["'ledger_id'"];
                    $ledgerGroupId = $serviceItem["'ledger_group_id'"];
                    break;
                }
            }
        }
        return [
            'ledger_id' => $ledgerId,
            'ledger_group_id' => $ledgerGroupId
        ];
    }

    public static function getCogsLedgerGroupAndLedgerId($organizationId = null, $itemId = null, $bookId = null)
    {
        $query = CogsAccount::query();

        if ($organizationId) {
            $organization = Organization::find($organizationId);
            if ($organization && $organization->group_id) {
                $query->where('group_id', $organization->group_id);
            } else {
                return ['message' => 'Organization not found or does not have a group ID.'];
            }
        } else {
            return ['message' => 'Organization ID is required to proceed.'];
        }
    
        if ($organization->company_id) {
            $query->where('company_id', $organization->company_id);
        } else {
            return ['message' => 'Organization does not have a valid Company ID.'];
        }

        if ($organization->id) {
            $query->where('organization_id', $organization->id);
        } else {
            return ['message' => 'Organization ID is required to filter cogs accounts.'];
        }

        if ($bookId) {
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(book_id, '$[*]')) LIKE ?", ['%' . $bookId . '%']);
            $bookQuery = clone $query;
            $cogsAccounts = $bookQuery->get();
            if ($cogsAccounts->isEmpty()) {
                $query->orWhereNull('book_id');
            }
        }
  
        if ($itemId) {
            $item = Item::find($itemId);
            if (!$item) {
                return ['message' => 'Record not found for the given item ID.'];
            }
    
            if ($item->category_id) {
                $query->where('category_id', $item->category_id);
                $itemCategoryQuery = clone $query;
                $cogsAccounts = $itemCategoryQuery->get();
                if ($cogsAccounts->isEmpty()) {
                    $query->orWhereNull('category_id');
                }
            }

            if ($item->subcategory_id) {
                $query->where('sub_category_id', $item->subcategory_id);
                $itemSubCategoryQuery = clone $query;
                $cogsAccounts = $itemSubCategoryQuery->get();
                if ($cogsAccounts->isEmpty()) {
                    $query->orWhereNull('sub_category_id');
                }
            }

            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(item_id, '$[*]')) LIKE ?", ['%' . $itemId . '%']);
            $itemQuery = clone $query;
            $cogsAccounts = $itemQuery->get();
            if ($cogsAccounts->isEmpty()) {
                $query->orWhereNull('item_id');
            }
        }

        $cogsAccount = $query->first();
        if (!$cogsAccount) {
            return ['message' => 'Record not found with the applied filters.'];
        }
    
        return collect([
            [
                'ledger_group' => $cogsAccount->ledgerGroup ? $cogsAccount->ledgerGroup->id : null,
                'ledger_id' => $cogsAccount->ledger ? $cogsAccount->ledger->id : null,
            ]
        ]);
        
    }
    

    public static function getGrLedgerGroupAndLedgerId($organizationId = null, $itemId = null, $bookId = null)
    {
        $query = GrAccount::query();
    
        if ($organizationId) {
            $organization = Organization::find($organizationId);
            if ($organization && $organization->group_id) {
                $query->where('group_id', $organization->group_id);
            } else {
                return ['message' => 'Organization not found or does not have a group ID.'];
            }
        } else {
            return ['message' => 'Organization ID is required to proceed.'];
        }
  
        if ($organization->company_id) {
            $query->where('company_id', $organization->company_id);
        } else {
            return ['message' => 'Organization does not have a valid Company ID.'];
        }

        if ($organization->id) {
            $query->where('organization_id', $organization->id);
        } else {
            return ['message' => 'Organization ID is required to filter GR accounts.'];
        }
    
        if ($bookId) {
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(book_id, '$[*]')) LIKE ?", ['%' . $bookId . '%']);
            $bookQuery = clone $query;
            $grAccounts = $bookQuery->get();
            if ($grAccounts->isEmpty()) {
                $query->orWhereNull('book_id');
            }
        }
    
        if ($itemId) {
            $item = Item::find($itemId);
           
            if (!$item) {
                return ['message' => 'Record not found for the given item ID.'];
            }
 
            if ($item->category_id) {
                $query->where('category_id', $item->category_id);
                $itemCategoryQuery = clone $query;
                $grAccounts = $itemCategoryQuery->get();
                if ($grAccounts->isEmpty()) {
                    $query->orWhereNull('category_id');
                }
            }

            if ($item->subcategory_id) {
                $query->where('sub_category_id', $item->subcategory_id);
                $itemSubCategoryQuery = clone $query;
                $grAccounts = $itemSubCategoryQuery->get();
                if ($grAccounts->isEmpty()) {
                    $query->orWhereNull('sub_category_id');
                }
            }

            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(item_id, '$[*]')) LIKE ?", ['%' . $itemId . '%']);
            $itemQuery = clone $query;
            $grAccounts = $itemQuery->get();
            if ($grAccounts->isEmpty()) {
                $query->orWhereNull('item_id');
            }
        }

        $grAccount = $query->first();
        if (!$grAccount) {
            return ['message' => 'Record not found with the applied filters.'];
        }
    
        return collect([
            [
                'ledger_group' => $grAccount->ledgerGroup ? $grAccount->ledgerGroup->id : null,
                'ledger_id' => $grAccount->ledger ? $grAccount->ledger->id : null,
            ]
        ]);
        
    }

    public static function getWipLedgerGroupAndLedgerId($organizationId = null,$bookId = null)
    {
        $query = WipAccount::query(); 

        if ($organizationId) {
            $organization = Organization::find($organizationId);
            if ($organization && $organization->group_id) {
                $query->where('group_id', $organization->group_id);
            } else {
                return ['message' => 'Organization not found or does not have a group ID.'];
            }
        } else {
            return ['message' => 'Organization ID is required to proceed.'];
        }

        if ($organization->company_id) {
            $query->where('company_id', $organization->company_id);
        } else {
            return ['message' => 'Organization does not have a valid Company ID.'];
        }

        if ($organization->id) {
            $query->where('organization_id', $organization->id);
        } else {
            return ['message' => 'Organization ID is required to filter WIP accounts.'];
        }

        if ($bookId) {
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(book_id, '$[*]')) LIKE ?", ['%' . $bookId . '%']);
            $bookQuery = clone $query;
            $wipAccounts = $bookQuery->get();
            if ($wipAccounts->isEmpty()) {
                $query->orWhereNull('book_id');
            }
        }

        $wipAccount = $query->first(); 

        if (!$wipAccount) {
            return ['message' => 'Record not found with the applied filters.'];
        }

        return collect([
            [
                'ledger_group' => $wipAccount->ledgerGroup ? $wipAccount->ledgerGroup->id : null, 
                'ledger_id' => $wipAccount->ledger ? $wipAccount->ledger->id : null,
            ]
        ]);
    }

    public static function getServiceLedgerGroupAndLedgerId($organizationId = null, $itemId = null, $bookId = null)
    {
        $query = ServiceAccount::query();

        if ($organizationId) {
            $organization = Organization::find($organizationId);
            if ($organization && $organization->group_id) {
                $query->where('group_id', $organization->group_id);
            } else {
                return ['message' => 'Organization not found or does not have a group ID.'];
            }
        } else {
            return ['message' => 'Organization ID is required to proceed.'];
        }

        if ($organization->company_id) {
            $query->where('company_id', $organization->company_id);
        } else {
            return ['message' => 'Organization does not have a valid Company ID.'];
        }

        if ($organization->id) {
            $query->where('organization_id', $organization->id);
        } else {
            return ['message' => 'Organization ID is required to filter Service accounts.'];
        }

        if ($bookId) {
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(book_id, '$[*]')) LIKE ?", ['%' . $bookId . '%']);
            $bookQuery = clone $query;
            $serviceAccounts = $bookQuery->get();
            if ($serviceAccounts->isEmpty()) {
                $query->orWhereNull('book_id');
            }
        }

        if ($itemId) {
            $item = Item::find($itemId);

            if (!$item) {
                return ['message' => 'Record not found for the given item ID.'];
            }

            if ($item->category_id) {
                $query->where('category_id', $item->category_id);
                $itemCategoryQuery = clone $query;
                $serviceAccounts = $itemCategoryQuery->get();
                if ($serviceAccounts->isEmpty()) {
                    $query->orWhereNull('category_id');
                }
            }

            if ($item->subcategory_id) {
                $query->where('sub_category_id', $item->subcategory_id);
                $itemSubCategoryQuery = clone $query;
                $serviceAccounts = $itemSubCategoryQuery->get();
                if ($serviceAccounts->isEmpty()) {
                    $query->orWhereNull('sub_category_id');
                }
            }

            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(item_id, '$[*]')) LIKE ?", ['%' . $itemId . '%']);
                $itemQuery = clone $query;
                $serviceAccounts = $itemQuery->get();
                if ($serviceAccounts->isEmpty()) {
                    $query->orWhereNull('item_id');
                }
        }

        $serviceAccount = $query->first();

        if (!$serviceAccount) {
            return ['message' => 'Record not found with the applied filters.'];
        }

        return collect([
            [
                'ledger_group' => $serviceAccount->ledgerGroup ? $serviceAccount->ledgerGroup->id : null,
                'ledger_id' => $serviceAccount->ledger ? $serviceAccount->ledger->id : null,
            ]
        ]);
    }

    public static function getPhysicalStockLedgerGroupAndLedgerId($organizationId = null, $itemId = null)
    {
        $query = PhysicalStockAccount::query();

        if ($organizationId) {
            $organization = Organization::find($organizationId);
            if ($organization && $organization->group_id) {
                $query->where('group_id', $organization->group_id);
            } else {
                return ['message' => 'Organization not found or does not have a group ID.'];
            }
        } else {
            return ['message' => 'Organization ID is required to proceed.'];
        }

        if ($organization->company_id) {
            $query->where('company_id', $organization->company_id);
        } else {
            return ['message' => 'Organization does not have a valid Company ID.'];
        }

        if ($organization->id) {
            $query->where('organization_id', $organization->id);
        } else {
            return ['message' => 'Organization ID is required to filter Physical Stock accounts.'];
        }

        if ($itemId) {
            $item = Item::find($itemId);

            if (!$item) {
                return ['message' => 'Record not found for the given item ID.'];
            }

            if ($item->category_id) {
                $query->where('category_id', $item->category_id);
                $itemCategoryQuery = clone $query;
                $physicalStockAccounts = $itemCategoryQuery->get();
                if ($physicalStockAccounts->isEmpty()) {
                    $query->orWhereNull('category_id');
                }
            }

            if ($item->subcategory_id) {
                $query->where('sub_category_id', $item->subcategory_id);
                $itemSubCategoryQuery = clone $query;
                $physicalStockAccounts = $itemSubCategoryQuery->get();
                if ($physicalStockAccounts->isEmpty()) {
                    $query->orWhereNull('sub_category_id');
                }
            }
              $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(item_id, '$[*]')) LIKE ?", ['%' . $itemId . '%']);
            $itemQuery = clone $query;
            $physicalStockAccounts = $itemQuery->get();
            if ($physicalStockAccounts->isEmpty()) {
                $query->orWhereNull('item_id');
            }
        }

        $physicalStockAccount = $query->first();

        if (!$physicalStockAccount) {
            return ['message' => 'Record not found with the applied filters.'];
        }

        return collect([
            [
                'ledger_group' => $physicalStockAccount->ledgerGroup ? $physicalStockAccount->ledgerGroup->id : null,
                'ledger_id' => $physicalStockAccount->ledger ? $physicalStockAccount->ledger->id : null,
            ]
        ]);
    }

}
