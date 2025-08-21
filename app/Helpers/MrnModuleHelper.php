<?php
namespace App\Helpers;

use stdClass;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Psy\TabCompletion\Matcher\ConstantsMatcher;

use App\Models\Book;
use App\Models\Vendor;
use App\Models\Organization;
use App\Models\ErpFinancialYear;
use App\Models\ErpVendorPurchaseSummary;
use App\Models\OrganizationBookParameter;

use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\MrnAttribute;
use App\Models\AlternateUOM;
use App\Models\MrnExtraAmount;
use App\Models\MrnAssetDetail;
use App\Models\MrnBatchDetail;
use App\Models\MrnItemLocation;
use App\Models\MrnAssetDetailHistory;
use App\Models\MrnBatchDetailHistory;

use App\Models\MrnHeaderHistory;
use App\Models\MrnDetailHistory;
use App\Models\MrnAttributeHistory;
use App\Models\MrnExtraAmountHistory;
use App\Models\MrnItemLocationHistory;

use App\Models\PRHeader;
use App\Models\PRHeaderHistory;

class MrnModuleHelper  
{ 
    public static function buildVendorPurchaseSummary(MrnHeader $header, ErpFinancialYear $fyYear, MrnHeaderHistory|null $oldHeader = null)
    {
        //Only run for approved documents and MRN
        $requiredStatuses = [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::POSTED];
        if (!in_array($header -> document_status, $requiredStatuses)) {
            return;
        }
        //Create or update the summary
        $vendorPurchaseSummary = ErpVendorPurchaseSummary::firstOrCreate([
            'group_id' => $header -> group_id,
            'company_id' => $header -> company_id,
            'organization_id' => $header -> organization_id,
            'vendor_id' => $header -> vendor_id,
            'fy_id' => $fyYear -> id,
            'currency_id' => $header -> org_currency_id
        ]);
        $vendorPurchaseSummary -> fy_code = $fyYear -> alias;
        //Default to current invoice value to be incremented
        $newInvoiceValue = $header -> taxable_amount;
        $incrementInvoiceValue = $newInvoiceValue;
        //Update - Amend
        if ($oldHeader) {
            //Keep the difference
            $oldHeader = $oldHeader -> taxable_amount;
            $incrementInvoiceValue = $newInvoiceValue - $oldHeader;
        }
        //Increment the value or difference
        $vendorPurchaseSummary -> increment('total_purchase_value', $incrementInvoiceValue);
    }

    public static function buildVendorPurchaseReturnSummary(PRHeader $header, ErpFinancialYear $fyYear, PRHeaderHistory|null $oldHeader = null)
    {
        //Only run for approved documents and SI, SI-DNOTE
        $requiredStatuses = [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::POSTED];
        if (!in_array($header -> document_status, $requiredStatuses)) {
            return;
        }
        //Create or update the summary
        $vendorPurchaseSummary = ErpVendorPurchaseSummary::firstOrCreate([
            'group_id' => $header -> group_id,
            'company_id' => $header -> company_id,
            'organization_id' => $header -> organization_id,
            'vendor_id' => $header -> vendor_id,
            'fy_id' => $fyYear -> id,
            'currency_id' => $header -> org_currency_id
        ]);
        $vendorPurchaseSummary -> fy_code = $fyYear -> alias;
        //Default to current invoice value to be incremented
        $newInvoiceValue = $header -> taxable_amount;
        $incrementInvoiceValue = $newInvoiceValue;
        //Update - Amend
        if ($oldHeader) {
            //Keep the difference
            $oldHeader = $oldHeader -> taxable_amount;
            $incrementInvoiceValue = $newInvoiceValue - $oldHeader;
        }
        //Increment the value or difference
        $vendorPurchaseSummary -> increment('total_return_value', $incrementInvoiceValue);
    }
}