<?php

namespace App\Helpers;

use DB;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\ErpSubStoreParent;

use App\Models\Item;
use App\Models\Unit;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\ErpAttribute;
use App\Models\ItemAttribute;
use App\Models\Configuration;

use App\Models\StockLedger;
use App\Models\StockLedgerReservation;
use App\Models\StockLedgerStoragePoint;

use App\Models\WhLevel;
use App\Models\WhDetail;
use App\Models\MrnDetail;
use App\Models\MrnJoItem;
use App\Models\WhStructure;
use App\Models\WhItemMapping;

use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;

class InventoryHelperV2
{
    public function __construct()
    {
    }

    // Update Stock Receipt
    public static function updateReceiptStock($documentHeader, $inspection = null)
    {
        try {
            $user = Helper::getAuthenticatedUser();

            // --- Check Configuration for UIC scanning
            $config = Configuration::where('type', 'organization')
                ->where('type_id', $user->organization_id)
                ->where('config_key', CommonHelper::ENFORCE_UIC_SCANNING)
                ->whereNull('deleted_at')
                ->first();

            $cfgYes = $config && strcasecmp((string) $config->config_value, 'yes') === 0;

            // cache for sub-store flags
            static $subStoreWarehouseCache = [];

            // returns true if sub-store (or its store) requires warehouse/putaway
            $subStoreHasWarehouse = function (?int $subStoreId) use (&$subStoreWarehouseCache, $documentHeader): bool {
                if (!$subStoreId)
                    return false;
                if (array_key_exists($subStoreId, $subStoreWarehouseCache)) {
                    return $subStoreWarehouseCache[$subStoreId];
                }

                // Only fetch the flag columns; adjust model/columns if different
                // $sub = ErpSubStore::select('id','is_warehouse_required')->find($subStoreId);
                // $flag = (int)($sub->is_warehouse_required ?? 0) === 1;

                $sub = $documentHeader->is_enforce_uic_scanning;
                $flag = (int) ($sub ?? 0) === 1;

                return $subStoreWarehouseCache[$subStoreId] = $flag;
            };

            $documentDetails = $documentHeader->items()
                ->where('is_inspection', 1)
                ->with('batches')        // assumes Detail model has: public function batches() { return $this->hasMany(...); }
                ->get();
            foreach ($documentDetails as $documentDetail) {
                if (empty($documentDetail->batches)) {
                    return self::errorResponse("Error in updateReceiptStock: Batch details not found.");
                }
                foreach ($documentDetail->batches as $detail) {
                    // Find the original HOLD ledger row
                    $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
                        ->where('document_header_id', $detail->header_id)
                        ->where('document_detail_id', $detail->detail_id)
                        ->where('lot_number', $detail->batch_number)
                        ->where('item_id', $detail->item_id)
                        ->where('store_id', $documentHeader->store_id)
                        ->where('sub_store_id', $documentHeader->sub_store_id)
                        ->where('transaction_type', 'receipt')
                        ->whereNull('utilized_id')
                        ->whereRaw('receipt_qty > 0')
                        // ->whereRaw('hold_qty > 0')
                        ->orderBy('original_receipt_date', 'ASC')
                        ->orderBy('document_date', 'ASC')
                        ->orderBy('id', 'ASC')
                        ->get();

                    if (empty($stockLedger)) {
                        continue;
                    }

                    foreach ($stockLedger as $stockLedger) {
                        $acceptedQty = (float) ($detail->accepted_inv_uom_qty ?? 0);
                        $rejectedQty = (float) ($detail->rejected_inv_uom_qty ?? 0);
                        $processedQty = $acceptedQty + $rejectedQty;
                        // Proportional costing (prevents double counting when both exist)
                        $totalItemCost = (float) (($documentDetail->basic_value ?? 0) - (($documentDetail->discount_amount ?? 0) + ($documentDetail->header_discount_amount ?? 0)));
                        $totalProcessed = max($processedQty, 0.0);

                        $acceptedCost = ($acceptedQty > 0 && $totalProcessed > 0)
                            ? round($totalItemCost * ($acceptedQty / $totalProcessed), 2)
                            : 0.0;

                        $rejectedCost = ($rejectedQty > 0 && $totalProcessed > 0)
                            ? round($totalItemCost - $acceptedCost, 2)
                            : 0.0;

                        // ------------------------------------------------------------
                        // 1) ACCEPTED: target store = main store (detail->store_id)
                        // ------------------------------------------------------------
                        if ($acceptedQty > 0) {
                            $acceptedStoreId = (int) ($inspection->store_id ?? $documentHeader->store_id);
                            $acceptedSubStore = (int) ($inspection->sub_store_id ?? $documentHeader->sub_store_id);

                            // only use putaway when cfgYes = true AND sub-store requires warehouse
                            $acceptedUsePutaway = $cfgYes && $subStoreHasWarehouse($acceptedSubStore);
                            $acceptedQtyField = $acceptedUsePutaway ? 'putaway_pending_qty' : 'receipt_qty';
                            // Try to merge into an existing non-hold ledger in that target column
                            $acceptedLedger = StockLedger::withDefaultGroupCompanyOrg()
                                ->where('document_header_id', $detail->header_id)
                                ->where('document_detail_id', $detail->detail_id)
                                ->where('lot_number', $detail->batch_number)
                                ->where('item_id', $detail->item_id)
                                ->where('store_id', $acceptedStoreId)
                                ->where('sub_store_id', $acceptedSubStore)
                                ->where('transaction_type', 'receipt')
                                ->where('hold_qty', 0)
                                ->where($acceptedQtyField, '>', 0)
                                ->whereNull('utilized_id')
                                ->orderBy('id', 'ASC')
                                ->first();

                            if (!$acceptedLedger) {
                                $acceptedLedger = $stockLedger->replicate();
                                $acceptedLedger->hold_qty = 0;
                                $acceptedLedger->utilized_id = null;
                                $acceptedLedger->store_id = $acceptedStoreId;      // ensure correct store
                                $acceptedLedger->sub_store_id = $acceptedSubStore; // ensure correct sub-store
                            }

                            // Reset both qty columns, then set chosen one
                            $acceptedLedger->receipt_qty = 0;
                            $acceptedLedger->putaway_pending_qty = 0;
                            $acceptedLedger->{$acceptedQtyField} = $acceptedQty;

                            $acceptedLedger->cost_per_unit = round($acceptedQty > 0 ? ($acceptedCost / $acceptedQty) : 0, 6);
                            $acceptedLedger->total_cost = $acceptedCost;
                            $acceptedLedger->document_status = $documentHeader->document_status;

                            self::updateStockCost($acceptedLedger);
                            $acceptedLedger->save();
                        }

                        // ------------------------------------------------------------
                        // 2) REJECTED: target store = inspection->rejected_store_id (if present), else main
                        //              target sub-store = inspection->rejected_sub_store_id
                        // ------------------------------------------------------------
                        if ($inspection && $rejectedQty > 0 && ($inspection->rejected_sub_store_id ?? null)) {
                            $rejectedStoreId = (int) ($inspection->store_id ?? $documentHeader->store_id);
                            $rejectedSubStore = (int) $inspection->rejected_sub_store_id;

                            $rejectedUsePutaway = $cfgYes && $subStoreHasWarehouse($rejectedSubStore);
                            $rejectedQtyField = $rejectedUsePutaway ? 'putaway_pending_qty' : 'receipt_qty';

                            $rejectedLedger = StockLedger::withDefaultGroupCompanyOrg()
                                ->where('document_header_id', $detail->header_id)
                                ->where('document_detail_id', $detail->detail_id)
                                ->where('lot_number', $detail->batch_number)
                                ->where('item_id', $detail->item_id)
                                ->where('store_id', $rejectedStoreId)
                                ->where('sub_store_id', $rejectedSubStore)
                                ->where('transaction_type', 'receipt')
                                ->where('hold_qty', 0)
                                ->where($rejectedQtyField, '>', 0)
                                ->whereNull('utilized_id')
                                ->orderBy('id', 'ASC')
                                ->first();

                            if (!$rejectedLedger) {
                                $rejectedLedger = $stockLedger->replicate();
                                $rejectedLedger->hold_qty = 0;
                                $rejectedLedger->utilized_id = null;
                                $rejectedLedger->store_id = $rejectedStoreId;  // move to rejected store (can be different)
                                $rejectedLedger->sub_store_id = $rejectedSubStore; // rejected sub-store
                            }

                            // Reset both qty columns, then set chosen one
                            $rejectedLedger->receipt_qty = 0;
                            $rejectedLedger->putaway_pending_qty = 0;
                            $rejectedLedger->{$rejectedQtyField} = $rejectedQty;

                            $rejectedLedger->cost_per_unit = round($rejectedQty > 0 ? ($rejectedCost / $rejectedQty) : 0, 6);
                            $rejectedLedger->total_cost = $rejectedCost;
                            $rejectedLedger->document_status = $documentHeader->document_status;

                            self::updateStockCost($rejectedLedger);
                            $rejectedLedger->save();
                        }

                        // ------------------------------------------------------------
                        // 3) Reduce or delete original HOLD row
                        // ------------------------------------------------------------
                        // $stockLedger->hold_qty -= $processedQty;
                        $stockLedger->receipt_qty -= $processedQty;

                        if ($stockLedger->receipt_qty <= 0) {
                            $stockLedger->attributes()->delete();
                            $stockLedger->delete();
                        } else {
                            $stockLedger->save(); // Partial hold remains
                        }
                    }
                }
            }

            return self::successResponse("MRN details updated successfully.", []);
        } catch (\Exception $e) {
            return self::errorResponse("Error in updateReceiptStock: " . $e->getMessage());
        }
    }

