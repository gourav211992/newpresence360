<?php

namespace App\Http\Controllers;
use Yajra\DataTables\DataTables;
use App\Models\ErpCurrencyExchange;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Http\Requests\CurrencyExchangeRateRequest;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\Helper;
use App\Models\CurrencyExchange;
use Auth;
use App\Models\Organization;


class ExchangeRateController extends Controller
{
    public function getExchangeRate(Request $r){
        $exchangeRate = CurrencyHelper::getCurrencyExchangeRates($r->currency, transactionDate: $r->date);
        return $exchangeRate;
    }

    public function index(Request $request)
{
    $user = Helper::getAuthenticatedUser();
    $organization = Organization::where('id', $user->organization_id)->first(); 
    $organizationId = $organization?->id ?? null;
    $companyId = $organization?->company_id ?? null;

    if ($request->ajax()) {
        $exchangeRates = ErpCurrencyExchange::with(['fromCurrency', 'uptoCurrency'])
            ->orderBy('id', 'desc');

        return DataTables::of($exchangeRates)
            ->addIndexColumn()
            ->editColumn('fromCurrency', function ($rate) {
                return $rate->fromCurrency->name ?? '';
            })
            ->editColumn('uptoCurrency', function ($rate) {
                return $rate->uptoCurrency->name ?? '';
            })
            ->editColumn('exchange_rate', function ($rate) {
                return $rate->exchange_rate ?? '';
            })
            ->editColumn('from_date', function ($rate) {
                return $rate->from_date ?? '';
            })
            ->addColumn('actions', function ($rate) {
                return '
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i data-feather="more-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="#" class="dropdown-item text-danger delete-btn" 
                                data-url="' . route('exchange-rates.destroy', $rate->id) . '" 
                                data-message="Are you sure you want to delete this Exchange Rate?">
                                <i data-feather="trash-2" class="me-50"></i> Delete
                            </a>
                        </div>
                    </div>
                ';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    $fromCurrencies = Currency::all();
    $toCurrencies = Currency::where(function($query) use ($user) {
        $query->where('id', $user->organization?->currency_id)
            ->orWhere('id', $user->organization?->group?->currency_id)
            ->orWhere('id', $user->organization?->company?->currency_id);
    })->distinct('id')->get();
    
    return view('procurement.exchange-rate.index', compact('fromCurrencies', 'toCurrencies'));
}

    public function store(CurrencyExchangeRateRequest  $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validated = $request->validated();
        $parentUrl = ConstantHelper::EXCHANGE_RATE_SERVICE_ALIAS;
        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if ($services && $services['services'] && $services['services']->isNotEmpty()) {
            $firstService = $services['services']->first();
            $serviceId = $firstService->service_id;
            $policyData = Helper::getPolicyByServiceId($serviceId);
            if ($policyData && isset($policyData['policyLevelData'])) {
                $policyLevelData = $policyData['policyLevelData'];
                $validated['group_id'] = $policyLevelData['group_id'];
                $validated['company_id'] = $policyLevelData['company_id'];
                $validated['organization_id'] = $policyLevelData['organization_id'];
            } else {
                $validated['group_id'] = $organization->group_id;
                $validated['company_id'] = $organization->company_id;
                $validated['organization_id'] = null;
            }
        } else {
            $validated['group_id'] = $organization->group_id;
            $validated['company_id'] = $organization->company_id;
            $validated['organization_id'] = null;
        }
    
        $exchangeRate = ErpCurrencyExchange::create($validated);
    
        return response()->json([
            'status' => true,
            'message' => 'Record created successfully',
            'data' => $exchangeRate,
        ]);
    }

    public function update(CurrencyExchangeRateRequest  $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validated = $request->validated();
        $exchangeRate = ErpCurrencyExchange::findOrFail($id);
        $parentUrl = ConstantHelper::EXCHANGE_RATE_SERVICE_ALIAS;
        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if ($services && $services['services'] && $services['services']->isNotEmpty()) {
            $firstService = $services['services']->first();
            $serviceId = $firstService->service_id;
            $policyData = Helper::getPolicyByServiceId($serviceId);
            if ($policyData && isset($policyData['policyLevelData'])) {
                $policyLevelData = $policyData['policyLevelData'];
                $validated['group_id'] = $policyLevelData['group_id'];
                $validated['company_id'] = $policyLevelData['company_id'];
                $validated['organization_id'] = $policyLevelData['organization_id'];
            } else {
                $validated['group_id'] = $organization->group_id;
                $validated['company_id'] = $organization->company_id;
                $validated['organization_id'] = null;
            }
        } else {
            $validated['group_id'] = $organization->group_id;
            $validated['company_id'] = $organization->company_id;
            $validated['organization_id'] = null;
        }
        $exchangeRate->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Record updated successfully',
            'data' => $exchangeRate,
        ]);
    }

    public function destroy($id)
    {
        try {
            $exchangeRate = ErpCurrencyExchange::findOrFail($id);
            $result = $exchangeRate->deleteWithReferences();
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }
    
            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully'
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the exchange rate record: ' . $e->getMessage()
            ], 500);
        }
    }    
    
}
