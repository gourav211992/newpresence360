<?php

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;
use App\Models\Tax;
use App\Models\Item;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\Vendor;
use App\Helpers\Helper;
use App\Models\ErpStore;
use App\Models\TaxDetail;
use App\Helpers\TaxHelper;
use App\Models\Compliance;
use App\Models\ErpPqHeader;
use App\Models\ErpSaleOrder;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\ErpSaleReturn;
use App\Models\ErpSaleInvoice;
use App\Helpers\ConstantHelper;
use Yajra\DataTables\DataTables;
use App\Helpers\SaleModuleHelper;
use App\Http\Requests\TaxRequest;
use App\Models\ErpPqHeaderHistory;
use Illuminate\Support\Facades\DB;
use App\Models\ErpSaleOrderHistory;
use App\Models\ErpSaleReturnHistory;
use App\Models\ErpSaleInvoiceHistory;
use App\Helpers\ServiceParametersHelper;
use Illuminate\Support\Facades\Response;


class TaxController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationId = $organization?->id ?? null;
        $companyId = $organization?->company_id ?? null;

        if ($request->ajax()) {
            $taxes = Tax::orderBy('id', 'desc');
            return DataTables::of(source: $taxes)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return '<span class="badge rounded-pill badge-light-' . ($row->status === 'active' ? 'success' : 'danger') . ' badgeborder-radius">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('applicability_type', function ($row) {
                    $taxDetail = $row->taxDetails->first();
                    return $taxDetail ? $taxDetail->applicability_type : 'N/A';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="dropdown">
                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                            <i data-feather="more-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="' . route('tax.edit', $row->id) . '">
                                <i data-feather="edit-3" class="me-50"></i>
                                <span>Edit</span>
                            </a>
                        </div>
                    </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('procurement.tax.index');
    }


    public function create()
    {
        $applicationTypes = ConstantHelper::TAX_APPLICATION_TYPE;
        $supplyTypes = ConstantHelper::PLACE_OF_SUPPLY_TYPES;
        $statuses = ConstantHelper::STATUS;
        $taxCategories = ConstantHelper::TAX_CLASSIFICATIONS;
        $gstSections = ConstantHelper::GST_TYPES;
        $tdsSections = ConstantHelper::getTdsSections();
        $tcsSections = ConstantHelper::getTcsSections();
        return view('procurement.tax.create', [
            'applicationTypes' => $applicationTypes,
            'supplyTypes' => $supplyTypes,
            'statuses' => $statuses,
            'taxCategories' => $taxCategories,
            'gstSections' => $gstSections,
            'tdsSections' => $tdsSections,
            'tcsSections' => $tcsSections,
        ]);
    }


    public function store(TaxRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = $request->validated();
        $parentUrl = ConstantHelper::TAX_SERVICE_ALIAS;
        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
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
                $validatedData['organization_id'] = null;
            }
        } else {
            $validatedData['group_id'] = $organization->group_id;
            $validatedData['company_id'] = $organization->company_id;
            $validatedData['organization_id'] = null;
        }
        DB::beginTransaction();
        try {
            $tax = Tax::create([
                'tax_group' => $validatedData['tax_group'],
                'description' => $validatedData['description'],
                'tax_category' => $validatedData['tax_category'],
                'tax_type' => $validatedData['tax_type'],
                'status' => $validatedData['status'],
                'organization_id' => $validatedData['organization_id'] ?? null,
                'group_id' => $validatedData['group_id'] ?? null,
                'company_id' => $validatedData['company_id'] ?? null,
            ]);

            $taxDetails = $validatedData['tax_details'];
            $category = $validatedData['tax_category'] ?? null;
            if (in_array($category, ['TDS', 'TCS'])) {

                if (count($taxDetails) > 2) {
                    throw new \Exception('Only two rows for TDS/TCS are allowed: one for Sale and one for Purchase.');
                }

                $saleCount = 0;
                $purchaseCount = 0;
                $firstPercentage = null;
                $percentagesConsistent = true;

                foreach ($taxDetails as $detail) {
                    $currentPercentage = $detail['tax_percentage'] ?? null;

                    if ($currentPercentage) {
                        if ($firstPercentage === null) {
                            $firstPercentage = $currentPercentage;
                        } elseif ($currentPercentage !== $firstPercentage) {
                            $percentagesConsistent = false;
                        }
                    }
                    $isSale = isset($detail['is_sale']) && $detail['is_sale'] == '1';
                    $isPurchase = isset($detail['is_purchase']) && $detail['is_purchase'] == '1';
                    if ($isSale && $isPurchase) {
                        throw new \Exception('Both Sale and Purchase cannot be selected in the same row.');
                    }

                    if ($isSale) $saleCount++;
                    if ($isPurchase) $purchaseCount++;

                    if ($category === 'TDS' && ($detail['applicability_type'] ?? '') !== 'deduction') {
                        throw new \Exception('For TDS, only Deduction is allowed.');
                    }

                    if ($category === 'TCS' && ($detail['applicability_type'] ?? '') !== 'collection') {
                        throw new \Exception('For TCS, only Collection is allowed.');
                    }
                }

                if ($saleCount > 1 || $purchaseCount > 1) {
                    throw new \Exception('Only one Sale and one Purchase row are allowed for TDS/TCS.');
                }
                if (!$percentagesConsistent) {
                    throw new \Exception('All TDS/TCS rows must have the same tax percentage value.');
                }
            }

            foreach ($validatedData['tax_details'] as $detail) {
                TaxDetail::create([
                    'tax_id' => $tax->id,
                    'ledger_id' => isset($detail['ledger_id']) ? $detail['ledger_id'] : null,
                    'ledger_group_id' => isset($detail['ledger_group_id']) ? $detail['ledger_group_id'] : null,
                    'tax_type' => $detail['tax_type'],
                    'tax_percentage' => $detail['tax_percentage'],
                    'place_of_supply' => $detail['place_of_supply'],
                    'applicability_type' => $detail['applicability_type'],
                    'is_purchase' => isset($detail['is_purchase']) && $detail['is_purchase'] == '1',
                    'is_sale' => isset($detail['is_sale']) && $detail['is_sale'] == '1',
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record created successfully',
                'data' => $tax,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        //
    }


    public function edit(string $id)
    {
        $tax = Tax::findOrFail($id);
        $applicationTypes = ConstantHelper::TAX_APPLICATION_TYPE;
        $supplyTypes = ConstantHelper::PLACE_OF_SUPPLY_TYPES;
        $statuses = ConstantHelper::STATUS;
        $taxCategories = ConstantHelper::TAX_CLASSIFICATIONS;
        $gstSections = ConstantHelper::GST_TYPES;
        $tdsSections = ConstantHelper::getTdsSections();
        $tcsSections = ConstantHelper::getTcsSections();
        $ledgerId = $tax->ledger_id;
        $ledger = Ledger::find($ledgerId);
        $ledgerGroups = $ledger ? $ledger->groups() : collect();
        $matchedSection = [];
        if ($tax->tax_category == 'TDS') {
            foreach ($tdsSections as $key => $value) {
                if ($key == $tax->tax_type) {
                    $matchedSection[$key] = $value;

                    break;
                }
            }
        } elseif ($tax->tax_category == 'TCS') {
            foreach ($tcsSections as $key => $value) {
                if ($key == $tax->tax_type) {
                    $matchedSection[$key] = $value;
                    break;
                }
            }
        }
        return view('procurement.tax.edit', [
            'tax' => $tax,
            'applicationTypes' => $applicationTypes,
            'supplyTypes' => $supplyTypes,
            'statuses' => $statuses,
            'taxCategories' => $taxCategories,
            'gstSections' => $gstSections,
            'tdsSections' => $tdsSections,
            'tcsSections' => $tcsSections,
            'ledgerGroups' => $ledgerGroups,
            'matchedSection' => $matchedSection,
        ]);
    }

    public function update(TaxRequest $request, string $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = $request->validated();
        $parentUrl = ConstantHelper::TAX_SERVICE_ALIAS;
        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
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
                $validatedData['organization_id'] = null;
            }
        } else {
            $validatedData['group_id'] = $organization->group_id;
            $validatedData['company_id'] = $organization->company_id;
            $validatedData['organization_id'] = null;
        }

        DB::beginTransaction();
        try {
            $tax = Tax::findOrFail($id);
            $tax->update([
                'tax_group' => $validatedData['tax_group'],
                'description' => $validatedData['description'],
                'tax_category' => $validatedData['tax_category'],
                'tax_type' => $validatedData['tax_type'],
                'status' => $validatedData['status'],
                'organization_id' => $validatedData['organization_id'] ?? null,
                'group_id' => $validatedData['group_id'] ?? null,
                'company_id' => $validatedData['company_id'] ?? null,
            ]);
            if ($request->has('tax_details')) {
                $newTaxDetailIds = [];
                foreach ($validatedData['tax_details'] as $detailData) {
                    $category = $validatedData['tax_category'] ?? null;
                    if (in_array($category, ['TDS', 'TCS'])) {
                        if (count($validatedData['tax_details']) > 2) {
                            throw new \Exception('Only two rows for TDS/TCS are allowed: one for Sale and one for Purchase.');
                        }

                        $saleCount = 0;
                        $purchaseCount = 0;
                        $firstPercentage = null;
                        $percentagesConsistent = true;

                        foreach ($validatedData['tax_details'] as $detail) {
                            $currentPercentage = $detail['tax_percentage'] ?? null;

                            if ($currentPercentage) {
                                if ($firstPercentage === null) {
                                    $firstPercentage = $currentPercentage;
                                } elseif ($currentPercentage !== $firstPercentage) {
                                    $percentagesConsistent = false;
                                }
                            }
                            $isSale = isset($detail['is_sale']) && $detail['is_sale'] == '1';
                            $isPurchase = isset($detail['is_purchase']) && $detail['is_purchase'] == '1';

                            if ($isSale && $isPurchase) {
                                throw new \Exception('Both Sale and Purchase cannot be selected in the same row.');
                            }

                            if ($isSale) $saleCount++;
                            if ($isPurchase) $purchaseCount++;

                            if ($category === 'TDS' && ($detail['applicability_type'] ?? '') !== 'deduction') {
                                throw new \Exception('For TDS, only Deduction is allowed.');
                            }

                            if ($category === 'TCS' && ($detail['applicability_type'] ?? '') !== 'collection') {
                                throw new \Exception('For TCS, only Collection is allowed.');
                            }
                        }

                        if ($saleCount > 1 || $purchaseCount > 1) {
                            throw new \Exception('Only one Sale and one Purchase row are allowed for TDS/TCS.');
                        }
                        if (!$percentagesConsistent) {
                            throw new \Exception('All TDS/TCS rows must have the same tax percentage value.');
                        }
                    }

                    if (isset($detailData['id'])) {
                        $taxDetail = $tax->taxDetails()->where('id', $detailData['id'])->first();
                        if ($taxDetail) {
                            $taxDetail->update([
                                'ledger_id' => isset($detailData['ledger_id']) ? $detailData['ledger_id'] : null,
                                'ledger_group_id' => isset($detailData['ledger_group_id']) ? $detailData['ledger_group_id'] : null,
                                'tax_type' => $detailData['tax_type'],
                                'tax_percentage' => $detailData['tax_percentage'],
                                'place_of_supply' => $detailData['place_of_supply'],
                                'applicability_type' => $detailData['applicability_type'],
                                'is_purchase' => isset($detailData['is_purchase']) && $detailData['is_purchase'] == '1',
                                'is_sale' => isset($detailData['is_sale']) && $detailData['is_sale'] == '1',
                            ]);
                            $newTaxDetailIds[] = $taxDetail->id;
                        }
                    } else {
                        $detailData['tax_id'] = $tax->id;
                        $newTaxDetail = TaxDetail::create($detailData);
                        $newTaxDetailIds[] = $newTaxDetail->id;
                    }
                }
                $tax->taxDetails()->whereNotIn('id', $newTaxDetailIds)->delete();
            } else {
                $tax->taxDetails()->delete();
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
                'data' => $tax,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getLedger(Request $request)
    {
        $searchTerm     = $request->input('q', '');
        $taxCategory    = $request->input('tax_category');
        $taxType        = $request->input('tax_type');
        $taxPercentage  = $request->input('tax_percentage');
        $transactionType = $request->input('transaction_type');

        $query = Ledger::query()
            ->where('status', 1);

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('code', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($taxCategory === 'GST') {
            if (!empty($taxType)) {
                $query->where('tax_type', $taxType);
            }
            if (!empty($taxPercentage)) {
                $query->where('tax_percentage', $taxPercentage);
            }
        }

        if ($taxCategory === 'TDS') {
            if (empty($transactionType) || $transactionType === 'purchase') {
                if (!empty($taxType)) {
                    $query->where('tds_section', $taxType);
                }
                if (!empty($taxPercentage)) {
                    $query->where('tds_percentage', $taxPercentage);
                }
            }
            if ($transactionType === 'sale') {
                $currentAssetsGroup = Group::where('name', 'Current Assets')->first();

                if ($currentAssetsGroup) {
                    $childGroupIds = $currentAssetsGroup->getAllChildIds();
                    $groupIds = array_merge([$currentAssetsGroup->id], $childGroupIds);
                    $stringGroupIds = array_map('strval', $groupIds);
                    $query->where(function ($q) use ($stringGroupIds) {
                        foreach ($stringGroupIds as $id) {
                            $q->orWhereJsonContains('ledger_group_id', $id);
                        }
                    });
                } else {
                    \Log::warning('Current Assets Group not found.');
                }
            }
        }

        if ($taxCategory === 'TCS') {
            if ($transactionType === 'purchase') {
                $currentAssetsGroup = Group::where('name', 'Current Assets')->first();
                if ($currentAssetsGroup) {
                    $childGroupIds = $currentAssetsGroup->getAllChildIds();
                    $groupIds = array_merge([$currentAssetsGroup->id], $childGroupIds);
                    $stringGroupIds = array_map('strval', $groupIds);
                    $query->where(function ($q) use ($stringGroupIds) {
                        foreach ($stringGroupIds as $id) {
                            $q->orWhereJsonContains('ledger_group_id', $id);
                        }
                    });
                } else {
                    \Log::warning('Current Assets Group not found.');
                }
            }
            if (empty($transactionType) || $transactionType === 'sale') {
                if (!empty($taxType)) {
                    $query->where('tcs_section', $taxType);
                }
                if (!empty($taxPercentage)) {
                    $query->where('tcs_percentage', $taxPercentage);
                }
            }
        }

        $results = $query->limit(10)->get(['id', 'code', 'name']);
        return response()->json($results);
    }

    public function getTaxPercentage(Request $request)
    {
        $taxCategory = $request->input('tax_category');
        $taxType = strtolower($request->input('tax_type'));
        $taxPercentage = null;

        if ($taxCategory === 'TDS' || $taxCategory === 'TCS') {
            $taxPercentage = Ledger::where('status', 1)
                ->when($taxCategory === 'TDS', function ($query) use ($taxType) {
                    return $query->where('tds_section', $taxType);
                })
                ->when($taxCategory === 'TCS', function ($query) use ($taxType) {
                    return $query->where('tcs_section', $taxType);
                })
                ->value($taxCategory === 'TDS' ? 'tds_percentage' : 'tcs_percentage');
        }

        return response()->json(['tax_percentage' => $taxPercentage]);
    }

    public function deleteTaxDetail($id)
    {
        DB::beginTransaction();

        try {
            $taxDetail = TaxDetail::findOrFail($id);
            $result = $taxDetail->deleteWithReferences();
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the record: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $tax = Tax::findOrFail($id);

            $referenceTables = [
                'erp_tax_details' => ['tax_id'],
            ];

            $result = $tax->deleteWithReferences($referenceTables);

            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the Tax record: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function testCalculateTax(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $firstAddress = $organization->addresses->first();
        if ($firstAddress) {
            $fromCountry = $firstAddress->country_id;
            $fromState = $firstAddress->state_id;
        } else {
            return response()->json(['error' => 'No address found for the organization.'], 404);
        }

        $price = $request->input('price', 2);
        $hsnId = $request->input('hsn_id', 15);
        $upToCountry = $request->input('country_id', $fromCountry);
        $upToState = $request->input('state_id', $fromState);
        $transactionType = $request->input('transaction_type', 'sale');
        $date = '2025-01-20';

        try {
            $taxDetails = TaxHelper::calculateTax($hsnId, $price, $fromCountry, $fromState, $upToCountry, $upToState, $transactionType, $date);
            return response()->json($taxDetails);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function calculateItemTax(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $fromStore = $request->store_id ? true : false;
        if ($fromStore) {
            $erpStore = ErpStore::find($request->store_id)->with('address');
            if (isset($erpStore)) {
                $companyCountryId = $erpStore->address->country_id;
                $companyStateId = $erpStore->address->state_id;
            } else {
                return response()->json(['error' => 'Store not found.'], 404);
            }
        } else {
            $organization = $user->organization;
            $firstAddress = $organization->addresses->first();
            if ($firstAddress) {
                $companyCountryId = $firstAddress->country_id;
                $companyStateId = $firstAddress->state_id;
            } else {
                return response()->json(['error' => 'No address found for the organization.'], 404);
            }
        }

        $price = $request->input('price', 0);
        $hsnId = null;
        $item = Item::find($request->item_id);
        if (isset($item)) {
            $hsnId = $item->hsn_id;
        } else {
            if ($request->hsn_id) {
                $hsnId = $request->hsn_id;
            } else {
                return response()->json(['error' => 'Invalid Item'], 500);
            }
        }
        $transactionType = $request->input('transaction_type', 'sale');
        if ($transactionType === "sale") {
            $fromCountry = $companyCountryId;
            $fromState = $companyStateId;
            $upToCountry = $request->input('party_country_id', $companyCountryId);
            $upToState = $request->input('party_state_id', $companyStateId);
        } else {
            $fromCountry = $request->input('party_country_id', $companyCountryId);
            $fromState = $request->input('party_state_id', $companyStateId);
            $upToCountry = $companyCountryId;
            $upToState = $companyStateId;
        }
        try {
            $taxDetails = TaxHelper::calculateTax($hsnId, $price, $fromCountry, $fromState, $upToCountry, $upToState, $transactionType);
            return response()->json($taxDetails);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function calculateTaxGroups(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        if ($request->store_id) {
            $erpStore = ErpStore::with('address')->find($request->store_id);
            if (!$erpStore) {
                return response()->json(['error' => 'Store not found.'], 404);
            }

            if (!$erpStore->address) {
                return response()->json(['error' => 'Store address not found.'], 404);
            }

            $companyCountryId = $erpStore->address->country_id;
            $companyStateId   = $erpStore->address->state_id;
        } else {
            $organization  = $user->organization;
            $firstAddress  = $organization?->addresses?->first();

            if (!$firstAddress) {
                return response()->json(['error' => 'No address found for the organization.'], 404);
            }

            $companyCountryId = $firstAddress->country_id;
            $companyStateId   = $firstAddress->state_id;
        }

        if ($request->from_country) {
            $companyCountryId = $request->from_country;
        }

        if ($request->from_state) {
            $companyStateId = $request->from_state;
        }

        $price = $request->input('price', 0);
        $hsnId = null;
        $item = Item::find($request->item_id);
        if ($item) {
            $hsnId = $item->hsn_id;
            if (!$hsnId) {
                return response()->json([
                    'price'                  => $price,
                    'total_tax'              => 0.0,
                    'total_amount_after_tax' => $price,
                    'group_taxes'            => [],
                ]);
            }
        } else {
            $hsnId = $request->hsn_id;
            if (!$hsnId) {
                return response()->json([
                    'price'                  => $price,
                    'total_tax'              => 0.0,
                    'total_amount_after_tax' => $price,
                    'group_taxes'            => [],
                ]);
            }
        }

        $transactionType = $request->input('transaction_type', 'purchase');
        $fromCountry = $companyCountryId;
        $fromState = $companyStateId;

        $upToCountry = $request->input('party_country_id', $companyCountryId);
        $upToState = $request->input('party_state_id', $companyStateId);

        try {
            $taxDetails = TaxHelper::calculateTaxGroups($hsnId, $price, $fromCountry, $fromState, $upToCountry, $upToState, $transactionType);
            return response()->json($taxDetails);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function calculateTaxForSalesModule(Request $request, string $alias)
    {
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $fromOrigin = $request->document_id ? TaxHelper::ADDRESS_TYPE_DOCUMENT : ($request->store_id ? TaxHelper::ADDRESS_TYPE_STORE : TaxHelper::ADDRESS_TYPE_ORGANIZATION);
            $modelType = $request->document_type ? $request->document_type : 'original';
            if ($fromOrigin === TaxHelper::ADDRESS_TYPE_DOCUMENT) { // Retrieve address from document
                $document = null;
                if ($alias === ConstantHelper::SR_SERVICE_ALIAS) {
                    if ($modelType == 'history') {
                        $document = ErpSaleReturnHistory::find($request->document_id);
                    } else {
                        $document = ErpSaleReturn::find($request->document_id);
                    }
                } else if ($alias === ConstantHelper::SI_SERVICE_ALIAS || $alias === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS || $alias === ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS || $alias === ConstantHelper::SERVICE_INV_SERVICE_ALIAS) {
                    if ($modelType == 'history') {
                        $document = ErpSaleInvoiceHistory::find($request->document_id);
                    } else {
                        $document = ErpSaleInvoice::find($request->document_id);
                    }
                } else if ($alias === ConstantHelper::PQ_SERVICE_ALIAS) {
                    if ($modelType == 'history') {
                        $document = ErpPqHeaderHistory::find($request->document_id);
                    } else {
                        $document = ErpPqHeader::find($request->document_id);
                    }
                } else {
                    if ($modelType == 'history') {
                        $document = ErpSaleOrderHistory::find($request->document_id);
                    } else {
                        $document = ErpSaleOrder::find($request->document_id);
                    }
                }
                if (isset($document) && isset($document->location_address_details)) {
                    $companyCountryId = $document->location_address_details?->country_id;
                    $companyStateId = $document->location_address_details?->state_id;
                } else {
                    return response()->json(['error' => 'Document not found.'], 404);
                }
            } else if ($fromOrigin === TaxHelper::ADDRESS_TYPE_STORE) { // Retrieve address from store
                $erpStore = ErpStore::with('address')->find($request->store_id);
                if (isset($erpStore) && isset($erpStore->address)) {
                    $companyCountryId = $erpStore->address?->country_id;
                    $companyStateId = $erpStore->address?->state_id;
                } else {
                    return response()->json(['error' => 'Store not found.'], 404);
                }
            } else { // Retrieve address from organization
                $organization = $user->organization;
                $firstAddress = $organization->addresses->first();
                if ($firstAddress) {
                    $companyCountryId = $firstAddress->country_id;
                    $companyStateId = $firstAddress->state_id;
                } else {
                    return response()->json(['error' => 'No address found for the organization.'], 404);
                }
            }
            $price = $request->input('price', 0);
            $hsnId = null;
            $item = Item::find($request->item_id);
            if (isset($item)) {
                $hsnId = $item->hsn_id;
            } else {
                return response()->json(['error' => 'Invalid Item'], 500);
            }
            $transactionType = $request->input('transaction_type', 'sale');
            if ($transactionType === "sale") {
                $fromCountry = $companyCountryId;
                $fromState = $companyStateId;
                $upToCountry = $request->input('party_country_id', $companyCountryId);
                $upToState = $request->input('party_state_id', $companyStateId);
            } else {
                $fromCountry = $request->input('party_country_id', $companyCountryId);
                $fromState = $request->input('party_state_id', $companyStateId);
                $upToCountry = $companyCountryId;
                $upToState = $companyStateId;
            }
            $taxRequired = SaleModuleHelper::checkTaxApplicability(isset($request->customer_id) ? $request->customer_id : ($request->vendor_id ?? 0), $request->header_book_id ?? 0);
            if ($taxRequired) {
                $taxDetails = TaxHelper::calculateTax($hsnId, $price, $fromCountry, $fromState, $upToCountry, $upToState, $transactionType);
                return response()->json($taxDetails);
            } else {
                return response()->json([]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
