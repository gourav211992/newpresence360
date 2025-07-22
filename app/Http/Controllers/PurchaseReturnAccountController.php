<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Facades\DataTables;
use App\Models\PurchaseReturnAccount;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use App\Models\Category;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\Item;
use App\Models\Book;
use App\Http\Requests\PurchaseReturnAccountRequest; 
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\AccountHelper;
use Illuminate\Support\Facades\DB;

class PurchaseReturnAccountController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser ();
        $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
        array_push($orgIds, $user?->organization_id);
        $companyIds = Organization::whereIn('id', $orgIds)
            ->pluck('company_id')
            ->toArray();
        $companies = OrganizationCompany::whereIn('id', $companyIds)->get();
        $ledgerGroups = Group::all();
        $ledgers = Ledger::where('status', '1') ->get();  
        $items = Item::where('status', 'active') ->get();
        $purchaseReturnAccounts = PurchaseReturnAccount::query()->get();
        $erpBooks = Book::where('status', 'active') ->get(); 

        return view('procurement.purchase-return-account.index', compact(
            'companies', 'ledgerGroups', 'ledgers', 'items', 'purchaseReturnAccounts', 'erpBooks','orgIds'
        ));
    }

    public function store(PurchaseReturnAccountRequest $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $user = Helper::getAuthenticatedUser ();
            $organization = $user->organization;
            $groupId = $organization->group_id;
            $insertData = [];
            $updateResults = [];

            foreach ($validated['purchase_return_accounts'] as $purchaseReturnAccountData) {
                if (isset($purchaseReturnAccountData['id']) && $purchaseReturnAccountData['id']) {
                    $existingAccount = PurchaseReturnAccount::find($purchaseReturnAccountData['id']);
                    if ($existingAccount) {
                        $existingAccount->update([
                            'group_id' => $groupId,
                            'company_id' => $purchaseReturnAccountData['company_id'],
                            'organization_id' => $purchaseReturnAccountData['organization_id'],
                            'ledger_group_id' => $purchaseReturnAccountData['ledger_group_id'] ?? null,
                            'ledger_id' => $purchaseReturnAccountData['ledger_id'] ?? null,
                            'category_id' => $purchaseReturnAccountData['category_id'] ?? null,
                            'sub_category_id' => $purchaseReturnAccountData['sub_category_id'] ?? null,
                            'item_id' => $purchaseReturnAccountData['item_id'] ?? null,
                            'book_id' => $purchaseReturnAccountData['book_id'] ?? null,
                        ]);
                        $updateResults[] = $existingAccount;
                    } else {
                        DB::rollBack();
                        return response()->json([
                            'status' => false,
                            'message' => "Purchase Return Account with ID {$purchaseReturnAccountData['id']} not found.",
                        ], 404);
                    }
                } else {
                    $newPurchaseReturnAccount = PurchaseReturnAccount::create([
                        'group_id' => $groupId,
                        'company_id' => $purchaseReturnAccountData['company_id'],
                        'organization_id' => $purchaseReturnAccountData['organization_id'],
                        'ledger_group_id' => $purchaseReturnAccountData['ledger_group_id'] ?? null,
                        'ledger_id' => $purchaseReturnAccountData['ledger_id'] ?? null,
                        'category_id' => $purchaseReturnAccountData['category_id'] ?? null,
                        'sub_category_id' => $purchaseReturnAccountData['sub_category_id'] ?? null,
                        'item_id' => $purchaseReturnAccountData['item_id'] ?? null,
                        'book_id' => $purchaseReturnAccountData['book_id'] ?? null,
                    ]);
                    $insertData[] = $newPurchaseReturnAccount;
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
        $itemId = $request->query('item_id');
        $bookId = $request->query('book_id','2');  
        if ($itemId && is_string($itemId)) {
            $itemId = explode(',', $itemId);
        }
        $ledgerData = AccountHelper::getPurchaseReturnLedgerGroupAndLedgerId($organizationId, $itemId, $bookId);
        if ($ledgerData) {
            return response()->json($ledgerData);
        }
        return response()->json(['message' => 'No data found for the given parameters'], 404);
    }

    public function destroy($id)
    {
        try {
            $purchaseReturnAccount = PurchaseReturnAccount::findOrFail($id); 
            $result = $purchaseReturnAccount->deleteWithReferences();  
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
                'message' => 'An error occurred while deleting the Purchase Return Account: ' . $e->getMessage(),
            ], 500);
        }
    }
}