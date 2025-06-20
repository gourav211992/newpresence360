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
        $status = ConstantHelper::STATUS;
        return view('procurement.bank.create',[
            'status' => $status,
        ]);
    }

    public function store(BankRequest $request)
    {
        $validatedData = $request->validated();
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $parentUrl = ConstantHelper::BANK_SERVICE_ALIAS;
        $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if ($services && $services['services'] && $services['services']->isNotEmpty()) {
            $firstService = $services['services']->first();
            $serviceId = $firstService->service_id;
            $policyData = Helper::getPolicyByServiceId($serviceId);
            if ($policyData && isset($policyData['policyLevelData'])) {
                $policyLevelData = $policyData['policyLevelData'];
                $validatedData['group_id'] = $policyLevelData['group_id'];
                $validatedData['company_id'] = $policyLevelData['company_id'];
                $validatedData['organization_id'] = $policyLevelData['organization_id'];
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = $organization->company_id;
                $validatedData['organization_id'] = $organization->id;
            }
        } else {
            $validatedData['group_id'] = $organization->group_id;
            $validatedData['company_id'] =$organization->company_id;
            $validatedData['organization_id'] = $organization->id;
        }
        try {
            $bank = Bank::create($validatedData);
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
            return response()->json([
                'status' => true,
                'message' => 'Record created successfully.',
                'data' => $bank,
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
        $status = ConstantHelper::STATUS;
        $bank = Bank::with('bankDetails')->findOrFail($id); 
        $ledgerId = $bank->ledger_id;
        $ledger = Ledger::find($ledgerId);
        $ledgerGroups = $ledger ? $ledger->groups() : collect(); 
        return view('procurement.bank.edit', compact('bank','status','ledgerGroups'));
    }


    public function update(BankRequest $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = $request->validated();
    
        try {
            $bank = Bank::findOrFail($id);
            $parentUrl = ConstantHelper::BANK_SERVICE_ALIAS;
            $services= Helper::getAccessibleServicesFromMenuAlias($parentUrl);
            if ($services && $services['services'] && $services['services']->isNotEmpty()) {
                $firstService = $services['services']->first();
                $serviceId = $firstService->service_id;
                $policyData = Helper::getPolicyByServiceId($serviceId);
                if ($policyData && isset($policyData['policyLevelData'])) {
                    $policyLevelData = $policyData['policyLevelData'];
                    $validatedData['group_id'] = $policyLevelData['group_id'];
                    $validatedData['company_id'] = $policyLevelData['company_id'];
                    $validatedData['organization_id'] = $policyLevelData['organization_id'];
                } else {
                    $validatedData['group_id'] = $organization->group_id;
                    $validatedData['company_id'] = $organization->company_id;
                    $validatedData['organization_id'] = $organization->id;
                }
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = $organization->company_id;
                $validatedData['organization_id'] = $organization->id;
            }
            $bank->update($validatedData);
            if ($request->has('bank_details') && is_array($validatedData['bank_details'])) {
                $newDetailIds = []; 
                foreach ($validatedData['bank_details'] as $detail) {
                    $detailId = $detail['id'] ?? null; 
    
                    $bankDetailData = [
                        'account_number' => $detail['account_number'] ?? null,
                        'branch_name' => $detail['branch_name'] ?? null,
                        'branch_address' => $detail['branch_address'] ?? null,
                        'ifsc_code' => $detail['ifsc_code'] ?? null,
                        'ledger_id' => $detail['ledger_id'] ?? null,
                        'ledger_group_id' => $detail['ledger_group_id'] ?? null,
                        'bank_id' => $bank->id,
                    ];
    
                    if ($detailId) {
                        $existingDetail = $bank->bankDetails()->find($detailId);
                        if ($existingDetail) {
                            $existingDetail->update($bankDetailData);
                        }
                        $newDetailIds[] = $detailId; 
                    } else {
                        $newDetail = $bank->bankDetails()->create($bankDetailData);
                        $newDetailIds[] = $newDetail->id;
                    }
                }
    
                $bank->bankDetails()->whereNotIn('id', $newDetailIds)->delete();
            } else {
                $bank->bankDetails()->delete();
            }
    
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully.',
                'data' => $bank,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the bank.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function deleteBankDetail($id)
    {
        try {
            $bankDetail = BankDetail::findOrFail($id);
            $result = $bankDetail->deleteWithReferences();
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }
            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully.',
            ], 200);
        
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
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
