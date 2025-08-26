<?php
namespace App\Helpers;

use stdClass;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
use App\Models\WHM\ErpWhmJob as WhmJob;
use App\Models\WHM\ErpItemUniqueCode as UIC;

use App\Helpers\ItemHelper;
use App\Helpers\InventoryHelperV2;
use App\Models\ErpItemAttribute;
use App\Models\WHM\ErpItemUniqueCode;

class MrnModuleHelper  
{ 
    // Build Vendor Purchase Summary
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

    // Build Vendor Purchase Return Summary
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

    // Close Deviation Job
    public static function closeDeviationJob($mrn, $jobId)
    {
        // dd(config('database.connections.mysql'));
        $mrnItemIds = $mrn->items->pluck('id')->toArray();
        if (!empty($mrnItemIds)) {
            $mrnItems = MrnDetail::whereIn('id', $mrnItemIds)->get();
            $jobData = WhmJob::find($jobId);
            $orderQty = 0;
            $invUomQty = 0;
            if ($jobData) {
                foreach ($mrnItems as $item) {
                    $batchDetails = MrnBatchDetail::where('detail_id', $item->id)->get();
                    if (!empty($batchDetails)) {
                        foreach ($batchDetails as $batchDetail) {
                            $pendingCodes = $item->uniqueCodes()
                                ->where('batch_id', $batchDetail->id)
                                ->where('batch_number', $batchDetail->batch_number)
                                ->where('status', '=',  'pending')
                                ->where('job_id', $jobData->id)
                                ->get();
                            
                            $pendingQty = $pendingCodes->sum('qty');
                            // If no pending codes for this item, skip to next
                            if ($pendingCodes->isEmpty()) {
                                continue;
                            }
                            
                            // Delete all pending codes for this job
                            $batchDetail->uniqueCodes()
                                ->where('status', 'pending')
                                ->where('job_id', $jobData->id)
                                ->delete();

                            // Check if any pending still exists for this job in this item
                            $hasPending = $batchDetail->uniqueCodes()
                                ->where('status', 'pending')
                                ->where('job_id', $jobData->id)
                                ->exists();
                            
                            if (!$hasPending) {
                                // Adjust accepted qty only once per item
                                $batchQty =  ItemHelper::convertToAltUom($item->item_id, $item->uom_id, $pendingQty ?? 0);
                                $batchDetail->decrement('inventory_uom_qty', $pendingQty);
                                $batchDetail->decrement('quantity', $batchQty);

                                $orderQty += $batchQty;
                                $invUomQty += $pendingQty;

                                // Update stock ledger (PUTAWAY pending) using the **delta** only
                                $updatePutwayPendingQty = InventoryHelperV2::updateBatchWiseStockFast(
                                    headerId:   (int) $batchDetail->header_id,
                                    detailId:   (int) $batchDetail->detail_id,
                                    itemId:     (int) $batchDetail->item_id,
                                    lotNumber:  (string) $batchDetail->batch_number,
                                    storeId:    (int) ($batchDetail->mrnHeader->store_id ?? 0),
                                    subStoreId: (int) ($batchDetail->mrnHeader->sub_store_id ?? 0),
                                    deltaInv:   (float) $batchDetail->inventory_uom_qty,
                                    mode:       'putaway'
                                );
                                if($updatePutwayPendingQty['status'] == 'error'){
                                    return self::errorResponse(
                                        "Error while updating Putaway Stock."
                                    );
                                }
                            }
                        }        
                    }

                    // Adjust order qty only once per item
                    $item->decrement('inventory_uom_qty', $invUomQty);
                    $item->decrement('order_qty', $orderQty);
                    if($mrn->reference_type == 'po'){
                        $item->po_item->decrement('grn_qty', $orderQty);
                    }
                    if($mrn->reference_type == 'jo'){
                        $item->jo_item->decrement('grn_qty', $orderQty);
                    }
                    if($mrn->reference_type == 'so'){
                        $item->soItem->decrement('grn_qty', $orderQty);
                    }
                    
                }

                // Final check for pending status across all items
                $jobHasPending = UIC::where('job_id', $jobData->id)
                    ->where('status', 'pending')
                    ->exists();

                $jobData->status = $jobHasPending ? 'deviation' : 'closed';
                $jobData->save();
            }
        }
    }

    // Compute Deviation Breakup
    public static function computeDeviationBreakup(int $mrnId, int $jobId): \Illuminate\Support\Collection
    {
        $mrnDetailIds = MrnDetail::whereIn('mrn_header_id', $mrnId)->pluck('id');
        // Aggregate per-batch for this job
        return UIC::query()
            ->select([
                'batch_id',
                'batch_number',
                DB::raw('SUM(qty)                                         AS total_qty'),
                DB::raw('SUM(CASE WHEN status = "scanned" THEN qty ELSE 0 END) AS scanned_qty'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN qty ELSE 0 END) AS pending_qty'),
            ])
            ->whereIn('morphable_id', $mrnDetailIds)
            ->where('morphable_type', MrnDetail::class)
            ->where('job_id', $jobId)
            ->groupBy('batch_id', 'batch_number')
            ->orderBy('batch_number')
            ->get();
    }

    // pending/scanned totals per item for quick MRN detail updates
    public static function pendingPerItem(int $mrnId, int $jobId): \Illuminate\Support\Collection
    {
        $mrnDetailIds = MrnDetail::whereIn('mrn_header_id', $mrnId)->pluck('id');

        return UIC::query()
            ->select([
                'morphable_id',
                DB::raw('SUM(CASE WHEN status = "pending" THEN qty ELSE 0 END) AS pending_qty'),
                DB::raw('SUM(CASE WHEN status = "scanned" THEN qty ELSE 0 END) AS scanned_qty'),
            ])
            ->whereIn('morphable_id', $mrnDetailIds)
            ->where('morphable_type', MrnDetail::class)
            ->where('job_id', $jobId)
            ->groupBy('morphable_id')
            ->get()
            ->keyBy('morphable_id');
    }

    // Error Response
    private static function errorResponse($message)
    {
        return [
            "status" => "error",
            "code" => "500",
            "message" => $message,
            "data" => null,
        ];
    }

    // Success Response
    private static function successResponse($response,$data)
    {
        return [
            "status" => "success",
            "code" => "200",
            "message" => $response,
            "data" => $data
        ];
    }

}