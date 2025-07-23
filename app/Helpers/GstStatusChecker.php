<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use App\Models\Customer;
use App\Models\Vendor;
use App\Helpers\EInvoiceHelper;
class GstStatusChecker
{
    /**
     * Check and log invalid GST numbers for customers and vendors.
     *
     * @return void
     */
    
    public static function checkInvalidGst()
    {
        $customers = Customer::with('compliances')->get();
        $vendors = Vendor::with('compliances')->get();
        $entities = $customers->concat($vendors);
    
        foreach ($entities as $entity) {
            $compliance = $entity->compliances;

            if (!$compliance || !$compliance->gstin_no) {
                Log::info("No GSTIN found for {$entity->company_name} (ID: {$entity->id})");
                $entity->update(['gst_status' => null]);
                continue;
            }
    
            $gstinNo = $compliance->gstin_no;
    
            try {
                $gstValidation = EInvoiceHelper::validateGstinName($gstinNo);
                $gstDataRaw = $gstValidation['checkGstIn'];
                $gstData = is_string($gstDataRaw)
                    ? json_decode($gstDataRaw, true)
                    : (is_array($gstDataRaw) ? $gstDataRaw : []);

                    if ($gstValidation['Status'] === 1) {
                        $entity->update(['gst_status' => 'ACT']);
                    } else {
                        $entity->update(['gst_status' => 'INACT']);
                    }
    
                $deregistrationDate = $gstData['DtDReg'] ?? null;
               if ($deregistrationDate && $deregistrationDate !== '1900-01-01') {
                    $entity->update(['gst_status' => 'INACT']);
                }
            } catch (\Exception $e) {
                Log::error("Error validating GSTIN for {$entity->company_name} (ID: {$entity->id}): " . $e->getMessage());
                $entity->update(['gst_status' => null]);
            }
        }
    
        Log::info('GST Status Check completed for all entities.');
    }
}