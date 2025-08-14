<?php
namespace App\Helpers;

use NumberToWords\NumberToWords;
class NumberHelper
{
    public static function convertAmountToWords($amount)
    {
        $numberToWords = new NumberToWords();

        // Get the number transformer for English
        $numberTransformer = $numberToWords->getNumberTransformer('en');

        // Split the amount into integer and decimal parts
        $parts = explode('.', number_format($amount, 2, '.', ''));

        $integerPart = (int) $parts[0]; // Get the integer part
        $decimalPart = isset($parts[1]) ? $parts[1] : '00'; // Get the decimal part (cents), default to 00

        // Convert the integer part to words
        $amountInWords = $numberTransformer->toWords($integerPart);


        // Format the result: Add decimal part as '/100' (without converting decimal to words)
        // $amountInWords .= ' and ' . $decimalPart . '/100';

        if ($decimalPart > 0) {
            $paiseInWords = $numberTransformer->toWords($decimalPart);
            $amountInWords .= ' rupees and ' . $paiseInWords . ' paise only';
        }

        return ucfirst($amountInWords);
    }
}
