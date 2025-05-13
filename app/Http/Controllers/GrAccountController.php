<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Facades\DataTables;
use App\Models\GrAccount;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use App\Models\Category;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\Item;
use App\Models\Book;
use App\Http\Requests\GrAccountRequest; 
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\AccountHelper;
use Illuminate\Support\Facades\DB;
use Auth;

class GrAccountController extends Controller
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
        $categories = Category::withDefaultGroupCompanyOrg()
        ->where('status', 'active')
        ->get();  
    
        $subCategories = Category::withDefaultGroupCompanyOrg()
            ->where('status', 'active') 
            ->whereNotNull('parent_id') 
            ->get();
    
        $ledgerGroups = Group::all();
        $ledgers = Ledger::withDefaultGroupCompanyOrg()
        ->where('status', '1') 
        ->get();  
        $items = Item::withDefaultGroupCompanyOrg()
        ->where('status', 'active') 
        ->get();
        $grAccounts = GrAccount::withDefaultGroupCompanyOrg()->get();
        $erpBooks = Book::withDefaultGroupCompanyOrg()
        ->where('status', 'active') 
        ->get(); 
        
        if ($request->ajax()) {
            $grAccounts = GrAccount::with([
                'organization', 'group', 'company', 'ledgerGroup',
                'ledger', 'category', 'subCategory', 'item'
            ])
            ->orderBy('group_id')
            ->orderBy('company_id') 
            ->orderBy('organization_id')
            ->orderBy('id', 'desc');

            return DataTables::of($grAccounts)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return '<span class="badge rounded-pill ' . 
                        ($row->status == 'active' ? 'badge-light-success' : 'badge-light-danger') . 
                        ' badgeborder-radius">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('gr-accounts.edit', $row->id);
                    $deleteUrl = route('gr-accounts.destroy', $row->id);
                    return '<div class="dropdown">
                                <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                    <i data-feather="more-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="' . $editUrl . '">
                                       <i data-feather="edit-3" class="me-50"></i>
                                        <span>Edit</span>
                                    </a>
                                    <form action="' . $deleteUrl . '" method="POST" class="dropdown-item">
                                        ' . csrf_field() . method_field('DELETE') . '
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i data-feather="trash" class="me-50"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('procurement.gr-account.index', compact(
            'companies', 'categories', 'subCategories', 'ledgerGroups', 'ledgers', 'items', 'grAccounts','erpBooks','orgIds'
        ));
    }
    public function store(GrAccountRequest $request)
    {
        DB::beginTransaction();
        try {
        $validated = $request->validated();
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization->group_id;
        $insertData = [];
        $updateResults = [];

        foreach ($validated['gr_accounts'] as $grAccountData) {
            if (isset($grAccountData['id']) && $grAccountData['id']) {
                $existingAccount = GrAccount::find($grAccountData['id']);
                if ($existingAccount) {
                    $existingAccount->update([
                        'group_id' => $groupId,
                        'company_id' => $grAccountData['company_id'],
                        'organization_id' => $grAccountData['organization_id'],
                        'ledger_group_id' => $grAccountData['ledger_group_id'] ?? null,
                        'ledger_id' => $grAccountData['ledger_id'] ?? null,
                        'category_id' => $grAccountData['category_id'] ?? null,
                        'sub_category_id' => $grAccountData['sub_category_id'] ?? null,
                        'item_id' => $grAccountData['item_id'] ?? null,
                        'book_id' => $grAccountData['book_id'] ?? null,
                    ]);
                    $updateResults[] = $existingAccount;
                } else {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => "GR account with ID {$grAccountData['id']} not found.",
                    ], 404);
                }
            } else {
                $newGrAccount = GrAccount::create([
                    'group_id' => $groupId,
                    'company_id' => $grAccountData['company_id'],
                    'organization_id' => $grAccountData['organization_id'],
                    'ledger_group_id' => $grAccountData['ledger_group_id'] ?? null,
                    'ledger_id' => $grAccountData['ledger_id'] ?? null,
                    'category_id' => $grAccountData['category_id'] ?? null,
                    'sub_category_id' => $grAccountData['sub_category_id'] ?? null,
                    'item_id' => $grAccountData['item_id'] ?? null,
                    'book_id' => $grAccountData['book_id'] ?? null,
                ]);
                $insertData[] = $newGrAccount;
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
        $bookId = $request->query('book_id',7);  

        if ($itemId && is_string($itemId)) {
            $itemId = explode(',', $itemId);
        }

        $ledgerData = AccountHelper::getGrLedgerGroupAndLedgerId($organizationId, $itemId, $bookId);
        
        if ($ledgerData) {
            return response()->json($ledgerData);
        }
        
        return response()->json(['message' => 'No data found for the given parameters'], 404);
    }
    public function destroy($id)
    {
        try {
            $grAccount = GrAccount::findOrFail($id); 
            $result = $grAccount->deleteWithReferences();  

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
                'message' => 'An error occurred while deleting the GR account: ' . $e->getMessage(),
            ], 500);
        }
    }
}