    // Update document status while update mrn
    private static function updateStockCost($stockLedger)
    {
        // Convert base cost to different currency levels
        $orgCostPerUnit = $stockLedger->cost_per_unit * $stockLedger->org_currency_exg_rate;
        $orgTotalCost = $stockLedger->total_cost * $stockLedger->org_currency_exg_rate;

        $compCostPerUnit = $orgCostPerUnit * $stockLedger->comp_currency_exg_rate;
        $compTotalCost = $orgTotalCost * $stockLedger->comp_currency_exg_rate;

        $groupCostPerUnit = $compCostPerUnit * $stockLedger->group_currency_exg_rate;
        $groupTotalCost = $compTotalCost * $stockLedger->group_currency_exg_rate;

        // Round and assign
        $stockLedger->org_currency_cost_per_unit = round($orgCostPerUnit, 6);
        $stockLedger->org_currency_cost = round($orgTotalCost, 2);
        $stockLedger->comp_currency_cost_per_unit = round($compCostPerUnit, 6);
        $stockLedger->comp_currency_cost = round($compTotalCost, 2);
        $stockLedger->group_currency_cost_per_unit = round($groupCostPerUnit, 6);
        $stockLedger->group_currency_cost = round($groupTotalCost, 2);
    }


    // Step 1: Check if stock is available (confirmed and unconfirmed)
    private static function checkStockAvailable($documentDetail)
    {
        $selectedAttr = $documentDetail['selectedAttr'] ?? [];
        $attributeGroups = Attribute::whereIn('id', $selectedAttr)->pluck('attribute_group_id')->values();

        $baseQuery = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id', $documentDetail['document_header_id'])
            ->where('document_detail_id', $documentDetail['document_detail_id'])
            ->where('item_id', $documentDetail['item_id'])
            ->where('store_id', $documentDetail['store_id'])
            ->where('sub_store_id', $documentDetail['sub_store_id'])
            ->where('transaction_type', $documentDetail['transaction_type'])
            ->where('book_type', $documentDetail['document_type'])
            ->whereNull('utilized_id');
        // ->where(function ($q) {
        //     $q->whereNull('hold_qty')->orWhere('hold_qty', '<=', 0);
        // });
        if($documentDetail['is_delete'])
        {
            $baseQuery->where('receipt_qty', $documentDetail['qty']);
        }


        // Apply attribute filters
        if ($attributeGroups->isNotEmpty() && !empty($selectedAttr)) {
            foreach ($attributeGroups as $index => $groupId) {
                if (isset($selectedAttr[$index])) {
                    $baseQuery->whereJsonContains('item_attributes', [
                        'attr_name' => (string) $groupId,
                        'attr_value' => (string) $selectedAttr[$index],
                    ]);
                }
            }
        }

        if (isset($documentDetail['station_id']) && $documentDetail['station_id']) {
            $baseQuery->where('station_id', $documentDetail['station_id']);
        }

        if (isset($documentDetail['stock_type']) && $documentDetail['stock_type']) {
            $baseQuery->where('stock_type', $documentDetail['stock_type']);
        }

        if (isset($documentDetail['wip_station_id']) && $documentDetail['wip_station_id']) {
            $baseQuery->where('wip_station_id', $documentDetail['wip_station_id']);
        }

        // Clone query to avoid re-execution conflict
        $confirmedStock = (clone $baseQuery)
            ->whereIn('document_status', ['approved', 'posted', 'approval_not_required'])
            ->first();

        $unConfirmedStock = (clone $baseQuery)
            ->whereNotIn('document_status', ['approved', 'posted', 'approval_not_required'])
            ->first();

        return [
            'confirmedStock' => $confirmedStock,
            'unConfirmedStock' => $unConfirmedStock,
        ];
    }

