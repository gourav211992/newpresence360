<?php
namespace App\Services;

use App\Models\MrnDetail;
use App\Models\MrnExtraAmount;
use App\Models\MrnItemLocation;

use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\InventoryHelperV2;

class MrnDeleteService
{
    public function deleteByRequest(array $deletedData, $mrn)
    {
        // Delete header-level TEDs
        MrnExtraAmount::whereIn('id', $deletedData['deletedHeaderExpTedIds'] ?? [])->delete();
        MrnExtraAmount::whereIn('id', $deletedData['deletedHeaderDiscTedIds'] ?? [])->delete();
        MrnExtraAmount::whereIn('id', $deletedData['deletedItemDiscTedIds'] ?? [])->delete();

        // Delete item location
        MrnItemLocation::whereIn('id', $deletedData['deletedItemLocationIds'] ?? [])->delete();

        // Delete MRN items
        if (!empty($deletedData['deletedMrnItemIds'])) {
            $mrnItems = MrnDetail::whereIn('id', $deletedData['deletedMrnItemIds'])->get();

            foreach ($mrnItems as $mrnItem) {
                $itemName = $mrnItem->item->item_name;
                if ($mrnItem->purchase_bill_qty > 0 || $mrnItem->pr_qty > 0) {
                    $errorMessage = $itemName . " has been used in purchase bill so cannot be deleted from this MRN.";
                    $data = self::errorResponse($errorMessage);
                    return $data;
                }

                if ($mrnItem->inspection_qty > 0) {
                    $errorMessage = $itemName . " can not be deleted because of inspection required.";
                    $data = self::errorResponse($errorMessage);
                    return $data;
                }

                // Check Stock and delete
                $documentHeaderId = $mrnItem->mrn_header_id;
                $documentDetailId = $mrnItem->id;
                $qty = $mrnItem->inventory_uom_qty;
                $itemId = $mrnItem->item_id;
                $storeId = $mrn->store_id;
                $subStoreId = $mrn->sub_store_id;
                $documentStatus = $mrn->document_status;
                $selectedAttr = collect($mrnItem->attributes)->pluck('attr_value')->filter()->values()->toArray();
                if ($mrn->reference_type == ConstantHelper::JO_SERVICE_ALIAS) {
                    $mrnData = [
                        'document_header_id' => $documentHeaderId,
                        'document_detail_id' => $documentDetailId,
                        'item_id' => $itemId,
                        'store_id' => $storeId,
                        'document_type' => 'mrn',
                        'attributes' => $selectedAttr,
                        'sub_store_id' => $subStoreId,
                        'transaction_type' => 'issue',
                        'document_status' => $documentStatus,
                        'book_type' => $mrn->book_code,
                    ];
                    $checkStockAvailable = InventoryHelperV2::checkStockForIssueDelete($mrnData, 'true');
                    if ($checkStockAvailable['status'] === 'error') {
                        $data = self::errorResponse($checkStockAvailable['message']);
                        return $data;
                    }
                }
                $mrnData = [
                    'qty' => $qty,
                    'item_id' => $itemId,
                    'store_id' => $storeId,
                    'document_type' => 'mrn',
                    'attributes' => $selectedAttr,
                    'sub_store_id' => $subStoreId,
                    'transaction_type' => 'receipt',
                    'document_status' => $documentStatus,
                    'document_header_id' => $documentHeaderId,
                    'document_detail_id' => $documentDetailId,
                ];
                $checkStockAvailable = InventoryHelperV2::checkStockForDelete($mrnData, 'true');
                if ($checkStockAvailable['status'] === 'error') {
                    $data = self::errorResponse($checkStockAvailable['message']);
                    return $data;
                }

                $orderQty = $mrnItem->order_qty;

                if ($geItem = $mrnItem->geItem) {
                    $geItem->mrn_qty -= $orderQty;
                    $geItem->save();
                }

                if ($asnItem = $mrnItem->asnItem) {
                    $asnItem->grn_qty -= $orderQty;
                    $asnItem->save();
                }

                switch ($mrn->reference_type) {
                    case ConstantHelper::JO_SERVICE_ALIAS:
                        if ($joItem = $mrnItem->joItem) {
                            $joItem->grn_qty -= $orderQty;
                            $joItem->save();
                        }
                        break;

                    case ConstantHelper::SO_SERVICE_ALIAS:
                        if ($soItem = $mrnItem->soItem) {
                            $soItem->grn_qty -= $orderQty;
                            $soItem->save();
                        }
                        break;

                    case ConstantHelper::PO_SERVICE_ALIAS:
                        if ($poItem = $mrnItem->poItem) {
                            $poItem->grn_qty -= $orderQty;
                            $poItem->save();
                        }
                        break;
                }

                $mrnItem->teds()->delete();
                $mrnItem->attributes()->delete();
                $mrnItem->delete();
            }
        }

        $data = self::successResponse($response = "MRN deleted successfully.");
        return $data;
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
