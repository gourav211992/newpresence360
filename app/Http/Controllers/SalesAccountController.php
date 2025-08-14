<?php

namespace App\Http\Controllers;
use App\Models\SalesAccount;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use App\Models\Category;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\Item;
use App\Models\Book;
use App\Models\Customer;
use App\Http\Requests\SalesAccountRequest; 
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\AccountHelper;
use Illuminate\Support\Facades\DB;
use Auth;

class SalesAccountController extends Controller
{
    public function index(Request $request, $id = null)
    {
        $user = Helper::getAuthenticatedUser();
        $userType = Helper::userCheck()['type'];
        $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
        array_push($orgIds, $user?->organization_id);
        $orgData = Organization::whereIn('id', $orgIds);
        $companyIds = $orgData
            ->pluck('company_id')
            ->toArray();
        $companies = OrganizationCompany::whereIn('id', $companyIds)->get();
        $ledgerGroups = Group::all();
        $ledgers = Ledger::where('status', '1') ->get();  
        $items = Item::where('status', 'active') ->get();
        $customers = Customer::where('status', 'active') ->get();
        $salesAccount = SalesAccount::query()->get();
        $erpBooks = Book::where('status', 'active') ->get(); 
        return view('procurement.sales-account.index', compact(
            'companies', 'ledgerGroups', 'ledgers', 'items', 'salesAccount','erpBooks','customers','orgIds'
        ));
    }

    public function store(SalesAccountRequest $request)
    {
        DB::beginTransaction();
        try {
        $validated = $request->validated();
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization->group_id;
        $insertData = [];
        $updateResults = [];
        foreach ($validated['sales_accounts'] as $salesAccountData) { 
            if (isset($salesAccountData['id']) && $salesAccountData['id']) {
                $existingAccount = SalesAccount::find($salesAccountData['id']);  
                if ($existingAccount) {
                    $existingAccount->update([
                        'group_id' => $groupId,
                        'company_id' => $salesAccountData['company_id'],
                        'organization_id' => $salesAccountData['organization_id'],
                        'ledger_group_id' => $salesAccountData['ledger_group_id'] ?? null,
                        'ledger_id' => $salesAccountData['ledger_id'] ?? null,
                        'customer_category_id' => $salesAccountData['customer_category_id'] ?? null,
                        'customer_sub_category_id' => $salesAccountData['customer_sub_category_id'] ?? null,
                        'customer_id' => $salesAccountData['customer_id'] ?? null,
                        'item_category_id' => $salesAccountData['item_category_id'] ?? null,
                        'item_sub_category_id' => $salesAccountData['item_sub_category_id'] ?? null,
                        'item_id' => $salesAccountData['item_id'] ?? null,
                        'book_id' => $salesAccountData['book_id'] ?? null,
                    ]);
                    $updateResults[] = $existingAccount;
                } else {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => "Sales account with ID {$salesAccountData['id']} not found.",
                    ], 404);
                }
            } else {
                $newSalesAccount = SalesAccount::create([ 
                    'group_id' => $groupId,
                    'company_id' => $salesAccountData['company_id'],
                    'organization_id' => $salesAccountData['organization_id'],
                    'ledger_group_id' => $salesAccountData['ledger_group_id'] ?? null,
                    'ledger_id' => $salesAccountData['ledger_id'] ?? null,
                    'customer_category_id' => $salesAccountData['customer_category_id'] ?? null,
                    'customer_sub_category_id' => $salesAccountData['customer_sub_category_id'] ?? null,
                    'customer_id' => $salesAccountData['customer_id'] ?? null,
                    'item_category_id' => $salesAccountData['item_category_id'] ?? null,
                    'item_sub_category_id' => $salesAccountData['item_sub_category_id'] ?? null,
                    'item_id' => $salesAccountData['item_id'] ?? null,
                    'book_id' => $salesAccountData['book_id'] ?? null,
                ]);
                $insertData[] = $newSalesAccount;
            }
        }
        DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record processed successfully.',
                'inserted' => count($insertData),
                'updated' => count($updateResults),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while processing the record: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function testLedgerGroupAndLedgerId(Request $request)
    {
        $organizationId = $request->query('organization_id', 5);
        $itemId = $request->query('item_id',7);
        $bookId = $request->query('book_id',2); 
        $CustomerId = $request->query('customer_id',8);   
        if ($itemId && is_string($itemId)) {
            $itemId = explode(',', $itemId);
        }
        $ledgerData = AccountHelper::getLedgerGroupAndLedgerIdForSalesAccount( $organizationId,$CustomerId, $itemId, $bookId);
        if ($ledgerData) {
            return response()->json($ledgerData);
        }
        return response()->json(['message' => 'No data found for the given parameters'], 404);
    }
    public function getOrganizationsByCompany($companyId)
    {
        $organizations = Organization::where('company_id', $companyId)
        ->where('status', 'active')
        ->get();

        return response()->json(['organizations' => $organizations]);
    }
    