    private static function deleteIssueStock($documentDetail)
    {
        $mrnDetail = MrnDetail::find($documentDetail['document_detail_id']);
        $RawMaterialData = MrnJoItem::where('header_id', $documentDetail['document_detail_id'])
            ->where('mrn_detail_id', $documentDetail['document_header_id'])->get();
        foreach ($RawMaterialData as $key => $value) {
            $documentDetail = [
                'document_header_id' => $value->mrn_header_id,
                'document_detail_id' => $value->mrn_detail_id,
                'item_id' => $value->mi_item_id,
                'store_id' => $value->store_id,
                'document_type' => 'mrn',
                'attributes' => $value->attributes,
                'sub_store_id' => $value->sub_store_id,
                'transaction_type' => 'issue',
                'document_status' => $value->status,
            ];
            self::deleteIssueStockJobType($documentDetail);
        }
    }

    private static function deleteIssueStockJobType($documentDetail)
    {
        $selectedAttr = $documentDetail['selectedAttr'] ?? [];
        $attributeGroups = Attribute::whereIn('id', $selectedAttr)->pluck('attribute_group_id')->values();
        $baseQuery = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id', $documentDetail['document_header_id'])
            ->where('document_detail_id', $documentDetail['document_detail_id'])
            ->where('item_id', $documentDetail['item_id'])
            ->where('store_id', $documentDetail['store_id'])
            ->where('sub_store_id', $documentDetail['sub_store_id'])
            ->where('transaction_type', 'issue')
            ->where('book_type', $documentDetail['document_type'])
            ->whereNull('utilized_id')
            ->where(function ($q) {
                $q->whereNull('hold_qty')->orWhere('hold_qty', '<=', 0);
            });

        // Apply attribute filters
        if ($attributeGroups->isNotEmpty() && !empty($selectedAttr)) {
            foreach ($attributeGroups as $index => $groupId) {
                if (isset($selectedAttr[$index])) {
                    $baseQuery->whereJsonContains('item_attributes', [
                        'attr_name' => (string) $groupId,
                        'attr_value' => (string) $selectedAttr[$index],
                    ]);
                }
            }
        }

        // Clone query to avoid re-execution conflict
        $issueStock = (clone $baseQuery)
            ->first();

        $utilizedStockLedger = StockLedger::withDefaultGroupCompanyOrg()
            ->where('utilized_id', $issueStock->id)
            ->get();

        $stockQty = 0;
        $isIssueStockDelete = 0;
        if ($utilizedStockLedger->isNotEmpty()) {
            foreach ($utilizedStockLedger as $val) {
                // $normalizedAttributes = self::normalizeJsonAttributes($val->item_attributes);
                $stockQty += $val->receipt_qty;
                // $normalized = InventoryHelperV2::normalizeJsonAttributes($val->item_attributes);

                $potentialMatches = StockLedger::withDefaultGroupCompanyOrg()
                    ->where([
                        'document_header_id' => $val->document_header_id,
                        'document_detail_id' => $val->document_detail_id,
                        'book_type' => $val->book_type,
                        'transaction_type' => $val->transaction_type,
                        'store_id' => $val->store_id,
                        'sub_store_id' => $val->sub_store_id,
                    ])
                    ->whereNull('utilized_id')
                    ->get();

                $target = InventoryHelperV2::normalizeJsonAttributes($val->item_attributes);

                $similarUtilizedRecord = $potentialMatches->first(function ($record) use ($target) {
                    return InventoryHelperV2::normalizeJsonAttributes($record->item_attributes) === $target;
                });

                if ($similarUtilizedRecord) {
                    // Merge quantities and reset utilization
                    $stockQty += $similarUtilizedRecord->receipt_qty;
                    $similarUtilizedRecord->attributes()->delete();
                    $similarUtilizedRecord->delete();
                }

                $val->receipt_qty = $stockQty;
                $val->save();
                $val->total_cost = ($val->receipt_qty * $val->cost_per_unit);
                $val->save();
                self::updateStockCost($val);
                $val->utilized_id = null;
                $val->utilized_date = null;
                $val->save();
            }
            $isIssueStockDelete = 1;
        }
        if ($isIssueStockDelete) {
            if(count($issueStock->attributes) > 0){
                $issueStock?->attributes()->delete();
            }
            $issueStock->delete();
        }
        return true;
    }

