<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use App\Models\Hsn;

class TaxHelper
{
    const ADDRESS_TYPES = [self::ADDRESS_TYPE_STORE, self::ADDRESS_TYPE_ORGANIZATION];
    const ADDRESS_TYPE_STORE = "Store";
    const ADDRESS_TYPE_ORGANIZATION = "Organization";
    const ADDRESS_TYPE_DOCUMENT = "Document";

    public static function calculateTax(int $hsnId, float $price, int $fromCountry, int $fromState, int $upToCountry, int $upToState, string $transactionType, string $date = null): array
    {
        $taxDetails = [];
        if (empty($date)) {
            $date = now()->toDateString();
        }
        $hsn = Hsn::withDefaultGroupCompanyOrg()
            ->where('id', $hsnId)
            ->where('status', 'active')
            ->first();
        if (!$hsn) {
            throw new \Exception('Active HSN code not found.');
        }

        $placeOfSupply = self::determinePlaceOfSupply($fromCountry, $fromState, $upToCountry, $upToState);
        $taxPatterns = $hsn->taxPatterns()
            ->where('from_price', '<=', $price)
            ->where('upto_price', '>=', $price)
            ->where('from_date', '<=', $date)
            ->orderBy('from_date', 'desc')
            ->get();

        if ($taxPatterns->isEmpty()) {
            return $taxDetails;
        }

        foreach ($taxPatterns as $taxPattern) {
            $taxGroup = $taxPattern->taxGroup;

            if ($taxGroup) {
                $taxCategory = $taxGroup->tax_category;
                $taxes = $taxGroup->taxDetails()
                    ->where('status', 'active')
                    ->get();

                foreach ($taxes as $taxDetail) {
                    if ($taxCategory === 'GST') {
                        if ($taxDetail->place_of_supply && $taxDetail->place_of_supply === $placeOfSupply) {
                            $matches = ($transactionType === 'purchase')
                                ? $taxDetail->is_purchase
                                : $taxDetail->is_sale;

                            if ($matches) {
                                $taxDetails[] = [
                                    'id' => $taxDetail->id,
                                    'applicability_type' => $taxDetail->applicability_type,
                                    'tax_group' => $taxGroup->tax_group,
                                    'tax_percentage' => $taxDetail->tax_percentage,
                                    'tax_type' => $taxDetail->tax_type,
                                    'tax_id' => $taxDetail->tax_id,
                                    'tax_code' => $taxDetail->tax_type,
                                ];
                            }
                        }
                    } else {
                        $matches = ($transactionType === 'purchase')
                            ? $taxDetail->is_purchase
                            : $taxDetail->is_sale;

                        if ($matches) {
                            $taxDetails[] = [
                                'id' => $taxDetail->id,
                                'applicability_type' => $taxDetail->applicability_type,
                                'tax_group' => $taxGroup->tax_group,
                                'tax_percentage' => $taxDetail->tax_percentage,
                                'tax_type' => $taxDetail->tax_type,
                                'tax_id' => $taxDetail->tax_id,
                                'tax_code' => $taxDetail->tax_type,
                            ];
                        }
                    }
                }
            }
        }

        return $taxDetails;
    }

    /**
     * Calculate tax groups based on HSN, price, and location details.
     *
     * @param int $hsnId
     * @param float $price
     * @param int $fromCountry
     * @param int $fromState
     * @param int $upToCountry
     * @param int $upToState
     * @param string $transactionType
     * @param string $date
     * @return array
     */
    public static function calculateTaxGroups(int $hsnId, float $price, int $fromCountry, int $fromState, int $upToCountry, int $upToState, string $transactionType, string $date = ''): array
    {
        $groupedTaxes    = [];
        $totalTaxAmount  = 0.0;

        $taxDetails = self::calculateTax($hsnId, $price, $fromCountry, $fromState, $upToCountry, $upToState, $transactionType, $date);

        if (empty($taxDetails)) {
            return [
                'price'                  => $price,
                'total_tax'              => 0.0,
                'total_amount_after_tax' => $price,
                'group_taxes'            => [],
            ];
        }

        foreach ($taxDetails as $taxDetail) {
            $taxAmount       = round(($price * $taxDetail['tax_percentage']) / 100, 2);
            $totalTaxAmount += $taxAmount;

            $groupName  = $taxDetail['tax_group'];
            $groupId    = $taxDetail['id'];

            if (!isset($groupedTaxes[$groupName])) {
                $groupedTaxes[$groupName] = [
                    'tax_group_id' => $groupId,
                    'group_name'   => $groupName,
                    'taxes'        => [],
                ];
            }

            $groupedTaxes[$groupName]['taxes'][] = [
                'tax_id'            => $taxDetail['tax_id'],
                'tax_percent'       => $taxDetail['tax_percentage'],
                'tax_amount'        => $taxAmount,
                'tax_type'          => $taxDetail['tax_type'],
                'tax_code'          => $taxDetail['tax_code'],
                'applicability_type' => $taxDetail['applicability_type'],
            ];
        }

        return [
            'price'                  => $price,
            'total_tax'              => $totalTaxAmount,
            'total_amount_after_tax' => round($price + $totalTaxAmount, 2),
            'group_taxes'            => array_values($groupedTaxes),
        ];
    }

    private static function determinePlaceOfSupply(int $fromCountry, int $fromState, int $upToCountry, int $upToState): string
    {
        if ($fromCountry === $upToCountry) {
            return ($fromState === $upToState) ? 'Intrastate' : 'Interstate';
        }

        return 'Overseas';
    }
}
