<?php

namespace App\Http\Controllers;
use App\Models\StockAccount;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use App\Models\Category;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\Item;
use App\Models\Book;
use App\Http\Requests\StockAccountRequest;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\AccountHelper;
use Illuminate\Support\Facades\DB;
use Auth;

class StockAccountController extends Controller
{
    public function index(Request $request, $id = null)
    {
        $user = Helper::getAuthenticatedUser();
        $userType = Helper::userCheck()['type'];
        $orgIds = $user -> organizations() -> pluck('organizations.id') -> toArray();
        array_push($orgIds, $user?->organization_id);
        $orgData = Organization::whereIn('id', values: $orgIds);
        $companyIds = $orgData
            ->pluck('company_id')
            ->toArray();
        $companies = OrganizationCompany::whereIn('id', $companyIds)->get();
        $ledgerGroups = Group::all();
        $ledgers = Ledger::where('status', '1') ->get();  
        $items = Item::where('status', 'active') ->get();
        $stockAccount = StockAccount::query()->get();
        $erpBooks = Book::where('status', 'active') ->get(); 
        return view('procurement.stock-account.index', compact(
            'companies', 'ledgerGroups', 'ledgers', 'items', 'stockAccount','erpBooks','orgIds'
        ));
    }

    public function store(StockAccountRequest $request)
    {
        DB::beginTransaction();
       try {
        $validated = $request->validated();
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization->group_id;
        $insertData = [];
        $updateResults = [];
        foreach ($validated['stock_accounts'] as $stockAccountData) {
            if (isset($stockAccountData['id']) && $stockAccountData['id']) {
                $existingAccount = StockAccount::find($stockAccountData['id']);
                if ($existingAccount) {
                    $existingAccount->update([
                        'group_id' => $groupId,
                        'company_id' => $stockAccountData['company_id'],
                        'organization_id' => $stockAccountData['organization_id'],
                        'ledger_group_id' => $stockAccountData['ledger_group_id'] ?? null,
                        'ledger_id' => $stockAccountData['ledger_id'] ?? null,
                        'category_id' => $stockAccountData['category_id'] ?? null,
                        'sub_category_id' => $stockAccountData['sub_category_id'] ?? null,
                        'item_id' => $stockAccountData['item_id'] ?? null,
                        'book_id' => $stockAccountData['book_id'] ?? null,
                    ]);
                    $updateResults[] = $existingAccount;
                } else {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => "Stock account with ID {$stockAccountData['id']} not found.",
                    ], 404);
                }
            } else {
                $newStockAccount = StockAccount::create([
                    'group_id' =>$groupId,
                    'company_id' => $stockAccountData['company_id'],
                    'organization_id' => $stockAccountData['organization_id'],
                    'ledger_group_id' => $stockAccountData['ledger_group_id'] ?? null,
                    'ledger_id' => $stockAccountData['ledger_id'] ?? null,
                    'category_id' => $stockAccountData['category_id'] ?? null,
                    'sub_category_id' => $stockAccountData['sub_category_id'] ?? null,
                    'item_id' => $stockAccountData['item_id'] ?? null,
                    'book_id' => $stockAccountData['book_id'] ?? null,
                ]);
                $insertData[] = $newStockAccount;
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
        $itemId = $request->query('item_id',5);
        $bookId = $request->query('book_id',1);
        if ($itemId && is_string($itemId)) {
            $itemId = explode(',', $itemId);
        }

        $ledgerData = AccountHelper::getStockLedgerGroupAndLedgerId( $organizationId, $itemId, $bookId);
        if ($ledgerData) {
            return response()->json($ledgerData);
        }
        return response()->json(['message' => 'No data found for the given parameters'], 404);
    }

    public function getOrganizationsByCompany($companyId)
    {
        $user = Helper::getAuthenticatedUser();
        $orgIds = $user -> organizations() -> pluck('organizations.id') -> toArray();
        array_push($orgIds, $user?->organization_id);
        $organizations = Organization::where('company_id', $companyId)
            ->whereIn('id', $orgIds)
            ->where('status', 'active')
            ->get();

        return response()->json(['organizations' => $organizations]);
    }

    public function getDataByOrganization($organizationId)
    {
        // Ledger fetch
        $ledgers = Ledger::query()
            ->where('status', '1')
            ->when($organizationId, function ($query) use ($organizationId) {
                $exists = Ledger::where('organization_id', $organizationId)
                    ->where('status', '1')
                    ->exists();
    
                if ($exists) {
                    $query->where('organization_id', $organizationId);
                }
            })
            ->get();
    
        // Book fetch
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
    
        // Item fetch with 'type' = 'Goods'
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
    
        return response()->json([
            'ledgers' => $ledgers,
            'erpBooks' => $erpBooks,
            'items' => $items
        ]);
    }
    
    public function getCategoriesByOrganization(Request $request, $organizationId)
    {
        $searchTerm = $request->input('search', '');
    
        $query = Category::query()
            ->with('parent')
            ->doesntHave('subCategories')
            ->where('type', 'Product')
            ->where('status', 'active'); 
    
        $query->when($organizationId, function ($q) use ($organizationId) {
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
            $query->where('name', 'LIKE', "%$searchTerm%");
        }
    
        $categories = $query->get(['id', 'name', 'parent_id']);
    
        if ($categories->isEmpty()) {
            return response()->json([
                'message' => 'No categories found for the provided organization.'
            ], 404);
        }
    
        return response()->json([
            'categories' => $categories
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
            $stockAccount = StockAccount::findOrFail($id);
            $result = $stockAccount->deleteWithReferences();
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
                'message' => 'An error occurred while deleting the stock account: ' . $e->getMessage(),
            ], 500);
        }
    }
}