    public static function normalizeJsonAttributes($attributes): string
    {
        if (is_string($attributes)) {
            $attributes = json_decode($attributes, true);
        }

        if (!is_array($attributes)) {
            return '[]';
        }

        // Sort individual items
        foreach ($attributes as &$item) {
            if (is_array($item)) {
                ksort($item);
            }
        }

        // Sort by top-level keys to avoid reordering issue
        usort($attributes, function ($a, $b) {
            return strcmp(json_encode($a), json_encode($b));
        });

        return json_encode($attributes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function checkStockForDelete($documentDetail, $isDelete)
    {
        if (!$documentDetail['qty']) {
            return self::errorResponse(
                "Qty can not be null."
            );
        }
        if (!$documentDetail['item_id']) {
            return self::errorResponse(
                "Item can not be null."
            );
        }
        if (!$documentDetail['store_id']) {
            return self::errorResponse(
                "Location can not be null."
            );
        }
        $documentDetail['is_delete'] = 1;
        $availStock = self::checkStockAvailable($documentDetail);
        $confirmedStock = $availStock['confirmedStock'] ?? null;
        $unConfirmedStock = $availStock['unConfirmedStock'] ?? null;

        if ($confirmedStock || $unConfirmedStock) {
            $unConfirmedStock->attributes()->delete();
            $unConfirmedStock->delete();

            return self::successResponse("Stock deleted successfully", [
                'stockLedger' => $documentDetail,
                'isDelete' => $isDelete ? 1 : 0,
            ]);
        }

        return self::errorResponse(
            "You cannot delete this {$documentDetail['document_type']} because stock is utilized."
        );
    }

    public static function checkStockForIssueDelete($documentDetail, $isDelete)
    {
        $documentType = $documentDetail['book_type'] ?? $documentDetail['document_type'];
        if ($documentType == ConstantHelper::MRN_SERVICE_ALIAS) {
            $issueStock = self::deleteIssueStock($documentDetail);
        } else {
            $issueStock = self::deleteIssueStockJobType($documentDetail);
        }

        if ($issueStock) {
            return self::successResponse("Issue stock deleted successfully", [
                'stockLedger' => $documentDetail,
                'isDelete' => $isDelete ? 1 : 0,
            ]);
        }

        return self::errorResponse(
            "You cannot delete this {$documentDetail['document_type']} because stock is already available."
        );
    }

    // Check/Update Stock For Receipt
    public static function checkStockForUpdate($mrnItem)
    {
        $mrnItem['is_delete'] = 0;
        $availStock = self::checkStockAvailable($mrnItem);
        $confirmedStock = $availStock['confirmedStock'] ?? null;
        if ($confirmedStock) {
            if ($confirmedStock->receipt_qty <= $mrnItem['inventory_uom_qty']) {
                return self::successResponse("Available stock", [
                    'stockLedger' => $confirmedStock
                ]);
            }
            $actualQty = ItemHelper::convertToAltUom($mrnItem['item_id'], $mrnItem['uom_id'], $confirmedStock->receipt_qty ?? 0);
            return self::errorResponse(
                "You cannot update this as available stock is only {$actualQty}."
            );
        }

        return self::successResponse("Available stock", [
            'stockLedger' => $mrnItem
        ]);
    }

    // Check/Update Stock For Issue
    public static function checkIssueStockForUpdate($documentDetail)
    {
        $selectedAttr = $documentDetail['selectedAttr'] ?? [];
        $attributeGroups = Attribute::whereIn('id', $selectedAttr)->pluck('attribute_group_id')->values();

        $baseQuery = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id', $documentDetail['document_header_id'])
            ->where('document_detail_id', $documentDetail['document_detail_id'])
            ->where('item_id', $documentDetail['item_id'])
            ->where('store_id', $documentDetail['store_id'])
            ->where('sub_store_id', $documentDetail['sub_store_id'])
            ->where('transaction_type', 'issue')
            ->where('book_type', $documentDetail['document_type'])
            ->whereNull('utilized_id')
            ->where(function ($q) {
                $q->whereNull('hold_qty')->orWhere('hold_qty', '<=', 0);
            });

        // Apply attribute filters
        if ($attributeGroups->isNotEmpty() && !empty($selectedAttr)) {
            foreach ($attributeGroups as $index => $groupId) {
                if (isset($selectedAttr[$index])) {
                    $baseQuery->whereJsonContains('item_attributes', [
                        'attr_name' => (string) $groupId,
                        'attr_value' => (string) $selectedAttr[$index],
                    ]);
                }
            }
        }

        if (isset($documentDetail['station_id']) && $documentDetail['station_id']) {
            $baseQuery->where('station_id', $documentDetail['station_id']);
        }

        if (isset($documentDetail['stock_type']) && $documentDetail['stock_type']) {
            $baseQuery->where('stock_type', $documentDetail['stock_type']);
        }

        if (isset($documentDetail['wip_station_id']) && $documentDetail['wip_station_id']) {
            $baseQuery->where('wip_station_id', $documentDetail['wip_station_id']);
        }

        // Clone query to avoid re-execution conflict
        $issueStock = (clone $baseQuery)
            ->first();

        $checkIssueStock = (clone $baseQuery)
            ->where('receipt_qty', '<', $documentDetail['inventory_uom_qty'])
            ->first();

        if ($checkIssueStock) {
            return self::errorResponse(
                "You cannot increase the quantity as issue stock is only {$issueStock->receipt_qty}."
            );
        } else {
            return self::successResponse("Available Issue Stock", [
                'issueStock' => $issueStock
            ]);
        }
    }

    /**
     * Update MRN receipt stock for a batch by a **delta** quantity.
     * mode = 'putaway' → decrement putaway_pending_qty by $deltaInv
     * else            → set/update receipt_qty (not used in deviation close)
     */
    public static function updateBatchWiseStockFast(
        int $headerId,
        int $detailId,
        int $itemId,
        string $lotNumber,
        ?int $storeId,
        ?int $subStoreId,
        float $deltaInv,
        string $mode = 'putaway'
    ): array {
        $q = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id', $headerId)
            ->where('document_detail_id', $detailId)
            ->where('item_id', $itemId)
            ->where('lot_number', $lotNumber)
            ->where('store_id', $storeId)
            ->where('sub_store_id', $subStoreId)
            ->where('transaction_type', 'receipt')
            ->where('book_type', 'mrn')
            ->whereNull('utilized_id');

        if ($mode === 'putaway') {
            // Only rows still pending putaway and not received
            $stock = (clone $q)
                ->where('putaway_pending_qty', '>', 0)
                ->where(function ($s): void {
                    $s->whereNull('receipt_qty')->orWhere('receipt_qty', '<=', 0);
                })
                ->first();

            if (!$stock) {
                return self::errorResponse('No Putaway Stock Found.');
            }

            $dec = max(0.0, (float) $deltaInv);
            if ($dec <= 0) {
                return self::successResponse('Nothing to decrement.', $stock);
            }

            // Clamp to avoid negative
            $dec = min($dec, (float) $stock->putaway_pending_qty);

            if ($dec > 0) {
                $stock->putaway_pending_qty = (float) $stock->putaway_pending_qty - $dec;
                $stock->total_cost = (float) $stock->putaway_pending_qty * (float) $stock->cost_per_unit;
                $stock->save();

                self::updateStockCost($stock);
            }

            return self::successResponse('Putaway pending updated.', $stock);
        }

        // Fallback branch if ever needed: update receipt_qty to exact value
        $stock = (clone $q)->where('receipt_qty', '>', 0)->first();
        if (!$stock) {
            return self::errorResponse('No Stock Found.');
        }

        $stock->receipt_qty = (float) $deltaInv; // here "deltaInv" is the new target qty
        $stock->total_cost = (float) $stock->receipt_qty * (float) $stock->cost_per_unit;
        $stock->save();

        self::updateStockCost($stock);

        return self::successResponse('Receipt qty updated.', $stock);
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
    private static function successResponse($response, $data)
    {
        return [
            "status" => "success",
            "code" => "200",
            "message" => $response,
            "data" => $data
        ];
    }
}
