<?php

namespace App\Http\Controllers;
use App\Models\CogsAccount;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\Item;
use App\Models\Book;
use App\Http\Requests\CogsAccountRequest; 
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\AccountHelper;
use Illuminate\Support\Facades\DB;
use Auth;

class CogsAccountController extends Controller
{
    public function index(Request $request, $id = null)
    {
        $user = Helper::getAuthenticatedUser();
        $userType = Helper::userCheck()['type'];
        $orgIds = $user -> organizations() -> pluck('organizations.id') -> toArray();
        array_push($orgIds, $user?->organization_id);
        $companyIds = Organization::whereIn('id', $orgIds)
            ->pluck('company_id')
            ->toArray();
        $companies = OrganizationCompany::whereIn('id', $companyIds)->get();
        $ledgerGroups = Group::all();
        $ledgers = Ledger::where('status', '1') ->get();  
        $items = Item::where('status', 'active') ->get();
        $cogsAccounts = CogsAccount::query()->get();
        $erpBooks = Book::where('status', 'active') ->get(); 

        return view('procurement.cogs-account.index', compact(
            'companies', 'ledgerGroups', 'ledgers', 'items', 'cogsAccounts', 'erpBooks','orgIds'
        ));
    }

    public function store(CogsAccountRequest $request)
    {
        DB::beginTransaction();

        try {
        $validated = $request->validated();
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization->group_id;
        $insertData = [];
        $updateResults = [];

        foreach ($validated['cogs_accounts'] as $cogsAccountData) {
            if (isset($cogsAccountData['id']) && $cogsAccountData['id']) {
                $existingAccount = CogsAccount::find($cogsAccountData['id']);
                if ($existingAccount) {
                    $existingAccount->update([
                        'group_id' => $groupId,
                        'company_id' => $cogsAccountData['company_id'],
                        'organization_id' => $cogsAccountData['organization_id'],
                        'ledger_group_id' => $cogsAccountData['ledger_group_id'] ?? null,
                        'ledger_id' => $cogsAccountData['ledger_id'] ?? null,
                        'category_id' => $cogsAccountData['category_id'] ?? null,
                        'sub_category_id' => $cogsAccountData['sub_category_id'] ?? null,
                        'item_id' => $cogsAccountData['item_id'] ?? null,
                        'book_id' => $cogsAccountData['book_id'] ?? null,
                    ]);
                    $updateResults[] = $existingAccount;
                } else {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => "COGS account with ID {$cogsAccountData['id']} not found.",
                    ], 404);
                }
            } else {
                $newCogsAccount = CogsAccount::create([
                    'group_id' => $groupId,
                    'company_id' => $cogsAccountData['company_id'],
                    'organization_id' => $cogsAccountData['organization_id'],
                    'ledger_group_id' => $cogsAccountData['ledger_group_id'] ?? null,
                    'ledger_id' => $cogsAccountData['ledger_id'] ?? null,
                    'category_id' => $cogsAccountData['category_id'] ?? null,
                    'sub_category_id' => $cogsAccountData['sub_category_id'] ?? null,
                    'item_id' => $cogsAccountData['item_id'] ?? null,
                    'book_id' => $cogsAccountData['book_id'] ?? null,
                ]);
                $insertData[] = $newCogsAccount;
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
        $itemId = $request->query('item_id');
        $bookId = $request->query('book_id','1');  
        if ($itemId && is_string($itemId)) {
            $itemId = explode(',', $itemId);
        }
        $ledgerData = AccountHelper::getCogsLedgerGroupAndLedgerId( $organizationId, $itemId, $bookId);
        if ($ledgerData) {
            return response()->json($ledgerData);
        }
        return response()->json(['message' => 'No data found for the given parameters'], 404);
    }

    public function destroy($id)
    {
        try {
            $cogsAccount = CogsAccount::findOrFail($id); 
            $result = $cogsAccount->deleteWithReferences();  
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
                'message' => 'An error occurred while deleting the COGS account: ' . $e->getMessage(),
            ], 500);
        }
    }
}
