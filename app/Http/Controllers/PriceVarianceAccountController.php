<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Facades\DataTables;
use App\Models\PriceVarianceAccount;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use App\Models\Category;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\Item;
use App\Models\Book;
use App\Http\Requests\PriceVarianceAccountRequest; 
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\AccountHelper;
use Illuminate\Support\Facades\DB;
use Auth;

class PriceVarianceAccountController extends Controller
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
        $items = Item::where('status', 'active') ->get();
        $priceVarianceAccounts = PriceVarianceAccount::query()->get();
        $erpBooks = Book::where('status', 'active') ->get(); 
        
        return view('procurement.price-variance-account.index', compact(
            'companies', 'ledgerGroups', 'ledgers', 'items', 'priceVarianceAccounts', 'erpBooks','orgIds'
        ));
    }

    public function store(PriceVarianceAccountRequest $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $groupId = $organization->group_id;
            $insertData = [];
            $updateResults = [];

            foreach ($validated['price_variance_accounts'] as $priceVarianceAccountData) {
                if (isset($priceVarianceAccountData['id']) && $priceVarianceAccountData['id']) {
                    $existingAccount = PriceVarianceAccount::find($priceVarianceAccountData['id']);
                    if ($existingAccount) {
                        $existingAccount->update([
                            'group_id' => $groupId,
                            'company_id' => $priceVarianceAccountData['company_id'],
                            'organization_id' => $priceVarianceAccountData['organization_id'],
                            'ledger_group_id' => $priceVarianceAccountData['ledger_group_id'] ?? null,
                            'ledger_id' => $priceVarianceAccountData['ledger_id'] ?? null,
                            'category_id' => $priceVarianceAccountData['category_id'] ?? null,
                            'sub_category_id' => $priceVarianceAccountData['sub_category_id'] ?? null,
                            'item_id' => $priceVarianceAccountData['item_id'] ?? null,
                            'book_id' => $priceVarianceAccountData['book_id'] ?? null,
                        ]);
                        $updateResults[] = $existingAccount;
                    } else {
                        DB::rollBack();
                        return response()->json([
                            'status' => false,
                            'message' => "Price Variance Account with ID {$priceVarianceAccountData['id']} not found.",
                        ], 404);
                    }
                } else {
                    $newPriceVarianceAccount = PriceVarianceAccount::create([
                        'group_id' => $groupId,
                        'company_id' => $priceVarianceAccountData['company_id'],
                        'organization_id' => $priceVarianceAccountData['organization_id'],
                        'ledger_group_id' => $priceVarianceAccountData['ledger_group_id'] ?? null,
                        'ledger_id' => $priceVarianceAccountData['ledger_id'] ?? null,
                        'category_id' => $priceVarianceAccountData['category_id'] ?? null,
                        'sub_category_id' => $priceVarianceAccountData['sub_category_id'] ?? null,
                        'item_id' => $priceVarianceAccountData['item_id'] ?? null,
                        'book_id' => $priceVarianceAccountData['book_id'] ?? null,
                    ]);
                    $insertData[] = $newPriceVarianceAccount;
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
        $itemId = $request->query('item_id',21);
        $bookId = $request->query('book_id',1);  
        if ($itemId && is_string($itemId)) {
            $itemId = explode(',', $itemId);
        }
        $ledgerData = AccountHelper::getPriceVarianceLedgerGroupAndLedgerId($organizationId, $itemId, $bookId);
        if ($ledgerData) {
            return response()->json($ledgerData);
        }
        return response()->json(['message' => 'No data found for the given parameters'], 404);
    }

    public function destroy($id)
    {
        try {
            $priceVarianceAccount = PriceVarianceAccount::findOrFail($id); 
            $result = $priceVarianceAccount->deleteWithReferences();  
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
                'message' => 'An error occurred while deleting the Price Variance Account: ' . $e->getMessage(),
            ], 500);
        }
    }
}
