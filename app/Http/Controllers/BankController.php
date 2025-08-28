<?php

namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Bank;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\BankDetail;
use App\Models\ErpAddress;
use Illuminate\Http\Request;
use App\Http\Requests\BankRequest;
use App\Helpers\ConstantHelper;
use App\Models\Organization;
use App\Helpers\Helper;
use App\Models\Scopes\DefaultGroupCompanyOrgScope;
use Auth;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first(); 
        $organizationId = $organization?->id ?? null;
        $companyId = $organization?->company_id ?? null;
    
        if ($request->ajax()) {
            $banks = Bank::withDefaultGroupCompanyOrg()
            ->orderBy('id', 'desc');
    
            return DataTables::of($banks)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return '<span class="badge rounded-pill ' . ($row->status == 'active' ? 'badge-light-success' : 'badge-light-danger') . '">
                                ' . ucfirst($row->status) . '
                            </span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('bank.edit', $row->id);
                    return '<div class="dropdown">
                                <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                    <i data-feather="more-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="' . $editUrl . '">
                                        <i data-feather="edit-3" class="me-50"></i>
                                        <span>Edit</span>
                                    </a>
                                </div>
                            </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
    
        return view('procurement.bank.index');
    }
    

    public function create()
    {
        $user = Helper::getAuthenticatedUser();
        $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
        if ($user->organization_id) {
            $orgIds[] = $user->organization_id;
        }
       $allOrganizations = Organization::whereIn('id', $orgIds)
        ->where('status', 'active')
        ->get();
     
        $status = ConstantHelper::STATUS;
        return view('procurement.bank.create',[
            'status' => $status,
            'allOrganizations'=>$allOrganizations,
        ]);
    }

   public function store(BankRequest $request)
    {
        $validatedData = $request->validated();
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $parentUrl = ConstantHelper::BANK_SERVICE_ALIAS;
        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);

        try {
            $createdBanks = [];

            $organizationIds = $validatedData['organization_id'] ?? [$organization->id];

            foreach ($organizationIds as $orgId) {
                $bankData = $validatedData;
                
                if ($services && $services['services'] && $services['services']->isNotEmpty()) {
                    $firstService = $services['services']->first();
                    $serviceId = $firstService->service_id;
                    $policyData = Helper::getPolicyByServiceId($serviceId);

                    if ($policyData && isset($policyData['policyLevelData'])) {
                        $policyLevelData = $policyData['policyLevelData'];
                        $bankData['group_id'] = $policyLevelData['group_id'];
                        $bankData['company_id'] = $policyLevelData['company_id'];
                        $bankData['organization_id'] = $orgId;
                    } else {
                        $bankData['group_id'] = $organization->group_id;
                        $bankData['company_id'] = $organization->company_id;
                        $bankData['organization_id'] = $orgId;
                    }
                } else {
                    $bankData['group_id'] = $organization->group_id;
                    $bankData['company_id'] = $organization->company_id;
                    $bankData['organization_id'] = $orgId;
                }

                $bank = Bank::create($bankData);

                if (isset($validatedData['bank_details']) && is_array($validatedData['bank_details'])) {
                    foreach ($validatedData['bank_details'] as $detail) {
                        $bankDetailData = [
                            'account_number' => $detail['account_number'] ?? null,
                            'branch_name' => $detail['branch_name'] ?? null,
                            'branch_address' => $detail['branch_address'] ?? null,
                            'ifsc_code' => $detail['ifsc_code'] ?? null,
                            'ledger_id' => $detail['ledger_id'] ?? null,
                            'ledger_group_id' => $detail['ledger_group_id'] ?? null,
                            'bank_id' => $bank->id,
                        ];
                        BankDetail::create($bankDetailData);
                    }
                }

                $createdBanks[] = $bank;
            }

            return response()->json([
                'status' => true,
                'message' => 'Record(s) created successfully.',
                'data' => $createdBanks,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the bank.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function getIfscDetails($ifsc)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://ifsc.razorpay.com/' . $ifsc);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new \Exception(curl_error($ch));
            }
            curl_close($ch);
    
            if ($response === false) {
                return response()->json(['status' => false, 'message' => 'Invalid IFSC code.'], 400);
            }
            $data = json_decode($response, true);
            return response()->json(['status' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred.', 'error' => $e->getMessage()], 500);
        }
    }
    

    public function show(Bank $bank)
    {
        $bank->load('bankDetails'); 
        return view('procurement.bank.show', compact('bank'));
    }

    public function edit($id)
    {
        $user = Helper::getAuthenticatedUser();
        $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
        if ($user->organization_id) {
            $orgIds[] = $user->organization_id;
        }

        $allOrganizations = Organization::whereIn('id', $orgIds)
            ->where('status', 'active')
            ->get();

        $status = ConstantHelper::STATUS;
        $bank = Bank::with('bankDetails')->findOrFail($id);
        $ledgerId = $bank->ledger_id;
        $ledger = Ledger::find($ledgerId);
        $ledgerGroups = $ledger ? $ledger->groups() : collect();
        $relatedBanks = Bank::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
        ->where(function($q) use ($bank) {
            $q->where('bank_name', $bank->bank_name)
            ->orWhere('bank_code', $bank->bank_code)
            ->orWhere('id', $bank->id); 
        })
        ->get();

       $selectedOrgIds = $relatedBanks->pluck('organization_id')->toArray();
        return view('procurement.bank.edit', [
                'bank'              => $bank,              
                'relatedBanks'      => $relatedBanks,     
                'status'            => $status,
                'ledgerGroups'      => $ledgerGroups,
                'allOrganizations'  => $allOrganizations,
                'selectedOrgIds'    => $selectedOrgIds, 
            ]);
    }

  public function update(BankRequest $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = $request->validated();

        try {
            $currentBank = Bank::findOrFail($id);
            $parentUrl = ConstantHelper::BANK_SERVICE_ALIAS;
            $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);

            if ($services && $services['services'] && $services['services']->isNotEmpty()) {
                $firstService = $services['services']->first();
                $serviceId = $firstService->service_id;
                $policyData = Helper::getPolicyByServiceId($serviceId);

                $validatedData['group_id']   = $policyData['group_id'] ?? $organization->group_id;
                $validatedData['company_id'] = $policyData['company_id'] ?? $organization->company_id;
            } else {
                $validatedData['group_id']   = $organization->group_id;
                $validatedData['company_id'] = $organization->company_id;
            }

            $updatedBanks = [];

            $organizationIds = $validatedData['organization_id'] ?? [$organization->id];

            Bank::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                ->where('bank_name', $currentBank->bank_name)
                ->whereNotIn('organization_id', $organizationIds)
                ->each(function ($bankToDelete) {
                    $bankToDelete->bankDetails()->delete();
                    $bankToDelete->delete();
                });
           
            foreach ($organizationIds as $orgId) {
                $bank = Bank::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                    ->where('bank_name', $currentBank->bank_name)
                    ->where('organization_id', $orgId)
                    ->first();

                if ($bank) {
                    $bank->update(array_merge($validatedData, ['organization_id' => $orgId]));
                } else {
                    $bank = Bank::create(array_merge($validatedData, ['organization_id' => $orgId]));
                }

                if (isset($validatedData['bank_details']) && is_array($validatedData['bank_details'])) {
                    foreach ($validatedData['bank_details'] as $detail) {
                        $accountNumber = $detail['account_number'] ?? null;
                        $ifscCode      = $detail['ifsc_code'] ?? null;

                        $existingDetail = $bank->bankDetails()
                            ->where('account_number', $accountNumber)
                            ->where('ifsc_code', $ifscCode)
                            ->first();

                        if ($existingDetail) {
                            $existingDetail->update([
                                'bank_id'          => $bank->id,
                                'branch_name'      => $detail['branch_name'] ?? $existingDetail->branch_name,
                                'branch_address'   => $detail['branch_address'] ?? $existingDetail->branch_address,
                                'ledger_id'        => $detail['ledger_id'] ?? $existingDetail->ledger_id,
                                'ledger_group_id'  => $detail['ledger_group_id'] ?? $existingDetail->ledger_group_id,
                            ]);
                        } else {
                            $bankDetailData = [
                                'bank_id'          => $bank->id,
                                'account_number'   => $accountNumber,
                                'branch_name'      => $detail['branch_name'] ?? null,
                                'branch_address'   => $detail['branch_address'] ?? null,
                                'ifsc_code'        => $ifscCode,
                                'ledger_id'        => $detail['ledger_id'] ?? null,
                                'ledger_group_id'  => $detail['ledger_group_id'] ?? null,
                            ];
                            $bank->bankDetails()->create($bankDetailData);
                        }
                    }
                }

                $updatedBanks[] = $bank;
            }

            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully.',
                'data' => $updatedBanks,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error while updating bank.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getLedger()
    {
        $bankGroup = Group::where('name', 'Bank Accounts')->first();

        if (!$bankGroup) {
            return response()->json([]);
        }

        $childGroupIds = $bankGroup->getAllChildIds();
        $groupIds = array_merge([$bankGroup->id], $childGroupIds);
        $stringGroupIds = array_map('strval', $groupIds);

        $ledgers = Ledger::where(function($q) use ($stringGroupIds) {
            foreach ($stringGroupIds as $id) {
                $q->orWhereJsonContains('ledger_group_id', $id);
            }
        })->get(['id', 'code', 'name']);

        return response()->json($ledgers);
    }

   public function deleteBankDetail($id)
    {
        try {
            $bankDetail = BankDetail::findOrFail($id);
            $bank       = $bankDetail->bank;

            if (!$bank) {
                return response()->json([
                    'status' => false,
                    'message' => 'Parent bank not found for this detail.',
                ], 404);
            }
           $relatedBanks = Bank::withoutGlobalScopes()
            ->where('bank_name', $bank->bank_name)
            ->where('bank_code', $bank->bank_code)
            ->pluck('id')
            ->toArray();

            $relatedDetails = BankDetail::whereIn('bank_id', $relatedBanks)
                ->where('account_number', $bankDetail->account_number)
                ->where('ifsc_code', $bankDetail->ifsc_code)
                ->get();
        

            $deletedCount = 0;

            foreach ($relatedDetails as $detail) {
                $result = $detail->deleteWithReferences();

                if (!$result['status']) {
                    return response()->json([
                        'status' => false,
                        'message' => $result['message'],
                        'referenced_tables' => $result['referenced_tables'] ?? [],
                    ], 400);
                }

                $deletedCount++;
            }

            return response()->json([
                'status'  => true,
                'message' => "Record Deleted successfully.",
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while deleting the record: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $bank = Bank::findOrFail($id);
            $referenceTables = [
                'erp_bank_details' => ['bank_id'],
            ];
            $result = $bank->deleteWithReferences($referenceTables);
            
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }

            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully.'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the bank: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function search(Request $request)
    {
       
        $term = $request->input('term', ''); 
        $results = collect(); 
            if (!empty($term)) {
                $results = ErpAddress::whereHas('erpAddressable', function($query) use ($term) {
                    $query->where('address', 'LIKE', "%$term%");
                })
                ->get(['id', 'address']);
            }

            if (empty($term) || $results->isEmpty()) {
                $results = ErpAddress::limit(10)
                    ->get(['id', 'address']);
            }
        return response()->json($results);
    }

}
