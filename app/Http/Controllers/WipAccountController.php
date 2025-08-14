<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Facades\DataTables;
use App\Models\WipAccount;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use App\Models\Category;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\Item;
use App\Models\Book;
use App\Models\UserOrganizationMapping;
use App\Models\EmployeeOrganizationEmployee;
use App\Http\Requests\WipAccountRequest;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\AccountHelper;
use App\Models\Trait\DefaultGroupCompanyOrg;
use Illuminate\Support\Facades\DB;
use Auth;

class WipAccountController extends Controller
{
    public function index(Request $request, $id = null)
    {
        $user = Helper::getAuthenticatedUser();
        $userType = Helper::userCheck()['type'];
        $orgIds = $user -> organizations() -> pluck('organizations.id') -> toArray();
        array_push($orgIds, $user?->organization_id);
        $orgData = Organization::whereIn('id', $orgIds);
        $companyIds = $orgData
            ->pluck('company_id')
            ->toArray();
        $companies = OrganizationCompany::whereIn('id', $companyIds)->get();
        $categories = Category::where('status', 'active')->get();  
        $subCategories = Category::where('status', 'active')
         ->whereNotNull('parent_id') 
         ->get();
        $ledgerGroups = Group::all();
        $ledgers = Ledger::where('status', '1') ->get();  
        $items = Item::where('status', 'active') ->get();
        $wipAccount = WipAccount::query()->get();
        $erpBooks = Book::where('status', 'active') ->get(); 
        
        if ($request->ajax()) {
            $wipAccounts = WipAccount::with([
                'organization', 'group', 'company', 'ledgerGroup',
                'ledger', 'category', 'subCategory', 'item','book'
            ])
            ->orderBy('group_id')
            ->orderBy('company_id') 
            ->orderBy('organization_id')
            ->orderBy('id', 'desc');

            return DataTables::of($wipAccounts)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return '<span class="badge rounded-pill ' . 
                        ($row->status == 'active' ? 'badge-light-success' : 'badge-light-danger') . 
                        ' badgeborder-radius">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('wip-accounts.edit', $row->id);
                    $deleteUrl = route('wip-accounts.destroy', $row->id);
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

        return view('procurement.wip-account.index', compact(
            'companies', 'categories', 'subCategories', 'ledgerGroups', 'ledgers', 'items', 'wipAccount','erpBooks','orgIds'
        ));
    }

    public function store(WipAccountRequest $request)
    {
        DB::beginTransaction();
       try {
        $validated = $request->validated();
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $groupId = $organization->group_id;
        $insertData = [];
        $updateResults = [];
        foreach ($validated['wip_accounts'] as $wipAccountData) {
            if (isset($wipAccountData['id']) && $wipAccountData['id']) {
                $existingAccount = WipAccount::find($wipAccountData['id']);
                if ($existingAccount) {
                    $existingAccount->update([
                        'group_id' => $groupId,
                        'company_id' => $wipAccountData['company_id'],
                        'organization_id' => $wipAccountData['organization_id'],
                        'ledger_group_id' => $wipAccountData['ledger_group_id'] ?? null,
                        'ledger_id' => $wipAccountData['ledger_id'] ?? null,
                        'category_id' => $wipAccountData['category_id'] ?? null,
                        'sub_category_id' => $wipAccountData['sub_category_id'] ?? null,
                        'item_id' => $wipAccountData['item_id'] ?? null,
                        'book_id' => $wipAccountData['book_id'] ?? null,
                    ]);
                    $updateResults[] = $existingAccount;
                } else {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => "wip account with ID {$wipAccountData['id']} not found.",
                    ], 404);
                }
            } else {
                $newwipAccount = WipAccount::create([
                    'group_id' =>$groupId,
                    'company_id' => $wipAccountData['company_id'],
                    'organization_id' => $wipAccountData['organization_id'],
                    'ledger_group_id' => $wipAccountData['ledger_group_id'] ?? null,
                    'ledger_id' => $wipAccountData['ledger_id'] ?? null,
                    'book_id' => $wipAccountData['book_id'] ?? null,
                ]);
                $insertData[] = $newwipAccount;
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
        $bookId = $request->query('book_id',1);  
        $ledgerData = AccountHelper::getwipLedgerGroupAndLedgerId( $organizationId, $bookId);
        if ($ledgerData) {
            return response()->json($ledgerData);
        }
        return response()->json(['message' => 'No data found for the given parameters'], 404);
    }

    public function destroy($id)
    {
        try {
            $wipAccount = wipAccount::findOrFail($id); 
            $result = $wipAccount->deleteWithReferences();
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
                'message' => 'An error occurred while deleting the wip account: ' . $e->getMessage(),
            ], 500);
        }
    }
}