    public function getDataByOrganization($organizationId)
    {
        $itemCategories = Category::query()
            ->with('parent')
            ->doesntHave('subCategories')
            ->where('type', 'product')
            ->where('status', 'active')
            ->when($organizationId, function ($query) use ($organizationId) {
                $exists = Category::where('organization_id', $organizationId)
                    ->doesntHave('subCategories')
                    ->where('type', 'product')
                    ->where('status', 'active')
                    ->exists();
    
                if ($exists) {
                    $query->where('organization_id', $organizationId);
                }
            })
            ->get();
    
        $customerCategories = Category::query()
            ->with('parent')
            ->doesntHave('subCategories')
            ->where('type', 'Customer')
            ->where('status', 'active')
            ->when($organizationId, function ($query) use ($organizationId) {
                $exists = Category::where('organization_id', $organizationId)
                    ->doesntHave('subCategories')
                    ->where('type', 'Customer')
                    ->where('status', 'active')
                    ->exists();
    
                if ($exists) {
                    $query->where('organization_id', $organizationId);
                }
            })
            ->get();
    
        $ledgers = Ledger::query()
            ->when($organizationId, function ($query) use ($organizationId) {
                $exists = Ledger::where('organization_id', $organizationId)->exists();
    
                if ($exists) {
                    $query->where('organization_id', $organizationId);
                }
            })
            ->get();
    
        $erpBooks = Book::query()
            ->where('status', 'active')
            ->when($organizationId, function ($query) use ($organizationId) {
                $exists = Book::where('organization_id', $organizationId)
                    ->where('status', 'active')
                    ->exists();
    
                if ($exists) {
                    $query->where('organization_id', $organizationId);
                }
            })
            ->get();
    
        $items = Item::query()
            ->where('status', 'active')
            ->when($organizationId, function ($query) use ($organizationId) {
                $exists = Item::where('organization_id', $organizationId)
                    ->where('status', 'active')
                    ->exists();
    
                if ($exists) {
                    $query->where('organization_id', $organizationId);
                }
            })
            ->get();
    
        $customers = Customer::query()
            ->where('status', 'active')
            ->when($organizationId, function ($query) use ($organizationId) {
                $exists = Customer::where('organization_id', $organizationId)
                    ->where('status', 'active')
                    ->exists();
    
                if ($exists) {
                    $query->where('organization_id', $organizationId);
                }
            })
            ->get();
    
        return response()->json([
            'itemCategories' => $itemCategories,
            'customerCategories' => $customerCategories,
            'ledgers' => $ledgers,
            'erpBooks' => $erpBooks,
            'items' => $items,
            'customers' => $customers,
        ]);
    }
    
    public function getCustomerAndSubCategoriesByCategory(Request $request)
    {
        $categoryId = $request->category_id;
        $organizationId = $request->input('organizationId');
        $searchTerm = $request->input('search', '');
    
        $customerQuery = Customer::query()->where('status', 'active');
    
        if ($categoryId && $organizationId) {
            $existsBoth = Customer::where('subcategory_id', $categoryId)
                ->where('status', 'active')
                ->where('organization_id', $organizationId)
                ->exists();
    
            if ($existsBoth) {
                $customers = Customer::where('subcategory_id', $categoryId)
                    ->where('status', 'active')
                    ->where('organization_id', $organizationId)
                    ->get();
            } else {
                $customers = Customer::where('subcategory_id', $categoryId)
                    ->where('status', 'active')
                    ->get();
            }
        } else {
            $customers = collect();
        }
    
        if ($searchTerm) {
             $customers = collect($customers)->filter(function ($customer) use ($searchTerm) {
                return stripos($customer->company_name, $searchTerm) !== false || stripos($customer->customer_code, $searchTerm) !== false;
            })->values();
        }
    
        return response()->json([
            'customers' => $customers
        ]);
    }

