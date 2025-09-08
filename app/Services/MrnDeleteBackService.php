<?php
namespace App\Services;

use DB;

use App\Models\MrnDetail;
use App\Models\MrnExtraAmount;
use App\Models\MrnItemLocation;

use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\InventoryHelperV2;

class MrnDeleteBackService
{
    // Delete By Request
    public function deleteByRequest(array $deletedData, $mrn)
    {
        return DB::transaction(function () use ($deletedData, $mrn) {

            // ---- header-level deletes (unchanged) ----
            MrnExtraAmount::whereIn('id', $deletedData['deletedHeaderExpTedIds']  ?? [])->delete();
            MrnExtraAmount::whereIn('id', $deletedData['deletedHeaderDiscTedIds'] ?? [])->delete();
            MrnExtraAmount::whereIn('id', $deletedData['deletedItemDiscTedIds']   ?? [])->delete();

            // ---- item location deletes (unchanged) ----
            MrnItemLocation::whereIn('id', $deletedData['deletedItemLocationIds'] ?? [])->delete();

            // ---- 1) PARTIAL: delete selected batches for selected mrn_detail(s) ----
            if (!empty($deletedData['deletedMrnItemBatchIds']) && is_array($deletedData['deletedMrnItemBatchIds'])) {
                foreach ($deletedData['deletedMrnItemBatchIds'] as $mrnDetailId => $batchIds) {
                    $mrnItem = MrnDetail::with(['item', 'batches'])->find($mrnDetailId);
                    if (!$mrnItem) continue;

                    $resp = $this->deleteMrnItemBatches($mrnItem, (array) $batchIds, $mrn);
                    if ($resp['status'] === 'error') {
                        return $resp;
                    }
                }
            }

            // ---- 2) FULL ITEM delete: delete remaining batches then item ----
            if (!empty($deletedData['deletedMrnItemIds'])) {
                $mrnItems = MrnDetail::with(['item','batches'])->whereIn('id', $deletedData['deletedMrnItemIds'])->get();

                foreach ($mrnItems as $mrnItem) {
                    $itemName = $mrnItem->item->item_name;

                    if ($mrnItem->purchase_bill_qty > 0 || $mrnItem->pr_qty > 0) {
                        return self::errorResponse($itemName . " has been used in purchase bill so cannot be deleted from this MRN.");
                    }

                    // If you want to allow delete when each batch has 0 inspection, move this guard to per-batch.
                    if ($mrnItem->inspection_qty > 0) {
                        return self::errorResponse($itemName . " can not be deleted because of inspection required.");
                    }

                    // first delete all batches for this item (will also reverse stock per batch)
                    $allBatchIds = $mrnItem->batches->pluck('id')->all();
                    if (!empty($allBatchIds)) {
                        $resp = $this->deleteMrnItemBatches($mrnItem, $allBatchIds, $mrn);
                        if ($resp['status'] === 'error') {
                            return $resp;
                        }
                    }

                    // remove item relations and the mrn item itself
                    $mrnItem->teds()->delete();
                    $mrnItem->attributes()->delete();
                    $mrnItem->delete();
                }
            }

            return self::successResponse("MRN deleted successfully.");
        });
    }

