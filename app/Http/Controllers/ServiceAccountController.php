<?php

namespace App\Http\Controllers;
use App\Models\ServiceAccount; 
use App\Models\Organization;
use App\Models\OrganizationCompany;
use App\Models\Category;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\Item;
use App\Models\Book;
use App\Http\Requests\ServiceAccountRequest;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\AccountHelper;
use Illuminate\Support\Facades\DB;
use Auth;

class ServiceAccountController extends Controller
{
    public function index(Request $request, $id = null)
    {
        $user = Helper::getAuthenticatedUser();
        $userType = Helper::userCheck()['type'];
        $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
        array_push($orgIds, $user?->organization_id);
        $companyIds = Organization::whereIn('id', $orgIds)
            ->pluck('company_id')
            ->toArray();
        $companies = OrganizationCompany::whereIn('id', $companyIds)->get();
        $ledgerGroups = Group::all();
        $ledgers = Ledger::where('status', '1') ->get();  
        $items = Item::where('type', 'Service')
            ->where('status', 'active') 
            ->get();
        $serviceAccounts = ServiceAccount::query()->get();
        $erpBooks = Book::where('status', 'active') ->get(); 
        return view('procurement.service-account.index', compact(
            'companies', 'ledgerGroups', 'ledgers', 'items', 'serviceAccounts','erpBooks','orgIds'
        ));
    }

    public function store(ServiceAccountRequest $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $groupId = $organization->group_id;
            $insertData = [];
            $updateResults = [];

            foreach ($validated['service_accounts'] as $serviceAccountData) {
                if (isset($serviceAccountData['id']) && $serviceAccountData['id']) {
                    $existingAccount = ServiceAccount::find($serviceAccountData['id']);
                    if ($existingAccount) {
                        $existingAccount->update([
                            'group_id' => $groupId,
                            'company_id' => $serviceAccountData['company_id'],
                            'organization_id' => $serviceAccountData['organization_id'],
                            'ledger_group_id' => $serviceAccountData['ledger_group_id'] ?? null,
                            'ledger_id' => $serviceAccountData['ledger_id'] ?? null,
                            'category_id' => $serviceAccountData['category_id'] ?? null,
                            'sub_category_id' => $serviceAccountData['sub_category_id'] ?? null,
                            'item_id' => $serviceAccountData['item_id'] ?? null,
                            'book_id' => $serviceAccountData['book_id'] ?? null,
                        ]);
                        $updateResults[] = $existingAccount;
                    } else {
                        DB::rollBack();
                        return response()->json([
                            'status' => false,
                            'message' => "Service account with ID {$serviceAccountData['id']} not found.",
                        ], 404);
                    }
                } else {
                    $newServiceAccount = ServiceAccount::create([
                        'group_id' => $groupId,
                        'company_id' => $serviceAccountData['company_id'],
                        'organization_id' => $serviceAccountData['organization_id'],
                        'ledger_group_id' => $serviceAccountData['ledger_group_id'] ?? null,
                        'ledger_id' => $serviceAccountData['ledger_id'] ?? null,
                        'category_id' => $serviceAccountData['category_id'] ?? null,
                        'sub_category_id' => $serviceAccountData['sub_category_id'] ?? null,
                        'item_id' => $serviceAccountData['item_id'] ?? null,
                        'book_id' => $serviceAccountData['book_id'] ?? null,
                    ]);
                    $insertData[] = $newServiceAccount;
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
        $organizationId = $request->query('organization_id', 1);
        $itemId = $request->query('item_id',1);
        $bookId = $request->query('book_id',7);  

        if ($itemId && is_string($itemId)) {
            $itemId = explode(',', $itemId);
        }

        $ledgerData = AccountHelper::getServiceLedgerGroupAndLedgerId($organizationId, $itemId, $bookId);
        
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
    
        // Item fetch with 'type' = 'Service'
        $items = Item::query()
            ->where('status', 'active')
            ->where('type', 'Service')
            ->when($organizationId, function ($query) use ($organizationId) {
                $exists = Item::where('organization_id', $organizationId)
                    ->where('status', 'active')
                    ->where('type', 'Service')
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
                ->where('type', 'Service')  
                ->exists();
    
            if ($existsBoth) {
                $items = Item::where('subcategory_id', $categoryId)
                    ->where('status', 'active')
                    ->where('organization_id', $organizationId)
                    ->where('type', 'Service')  
                    ->get();
            } else {
                $items = Item::where('subcategory_id', $categoryId)
                    ->where('status', 'active')
                    ->where('type', 'Service') 
                    ->get();
            }
        } else {
            $items = collect();
        }
    
        return response()->json(['items' => $items]);
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

        $validGroupNames = ['Incomes', 'Expenses'];
        $groupExists = false;

        $query->where(function($q) use ($validGroupNames, &$groupExists) {
            foreach ($validGroupNames as $groupName) {
                $group = Group::where('name', $groupName)->first();
                if ($group) {
                    $childGroupIds = $group->getAllChildIds();
                    $groupIds = array_merge([$group->id], $childGroupIds);
                    $stringGroupIds = array_map('strval', $groupIds);

                    $q->orWhere(function($q2) use ($stringGroupIds) {
                        foreach ($stringGroupIds as $id) {
                            $q2->orWhereJsonContains('ledger_group_id', $id);
                        }
                    });
                    $groupExists = true;
                } else {
                    \Log::warning("Group not found: " . $groupName);
                }
            }
        });

        if (!$groupExists) {
            return response()->json([
                'ledgers' => [],
            ]);
        }

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
            $serviceAccount = ServiceAccount::findOrFail($id); 
            $result = $serviceAccount->deleteWithReferences();  

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
                'message' => 'An error occurred while deleting the service account: ' . $e->getMessage(),
            ], 500);
        }
    }
}