    public function getItemsAndSubCategoriesByCategory(Request $request)
    {
        $categoryId = $request->category_id;
        $organizationId = $request->input('organizationId');
        if ($categoryId && $organizationId) {
            $existsBoth = Item::where('subcategory_id', $categoryId)
                ->where('status', 'active')
                ->where('organization_id', $organizationId)
                ->exists();
        
            if ($existsBoth) {
                $items = Item::where('subcategory_id', $categoryId)
                    ->where('status', 'active')
                    ->where('organization_id', $organizationId)
                    ->get();
            } else {
                $items = Item::where('subcategory_id', $categoryId)
                    ->where('status', 'active')
                    ->get();
            }
        } else {
            $items = collect();
        }
        return response()->json([
            'items' => $items
        ]);
    }

        public function getCategoriesByOrganization(Request $request, $organizationId)
    {
        $searchTerm = $request->input('search', '');
    
        // Item Categories
        $itemCategoriesQuery = Category::query()
            ->with('parent')
            ->doesntHave('subCategories')
            ->where('status', 'active')
            ->where('type', 'Product');
    
        $itemCategoriesQuery->when($organizationId, function ($q) use ($organizationId) {
            $exists = Category::query()
                ->doesntHave('subCategories')
                ->where('type', 'Product')
                ->where('status', 'active')
                ->where('organization_id', $organizationId)
                ->exists();
    
            if ($exists) {
                $q->where('organization_id', $organizationId);
            }
        });
    
        if ($searchTerm) {
            $itemCategoriesQuery->where('name', 'LIKE', "%$searchTerm%");
        }
    
        $itemCategories = $itemCategoriesQuery->get(['id', 'name', 'parent_id']);
    
        // Customer Categories
        $customerCategoriesQuery = Category::query()
            ->with('parent')
            ->doesntHave('subCategories')
            ->where('status', 'active')
            ->where('type', 'Customer');
    
        $customerCategoriesQuery->when($organizationId, function ($q) use ($organizationId) {
            $exists = Category::query()
                ->doesntHave('subCategories')
                ->where('type', 'Customer')
                ->where('status', 'active')
                ->where('organization_id', $organizationId)
                ->exists();
    
            if ($exists) {
                $q->where('organization_id', $organizationId);
            }
        });
    
        if ($searchTerm) {
            $customerCategoriesQuery->where('name', 'LIKE', "%$searchTerm%");
        }
    
        $customerCategories = $customerCategoriesQuery->get(['id', 'name', 'parent_id']);
    
        return response()->json([
            'item_categories' => $itemCategories,
            'customer_categories' => $customerCategories
        ]);
    }
    
    public function getLedgersByOrganization(Request $request, $organizationId)
    {
        $searchTerm = $request->input('search', '');
    
        $query = Ledger::query()
            ->where('status', '1');
    
        $query->when($organizationId, function ($q) use ($organizationId) {
            $exists = Ledger::where('organization_id', $organizationId)
                ->where('status', '1')
                ->exists();
    
            if ($exists) {
                $q->where('organization_id', $organizationId);
            }
        });
    
        if ($searchTerm) {
            $query->where('name', 'LIKE', "%$searchTerm%");
        }
    
        $ledgers = $query->get(['id', 'name', 'code']);
    
        return response()->json([
            'ledgers' => $ledgers->isEmpty() ? [] : $ledgers,
        ]);
    }
    
    
    public function getLedgerGroupByLedger(Request $request)
    {
        $ledgerId = $request->input('ledger_id');
        $searchTerm = $request->input('search_term', '');

        if (empty($ledgerId)) {
            return response()->json(['message' => 'No ledger id provided.'], 400);
        }

        $ledger = Ledger::find($ledgerId);

        if (!$ledger) {
            return response()->json(['message' => 'Ledger not found for the provided id.'], 404);
        }
        $ledgerGroups = $ledger->groups(); 

        if ($ledgerGroups->isEmpty()) {
            return response()->json(['message' => 'No groups found for the given ledger.'], 404);
        }
        if ($searchTerm) {
            $ledgerGroups = $ledgerGroups->filter(function ($group) use ($searchTerm) {
                return stripos($group->name, $searchTerm) !== false;
            });
        }
        $ledgerGroupData = $ledgerGroups->map(function($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
            ];
        });

        return response()->json([
            'ledgerGroupData' => $ledgerGroupData,
            'message' => $ledgerGroups->isEmpty() ? 'No groups found for the given ledger.' : 'Groups found.',
        ]);
    }


    public function destroy($id)
    {
        try {
            $salesAccount = SalesAccount::findOrFail($id);
            $result = $salesAccount->deleteWithReferences();
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? [],
                ], 400);
            }

            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully!',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the sales account: ' . $e->getMessage(),
            ], 500);
        }
    }
}