<?php
namespace App\Services\Inspection;

use App\Models\InspChecklist;
use App\Models\InspectionHeader;
use App\Models\InspectionDetail;
use App\Models\InspBatchDetail;
use App\Models\InspectionItemAttribute;

use App\Models\InspBatchDetailHistory;
use App\Models\InspectionHeaderHistory;
use App\Models\InspectionDetailHistory;
use App\Models\InspectionItemLocation;
use App\Models\InspectionItemAttributeHistory;

use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\InventoryHelperV2;

class DeleteService
{
    public function deleteByRequest(array $deletedData, $mrn)
    {
        // Delete MRN items
        if (!empty($deletedData['deletedMrnItemIds'])) {
            $inspItems = InspectionDetail::whereIn('id', $deletedData['deletedMrnItemIds'])->get();

            foreach ($inspItems as $inspItem) {
                if(!empty($inspItem->batches)){
                    foreach ($inspItem->batches as $inspbatch) {

                    }
                    $itemName = $inspItem->item->item_name;
                }    
                
                // Check Stock and delete
                $documentHeaderId = $inspItem->mrn_header_id;
                $documentDetailId = $inspItem->id;
                $itemId = $inspItem->item_id;
                $storeId = $mrn->store_id;
                $subStoreId = $mrn->sub_store_id;
                $documentStatus = $mrn->document_status;
                $selectedAttr = collect($inspItem->attributes)->pluck('attr_value')->filter()->values()->toArray();
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
                    'document_header_id' => $documentHeaderId,
                    'document_detail_id' => $documentDetailId,
                    'item_id' => $itemId,
                    'store_id' => $storeId,
                    'document_type' => 'mrn',
                    'attributes' => $selectedAttr,
                    'sub_store_id' => $subStoreId,
                    'transaction_type' => 'receipt',
                    'document_status' => $documentStatus,
                ];
                $checkStockAvailable = InventoryHelperV2::checkStockForDelete($mrnData, 'true');
                if ($checkStockAvailable['status'] === 'error') {
                    $data = self::errorResponse($checkStockAvailable['message']);
                    return $data;
                }

                $orderQty = $inspItem->order_qty;

                if ($geItem = $inspItem->geItem) {
                    $geItem->mrn_qty -= $orderQty;
                    $geItem->save();
                }

                if ($asnItem = $inspItem->asnItem) {
                    $asnItem->grn_qty -= $orderQty;
                    $asnItem->save();
                }

                switch ($mrn->reference_type) {
                    case ConstantHelper::JO_SERVICE_ALIAS:
                        if ($joItem = $inspItem->joItem) {
                            $joItem->grn_qty -= $orderQty;
                            $joItem->save();
                        }
                        break;

                    case ConstantHelper::SO_SERVICE_ALIAS:
                        if ($soItem = $inspItem->soItem) {
                            $soItem->grn_qty -= $orderQty;
                            $soItem->save();
                        }
                        break;

                    case ConstantHelper::PO_SERVICE_ALIAS:
                        if ($poItem = $inspItem->poItem) {
                            $poItem->grn_qty -= $orderQty;
                            $poItem->save();
                        }
                        break;
                }

                $inspItem->teds()->delete();
                $inspItem->attributes()->delete();
                $inspItem->delete();
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