    /**
     * Delete given batches for an MRN item (reverse stock batch-wise and update linked docs).
     * Qty to reverse per batch = batch.order_qty (as requested).
     * We also pass inventory_uom_qty from batch to InventoryHelperV2.
     */
    protected function deleteMrnItemBatches(MrnDetail $mrnItem, array $batchIds, $mrn)
    {
        if (empty($batchIds)) {
            return self::successResponse("No batches to delete.");
        }

        $itemName        = $mrnItem->item->item_name;
        $storeId         = $mrn->store_id;
        $subStoreId      = $mrn->sub_store_id;
        $documentStatus  = $mrn->document_status;
        $selectedAttr    = collect($mrnItem->attributes)->pluck('attr_value')->filter()->values()->toArray();

        // If you keep the item-level guards for partial deletes, leave these here
        if ($mrnItem->purchase_bill_qty > 0 || $mrnItem->pr_qty > 0) {
            return self::errorResponse($itemName . " has been used in purchase bill so cannot be deleted.");
        }

        // Load only requested batches
        $batches = $mrnItem->batches()->whereIn('id', $batchIds)->get();

        // Keep total to adjust linked docs
        $totalOrderQtyRemoved = 0.0;

        foreach ($batches as $batch) {
            // Batch fields that we need (adjust names if your columns differ)
            // order_qty             -> the qty to reverse (you requested this)
            // inventory_uom_qty     -> send to InventoryHelperV2
            // batch_number, id      -> batch context
            $batchOrderQty     = (float) ($batch->order_qty ?? 0);
            $inventoryUomQty   = (float) ($batch->inventory_uom_qty ?? 0); // may be 0/NULL if not present

            // If you need to block when a batch has inspection > 0, do it here:
            // if (($batch->inspection_qty ?? 0) > 0) {
            //     return self::errorResponse("Batch {$batch->batch_number} cannot be deleted because inspection quantity exists.");
            // }

            // Build a batch-aware payload for your helpers
            $mrnDataBase = [
                'document_header_id' => $mrnItem->mrn_header_id,
                'document_detail_id' => $mrnItem->id,
                'item_id'            => $mrnItem->item_id,
                'store_id'           => $storeId,
                'sub_store_id'       => $subStoreId,
                'document_type'      => 'mrn',
                'document_status'    => $documentStatus,
                'attributes'         => $selectedAttr,

                // batch context
                'batch_id'           => $batch->id,
                'batch_number'       => $batch->batch_number,

                // quantities expected by your helper
                'quantity'           => $batchOrderQty,   // <- reverse this much from stock
                'inventory_uom_qty'  => $inventoryUomQty, // <- pass through from batch
            ];

            // If JO service needs issue delete validation for the same batch
            if ($mrn->reference_type == ConstantHelper::JO_SERVICE_ALIAS) {
                $issueCheck = InventoryHelperV2::checkStockForIssueDelete(
                    $mrnDataBase + ['transaction_type' => 'issue'],
                    'true'
                );
                if ($issueCheck['status'] === 'error') {
                    return self::errorResponse($issueCheck['message']);
                }
            }

            // Validate we can reverse receipt for this batch
            $receiptCheck = InventoryHelperV2::checkStockForDelete(
                $mrnDataBase + ['transaction_type' => 'receipt'],
                'true'
            );
            if ($receiptCheck['status'] === 'error') {
                return self::errorResponse($receiptCheck['message']);
            }

            // If you have an explicit reversal call for the stock, call it here:
            // InventoryHelperV2::reverseReceiptForBatch($mrnDataBase);

            $totalOrderQtyRemoved += $batchOrderQty;

            // Finally delete the batch
            $batch->delete();
        }

        // Update linked documents by the total batch order qty removed
        if ($totalOrderQtyRemoved > 0) {
            if ($geItem = $mrnItem->geItem) { $geItem->mrn_qty -= $totalOrderQtyRemoved; $geItem->save(); }
            if ($asnItem = $mrnItem->asnItem) { $asnItem->grn_qty -= $totalOrderQtyRemoved; $asnItem->save(); }

            switch ($mrn->reference_type) {
                case ConstantHelper::JO_SERVICE_ALIAS:
                    if ($joItem = $mrnItem->joItem) { $joItem->grn_qty -= $totalOrderQtyRemoved; $joItem->save(); }
                    break;
                case ConstantHelper::SO_SERVICE_ALIAS:
                    if ($soItem = $mrnItem->soItem) { $soItem->grn_qty -= $totalOrderQtyRemoved; $soItem->save(); }
                    break;
                case ConstantHelper::PO_SERVICE_ALIAS:
                    if ($poItem = $mrnItem->poItem) { $poItem->grn_qty -= $totalOrderQtyRemoved; $poItem->save(); }
                    break;
            }
        }

        // If you want to keep item with reduced totals, update and return here.
        // Otherwise, if the item has no batches left, remove the item record too.
        $remaining = $mrnItem->batches()->count();
        if ($remaining === 0) {
            $mrnItem->teds()->delete();
            $mrnItem->attributes()->delete();
            $mrnItem->delete();
        }

        return self::successResponse("Selected batches deleted successfully.");
    }


    private static function errorResponse($message)
    {
        return [
            "status" => "error",
            "code" => "500",
            "message" => $message,
            "data" => null,
        ];

    }

    private static function successResponse($response)
    {
        return [
            "status" => "success",
            "code" => "200",
            "message" => $response
        ];
    }
}
