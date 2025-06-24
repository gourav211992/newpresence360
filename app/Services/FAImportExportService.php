<?php

namespace App\Services;

use App\Models\Ledger;
use App\Helpers\Helper;
use App\Models\ErpAssetCategory;
use App\Models\FixedAssetRegistration;
use App\Models\ErpStore;
use App\Models\CostCenterOrgLocations;
use App\Models\CostCenter;
use App\Models\Vendor;
use App\Models\Currency;
use App\Helpers\CurrencyHelper;

use Exception;

class FAImportExportService
{
    public function checkRequiredFields(array $data)
    {
        $requiredFields = [
            'asset_code',
            'asset_name',
            'location',
            'cost_center',
            'category',
            'ledger',
            'capitalize_date', // fixed spelling
            'quantity',
            'maintenance_schedule',
            'useful_life', // optional if not used directly, otherwise remove
            'current_value', // used in calculation
            'vendor',
            'currency',
        ];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new Exception("Missing required fields: " . implode(', ', $missingFields));
        }

        return true;
    }
    public function processData(array $data)
    {

        $org = Helper::getAuthenticatedUser()->organization;
        $existing = FixedAssetRegistration::withDefaultGroupCompanyOrg()->where('asset_code', $data['asset_code'])
            ->first();
        if ($existing) {
            throw new \Exception("Asset Code already exists: {$data['asset_code']}");
        }
        if (!in_array($data['maintenance_schedule'], ['weekly', 'monthly', 'quarterly', 'semi-annually', 'anually'])) {
            throw new \Exception("Invalid maintenance schedule: {$data['maintenance_schedule']}");
        }

        if (!isset($data['quantity']) || filter_var($data['quantity'], FILTER_VALIDATE_INT) === false) {
            throw new \Exception("Quantity must be an integer.");
        }

        if (!isset($data['useful_life']) || !is_numeric($data['useful_life'])) {
            throw new \Exception("Life must be a number.");
        }

        foreach (['capitalize_date', 'book_date'] as $field) {
            if (isset($data[$field]) && $data[$field] !== null) {
                try {
                    $date = new \DateTime($data[$field]);
                    // Format the date to YYYY-mm-DD
                    $data[$field] = $date->format('Y-m-d');
                } catch (\Exception $e) {
                    throw new \Exception("{$field} must be a valid date in a recognizable format.");
                }
            }
        }

        
      
        // Calculate purchase_amount
        $data['purchase_amount'] = $data['current_value'];
        $location = ErpStore::withDefaultGroupCompanyOrg()
            ->where('store_name', $data['location'])
            ->first();
        if (empty($location)) {
            throw new \Exception("Location(s) not found");
        }


        $cost_center = CostCenter::where('status', 'active')->where('name', $data['cost_center'])
            ->withDefaultGroupCompanyOrg()
            ->first();

        if (empty($cost_center)) {
            throw new \Exception($data['cost_center']." Cost Center(s) not found");
        }


    $ledger = Ledger::withDefaultGroupCompanyOrg()
    ->where('name', 'LIKE', '%' . trim($data['ledger'] ?? '') . '%')
    ->first();

        if (empty($ledger)) {
            throw new \Exception($data['ledger']. " Ledger(s) not found");
        }

        $ledgerGroup = $ledger->group() ?? null;
        if (empty($ledgerGroup[0])) {
            throw new \Exception("Ledger group not found for ledger: {$data['ledger']}");
        }

        $category = ErpAssetCategory::withDefaultGroupCompanyOrg()
            ->where('name', $data['category'])
            ->first();

        if (empty($category)) {
            throw new \Exception($data['category'] ." Category(s) not found");
        }
        $vendor = Vendor::withDefaultGroupCompanyOrg()->where('display_name', $data['vendor'])->first();
        if (empty($vendor)) {
            throw new \Exception($data['vendor'] ." Vendor(s) not found");
        }
        $currency = Currency::where('short_name', $data['currency'])->first();
        if (empty($currency)) {
            throw new \Exception($data['currency']. " Currency(s) not found");
        }
        $echange = CurrencyHelper::getCurrencyExchangeRates($currency->id, date('Y-m-d'));
        $echange = $echange['data'];
        if (empty($echange)) {
            throw new \Exception("Exchange rate not found for currency: {$data['currency']}");
        }

        $setup = $category->setup ?? null;
        if (empty($setup)) {
            throw new \Exception("Asset category setup not found for category: {$data['category']}");
        }

        $dep_percetage = $setup->salvage_percentage ?? $org->dep_percetage ?? null;
        if (empty($dep_percetage)) {
            throw new \Exception("Depreciation percentage not found for category: {$data['category']}");
        }

        $life = (int)$data['useful_life'];
        $value = (float)$data['current_value'];

        if ($life <= 0 || $value <= 0) {
            throw new \Exception("Invalid depreciation parameters: life={$life}, value={$value}");
        }
        $depreciationType = $org->dep_type ?? null;
        $currentValue = floatval($value ?? 0);
        $depreciationPercentage = floatval($dep_percetage ?? 0);
        $usefulLife = floatval($life ?? 0);
        $method = $org->dep_method ?? null;

        // Ensure all required values are provided
        if (!$depreciationType || !$currentValue || !$depreciationPercentage || !$usefulLife || !$method) {
            return [
                'error' => 'Missing required values for depreciation calculation.'
            ];
        }

        $salvageValue = round($currentValue * ($depreciationPercentage / 100), 2);

        if ($method === 'SLM') {
            $annualDepreciation = ($currentValue - $salvageValue) / $usefulLife;
            $depreciationRate = round(($annualDepreciation / $currentValue) * 100, 2);
        } elseif ($method === 'WDV') {
            $depreciationRate = round((1 - pow($salvageValue / $currentValue, 1 / $usefulLife)) * 100, 2);
        } else {
            $depreciationRate = 0;
        }
        return [
            'location_id' => $location->id,
            'cost_center_id' => $cost_center->id,
            'category_id' => $category->id,
            'asset_name' => $data['asset_name'],
            'asset_code' => $data['asset_code'],
            'quantity' => (int)$data['quantity'],
            'ledger_id' => $ledger->id,
            'ledger_group_id' => $ledgerGroup[0]->id,
            'capitalize_date' => $data['capitalize_date'],
            'last_dep_date' => $data['capitalize_date'],
            'maintenance_schedule' => $data['maintenance_schedule'],
            'depreciation_method' => $method,
            'useful_life' => $usefulLife,
            'salvage_value' => $salvageValue,
            'depreciation_percentage' => $depreciationPercentage,
            'depreciation_percentage_year' => $depreciationRate,
            'dep_type' => $depreciationType,
            'total_depreciation' => 0,
            'current_value' => $currentValue,
            'current_value_after_dep' => $currentValue,
            'vendor_id' => $vendor->id,
            'currency_id' => $currency->id,
            'sub_total' => $data['current_value'],
            'purchase_amount' => $data['purchase_amount'],
            'book_date' => $data['book_date'] ?? null,
        ];
    }
}
