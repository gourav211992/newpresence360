<?php

namespace App\Helpers;
use App\Helpers\Helper;
use App\Models\Currency;
use App\Models\CurrencyExchange;
use App\Models\Group;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use App\Models\OrganizationGroup;
use Carbon\Carbon;

class CurrencyHelper
{
    /* Get Currency Exchanges for Group, Organization and Company*/
    public static function getCurrencyExchangeRates(int $currencyId, string $transactionDate, float $exhangeRate = null) : array
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::find($user -> organization_id);

        // $group = Group::find($organization ?-> group_id ?? null);
        $group = OrganizationGroup::find($organization ?-> group_id ?? null);

        $company = OrganizationCompany::find($organization ?-> company_id ?? null);
        //Get Ids
        $organizationId = isset($organization -> id) ? $organization -> id : 0;
        $groupId = isset($group -> id) ? $group -> id : 0;
        $companyId = isset($company -> id) ? $company -> id : 0;
        $partyCurrency = Currency::find($currencyId);
        // Check
        if (isset($organization)) {
            $organizationCurrency = Currency::find($organization -> currency_id);

            //Organization Currency Check
            if (isset($organizationCurrency)) {
                if (isset($exhangeRate)) {
                    $organizationExchangeRate = $exhangeRate;
                } else {
                    if ($currencyId === $organizationCurrency -> id) {
                        $organizationExchangeRate = 1;
                    } else {
                        $organizationExchangeRate = self::getCurrencyExchangeRate($currencyId, $organizationCurrency -> id, $transactionDate, $groupId, $companyId, $organizationId);
                    }
                }

                //dd($organizationExchangeRate);

                //Organization Exchange Rate Check
                if (!isset($organizationExchangeRate)) {
                    return array(
                        'status' => false,
                        'message' => 'Exchange Rate not mapped from ' . $partyCurrency -> short_name . ' to ' . $organizationCurrency -> short_name,
                        'data' => null
                    );
                }
            } else {
                return array(
                    'status' => false,
                    'message' => 'Organization currency not mapped',
                    'data' => null
                );
            }
        } else {
            return array(
                'status' => false,
                'message' => 'Organization not mapped',
                'data' => null
            );
        }

         //Company Check
         if (isset($company)) {
            $companyCurrency = Currency::find($company -> currency_id);
            //Company Currency Check
            if (isset($companyCurrency)) {
                if ($organizationCurrency -> id === $companyCurrency -> id) {
                    $companyExchangeRate = 1;
                } else {
                    $companyExchangeRate = self::getCurrencyExchangeRate($organizationCurrency -> id, $companyCurrency -> id, $transactionDate, $groupId, $companyId, $organizationId);
                }
                //Company Exchange Rate Check
                if (!isset($companyExchangeRate)) {
                    return array(
                        'status' => false,
                        'message' => 'Exchange Rate not mapped from ' . $organizationCurrency ?-> short_name .' to '. $companyCurrency -> short_name,
                        'data' => null
                    );
                }
            } else {
                return array(
                    'status' => false,
                    'message' => 'Company currency not mapped',
                    'data' => null
                );
            }
        } else {
            return array(
                'status' => false,
                'message' => 'Company not mapped',
                'data' => null
            );
        }

        //Group Check
        if (isset($group)) {
            $groupCurrency = Currency::find($group -> currency_id);
            //Group Currency Check
            if (isset($groupCurrency)) {
                if ($companyCurrency -> id === $groupCurrency -> id) {
                    $groupExchangeRate = 1;
                } else {
                    $groupExchangeRate = self::getCurrencyExchangeRate($companyCurrency -> id, $groupCurrency -> id, $transactionDate, $groupId, $companyId, $organizationId);
                }
                //Group Exchange Rate Check
                if (!isset($groupExchangeRate)) {
                    return array(
                        'status' => false,
                        'message' => 'Exchange Rate not mapped from ' .$companyCurrency -> short_name .' to ' . $groupCurrency -> short_name,
                        'data' => null
                    );
                }
            } else {
                return array(
                    'status' => false,
                    'message' => 'Group currency not mapped',
                    'data' => null
                );
            }
        } else {
            return array(
                'status' => false,
                'message' => 'Group not mapped',
                'data' => null
            );
        }

        //All currencies and exchange rate found
        return array(
            'status' => true,
            'message' => 'Currency details found',
            'data' => array(
                'party_currency_code'=> @$partyCurrency ?-> short_name,
                'party_currency_id' => @$partyCurrency ?-> id,
                'organization_id' => @$organization -> id,
                'org_currency_id' => @$organizationCurrency -> id,
                'org_currency_code' => @$organizationCurrency -> short_name,
                'org_currency_exg_rate' => @$organizationExchangeRate,
                'company_id' => @$company -> id,
                'comp_currency_id' => @$companyCurrency -> id,
                'comp_currency_code' => @$companyCurrency -> short_name,
                'comp_currency_exg_rate' => @$companyExchangeRate,
                'group_id' => @$group -> id,
                'group_currency_id' => @$groupCurrency -> id,
                'group_currency_code' => @$groupCurrency -> short_name,
                'group_currency_exg_rate' => @$groupExchangeRate
            )
        );
    }

    public static function getCurrencyExchangeRate(int $fromCurrencyId, int $toCurrencyId, string $transactionDate, int $groupId, int $companyId, $organizationId)
    {
        $exchangeRate = CurrencyExchange::selectRaw('*, COALESCE(company_id, ?) as company_id, COALESCE(organization_id, ?) as organization_id', [$companyId, $organizationId])
            -> where('group_id', $groupId)
            -> where('from_currency_id', $fromCurrencyId) -> where('upto_currency_id', $toCurrencyId)
            -> where('from_date', '<=', Carbon::parse($transactionDate)) -> orderBy('from_date', 'DESC') -> first();
        return $exchangeRate->exchange_rate ?? null;
    }
    public static function getOrganizationCurrency(){
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::find($user->organization_id);
        return $organization->currency;
    }
    public static function getGroupCurrency(){
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::find($user -> organization_id);
        $group = OrganizationGroup::find($organization?->group_id ?? null);
        return $group->currency;
    }
    public static function getCompanyCurrency(){
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::find($user -> organization_id);
        $company = OrganizationCompany::find($organization?->company_id ?? null);
        return $company->currency;
    }
    
    public static function convertAmtToGroupCompOrgCurrency(float $amount, int $currencyId, string $transactionDate, float $exchangeRate = null) : array
    {
        //Exchange Rates
        $exhangeRates = self::getCurrencyExchangeRates($currencyId, $transactionDate, $exchangeRate);
        $exhangeRates = $exhangeRates['data'];
        $organizationExchangeRate = $exhangeRates['org_currency_exg_rate'];
        $orgCurrencyAmt = $amount * $organizationExchangeRate;
        $companyExchangeRate = $exhangeRates['comp_currency_exg_rate'];
        $compCurrencyAmt = $orgCurrencyAmt * $companyExchangeRate; 
        $groupExchangeRate = $exhangeRates['group_currency_exg_rate'];
        $groupCurrencyAmt = $compCurrencyAmt * $groupExchangeRate;
        //Amount
        $amountArray = array(
            'org_currency_amount' => round($orgCurrencyAmt, 2),
            'comp_currency_amount' => round($compCurrencyAmt, 2),
            'group_currency_amount' => round($groupCurrencyAmt, 2),
        );
        return $amountArray;
    }
}
