<?php
namespace App\Services;

use App\Models\MoItem;
use App\Models\ErpSoItem;
use App\Models\PwoSoMapping;
use App\Models\ErpPslipItem;
use App\Models\ErpPslipItemDetail;
use App\Models\PslipBomConsumption;
use App\Models\ErpPslipItemLocation;
use App\Models\PwoStationConsumption;

use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelperV2;
use Illuminate\Support\Facades\Log;
class PslipDeleteService
{
     /**
     * Delete production item for a production slip.
     *
     * @param array $deletedData      Data containing deleted consumption item IDs
     * @param object $productionSlip  The production slip instance
     * @return array                  Success or error response
     */
    public function deleteProductionItems(array $deletedData, $productionSlip)
    {
        // ✅ Early exit if no production items are marked for deletion
        if (empty($deletedData['deletedSiItemIds'])) {
            return self::successResponse("No production items found to delete.");
        }

        // ✅ Fetch all Pslip Items with related item & MO product in one go

        $psItems = ErpPslipItem::whereIn('id', $deletedData['deletedSiItemIds'])->get();

        # all ted remove item level
        foreach($psItems as $psItem) {

            $pslipBomMappings = PslipBomConsumption::where('pslip_id', $productionSlip?->id)
                    ->where('pslip_item_id', $psItem?->id)
                    ->get();

            // Process Delete Pslip Bom Consumptions
            $result = $this->processDeletePslipBomConsumptions($pslipBomMappings, $productionSlip);

            return $result['status'] === 'error'
                ? self::errorResponse($result['message'])
                :$result;

        }

        return self::successResponse("Pslip production items deleted successfully.");
    }

    /**
     * Delete consumption items for a production slip.
     *
     * @param array $deletedData      Data containing deleted consumption item IDs
     * @param object $productionSlip  The production slip instance
     * @return array                  Success or error response
     */
    public function deleteConsumptionItems(array $deletedData, $productionSlip)
    {
        // ✅ Early exit if no consumption items are marked for deletion
        if (empty($deletedData['deletedConsItemIds'])) {
            return self::successResponse("No consumption items found to delete.");
        }

        // ✅ Fetch all BOM consumptions with related item & MO product in one go
        $pslipBomConsumptions = PslipBomConsumption::whereIn('id', $deletedData['deletedConsItemIds'])
            ->get();

        // Process Delete Pslip Bom Consumptions
        $this->processDeletePslipBomConsumptions($pslipBomConsumptions, $productionSlip);

        return self::successResponse("Pslip consumption items deleted successfully.");
    }

    /**
     * Process delete Pslip Bom Consumption
     */

    private function processDeletePslipBomConsumptions($pslipBomMappings, $productionSlip)
    {
        foreach($pslipBomMappings as $keys=>$pslipBomMapping) {
            $attributes = $pslipBomMapping->attributes ?? [];
            $psItem = $pslipBomMapping->pslip_item;

            // 1: Update MO Item consumed quantity
            $this->updateMoItemConsumption($pslipBomMapping, $psItem, $attributes);

            // Prepare attributes for stock checks
            $selectedAttr = collect($attributes)->pluck('attribute_value')->filter()->values()->toArray();

            // 2: Check stock for issue reversal
            $issueCheck = $this->checkIssueStock($pslipBomMapping, $psItem, $productionSlip, $selectedAttr);

            if ($issueCheck !== true) return $issueCheck;

            // 3: Check stock for receipt reversal
            $receiptCheck = $this->checkReceiptStock($psItem, $productionSlip, $selectedAttr);

            if($psItem->subprime_qty > 0) {
                 $receiptCheck = $this->checkReceiptStock($psItem, $productionSlip, $selectedAttr);
            }
            if($productionSlip->rg_sub_store_id && $psItem->rejected_qty > 0) {
                $receiptCheck = $this->checkReceiptRejectStock($psItem, $productionSlip, $selectedAttr);
            }


            if ($receiptCheck !== true) return $receiptCheck;

            // 4: Update MO product & station consumption
            $this->updatePwoStationConsumption($psItem);

            // 5: Update Sales Order & Mapping if applicable
            $this->updateSalesOrderAndMapping($psItem, $productionSlip);

            // 6: Clean up related records and delete psItem
            $this->cleanupPsItem($psItem, $productionSlip);

        }

        return self::successResponse("Success.");

    }

    /**
     * Update consumed quantity in MO Item
     */
    private function updateMoItemConsumption($pslipBomMapping, $psItem, $moProductAttributes)
    {
        $moItem = MoItem::where('mo_id', $psItem?->mo_product?->mo_id)
            ->when($psItem->so_id, fn($q) => $q->where('so_id', $psItem->so_id))
            ->where('item_id', $pslipBomMapping?->item_id)
            ->when(!empty($moProductAttributes), function ($query) use ($moProductAttributes) {
                $query->whereHas('attributes', function ($attrQuery) use ($moProductAttributes) {
                    $attrQuery->where(function ($subQuery) use ($moProductAttributes) {
                        foreach ($moProductAttributes as $poAttribute) {
                            $subQuery->orWhere(fn($q) =>
                                $q->where('item_attribute_id', $poAttribute['item_attribute_id'] ?? $poAttribute['attribute_id'])
                                ->where('attribute_value', $poAttribute['attribute_value'])
                            );
                        }
                    });
                }, '=', count($moProductAttributes));
            })
            ->first();

        if ($moItem) {
            $moItem->consumed_qty -= $pslipBomMapping->consumption_qty;
            $moItem->save();
        }
    }

