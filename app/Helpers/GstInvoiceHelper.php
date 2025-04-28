<?php

namespace App\Helpers;
use App\Models\Customer;
use App\Models\Vendor;

class GstInvoiceHelper  
{ 
    const B2B_INVOICE_TYPE = "B2B";
    const B2C_INVOICE_TYPE = "B2C";
    const EXPORT_INVOICE_TYPE = "Export";
    
    public static function getGstInvoiceType($partyId, $partyCountryId, $sellerCountryId, string $partyType = 'customer') : string|null
    {
        //Retrieve party first
        $party = null;
        if ($partyType === 'customer') {
            $party = Customer::find($partyId);
        } else if ($partyType === 'vendor') {
            $party = Vendor::find($partyId);
        } else {
            $party = null;
        }
        if (!isset($party)) {
            return null;
        }
        //Get the GST
        $gstRegistered = $party -> compliances ?-> gst_applicable;
        if ($gstRegistered) {
            if ($partyCountryId === $sellerCountryId) {
                return self::B2B_INVOICE_TYPE;
            } else {
                return self::B2B_INVOICE_TYPE;
            }
        } else {
            if ($partyCountryId !== $sellerCountryId) {
                return self::EXPORT_INVOICE_TYPE;
            } else {
                return null;
            }
        }
    }
}