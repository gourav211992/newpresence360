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
    public static function calculateTax(int $hsnId, float $price, int $fromCountry, int $fromState, int $upToCountry, int $upToState, string $transactionType,string $date = null): array
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
                                        'tax_code' =>$taxDetail->tax_type,
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
                                    'tax_code' =>$taxDetail->tax_type,
                                ];
                            }
                        }
                    }
            }
        }

        return $taxDetails;
    }

    private static function determinePlaceOfSupply(int $fromCountry, int $fromState, int $upToCountry, int $upToState): string
    {
        if ($fromCountry === $upToCountry) {
            return ($fromState === $upToState) ? 'Intrastate' : 'Interstate';
        }
        return 'Overseas';
    }
}