    /**
     * Validate stock for issue reversal
     */
    private function checkIssueStock($pslipBomMapping, $psItem, $productionSlip, array $selectedAttr)
    {
        $pslipData = [
            'document_header_id' => $productionSlip->id,
            'document_detail_id' => $pslipBomMapping->id,
            'item_id'            => $pslipBomMapping->item_id,
            'store_id'           => $psItem->store_id,
            'document_type'      => ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS,
            'attributes'         => $selectedAttr,
            'sub_store_id'       => $psItem->sub_store_id,
            'transaction_type'   => 'issue',
            'document_status'    => $productionSlip->document_status,
            'book_type'          => ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS,
        ];

        $check = InventoryHelperV2::checkStockForIssueDelete($pslipData, true);
        return $check['status'] === 'error'
            ? self::errorResponse($check['message'])
            : true;
    }

    /**
     * Validate stock for receipt reversal
     */
    private function checkReceiptStock($psItem, $productionSlip, array $selectedAttr)
    {
        $pslipItemData = [
            'qty' => $productionSlip->accepted_qty,
            'document_header_id' => $productionSlip->id,
            'document_detail_id' => $psItem->id,
            'item_id'            => $psItem->item_id,
            'store_id'           => $psItem->store_id,
            'document_type'      => ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS,
            'attributes'         => $selectedAttr,
            'sub_store_id'       => $productionSlip->fg_sub_store_id,
            'transaction_type'   => 'receipt',
            'document_status'    => $productionSlip->document_status,
        ];

        $check = InventoryHelperV2::checkStockForDelete($pslipItemData, true);


        return $check['status'] === 'error'
            ? self::errorResponse($check['message'])
            : true;
    }


    private function checkReceiptRejectStock($psItem, $productionSlip, array $selectedAttr)
    {
        $pslipItemData = [
            'qty' => $productionSlip->accepted_qty,
            'document_header_id' => $productionSlip->id,
            'document_detail_id' => $psItem->id,
            'item_id'            => $psItem->item_id,
            'store_id'           => $psItem->store_id,
            'document_type'      => ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS,
            'attributes'         => $selectedAttr,
            'sub_store_id'       => $productionSlip->rg_sub_store_id,
            'transaction_type'   => 'receipt',
            'document_status'    => $productionSlip->document_status,
        ];

        $check = InventoryHelperV2::checkStockForDelete($pslipItemData, true);


        return $check['status'] === 'error'
            ? self::errorResponse($check['message'])
            : true;
    }

    /**
     * Back update MO Product and PWO Station consumption
     */
    private function updatePwoStationConsumption($psItem)
    {
        if (!$psItem?->mo_product) return;

        $deductQty = $psItem->accepted_qty + $psItem->subprime_qty;

        // Update MO Product
        $psItem->mo_product->pslip_qty -= $deductQty;
        $psItem->mo_product->save();

        // Update PWO Station
        $pwoStationConsumption = PwoStationConsumption::where('pwo_mapping_id', $psItem->mo_product?->pwoMapping?->id)
            ->where('mo_id', $psItem->mo_product->mo_id)
            ->where('station_id', $psItem->mo_product?->mo?->station_id)
            ->first();

        if ($pwoStationConsumption) {
            $pwoStationConsumption->pslip_qty -= $deductQty;
            $pwoStationConsumption->save();
        }
    }

    /**
     * Back update Sales Order Item and Mapping if last station + approved amendment
     */
    private function updateSalesOrderAndMapping($psItem, $productionSlip)
    {
        $deductQty = $psItem->accepted_qty + $psItem->subprime_qty;

        if ($psItem->mo_product?->mo?->is_last_station
            && in_array($productionSlip->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED)
            && ($actionType ?? null) === 'amendment') {

            if ($soItem = ErpSoItem::find($psItem->so_item_id)) {
                $soItem->pslip_qty -= $deductQty;
                $soItem->save();
            }

            if ($pwoSoMappingItem = PwoSoMapping::find($psItem->mo_product->pwo_mapping_id)) {
                $pwoSoMappingItem->pslip_qty -= $deductQty;
                $pwoSoMappingItem->save();
            }
        }

    }

    /**
     * Clean up related records and delete psItem
     */
    private function cleanupPsItem($psItem, $productionSlip)
    {
        ErpPslipItemLocation::where('pslip_item_id', $psItem->id)->delete();
        PslipBomConsumption::where("pslip_id", $productionSlip->id)
            ->where("pslip_item_id", $psItem->id)
            ->delete();
        ErpPslipItemDetail::where('pslip_item_id', $psItem->id)->delete();

        // Remove attributes
        $psItem->attributes()->delete();

        // Delete the item itself
        $psItem->delete();
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
