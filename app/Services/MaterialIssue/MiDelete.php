<?php
namespace App\Services\MaterialIssue;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelperV2;
use App\Models\ErpMaterialIssueHeader;
use App\Models\ErpMiItem;
use App\Models\ErpPslipItem;
use App\Models\ErpPwoItem;
use App\Models\JobOrder\JoItem;
use App\Models\JobOrder\JoProduct;
use App\Models\MoItem;
use App\Models\PiItem;
use App\Helpers\Inventory\MaterialIssue\Constants as MIConstants;

class MiDelete
{
    public function deleteByRequest(array $deletedItemIds, ErpMaterialIssueHeader $mi)
    {
        // Delete Mi items
        if (!empty($deletedItemIds)) {
            $miItems = ErpMiItem::whereIn('id', $deletedItemIds)->get();
            //Loop through all the items
            foreach ($miItems as $miItem) {  
                // Check Stock and delete
                $selectedAttr = $miItem->attributes->pluck('attr_value')->filter()->values()->toArray();
                //Issue Stock Delete
                $issueCheck = $this->checkIssueStock($miItem, $miItem->material_issue_id, $mi->document_status, $selectedAttr);
                if ($issueCheck !== true) return $issueCheck;

                //Receipt Stock Delete
                $receiptCheck = $this->checkReceiptStock($miItem, $miItem->material_issue_id, $mi->document_status, $selectedAttr);
                if ($receiptCheck !== true) return $receiptCheck;

                $miQty = $miItem->issue_qty;

                // MFG ORDER
                if (isset($miItem -> mo_item_id)) {
                    //Back update in MO ITEM
                    $moItem = MoItem::find($miItem -> mo_item_id);
                    if (isset($moItem)) {
                        $moItem -> mi_qty = $moItem -> mi_qty - $miQty;
                        $moItem -> save();
                    }
                }
                //PWO
                if (isset($miItem -> pwo_item_id)) {
                    //Back update in PWO ITEM
                    $pwoItem = ErpPwoItem::find($miItem -> pwo_item_id);
                    if (isset($pwoItem)) {
                        $pwoItem -> mi_qty = $pwoItem -> mi_qty - $miQty;
                        $pwoItem -> save();
                    }
                }
                //PURCHASE INDENT
                if (isset($miItem -> pi_item_id)) {
                    //Back update in PI ITEM
                    $piItem = PiItem::find($miItem -> pi_item_id);
                    if (isset($piItem)) {
                        $piItem -> mi_qty = $piItem -> mi_qty - $miQty;
                        $piItem -> save();
                    }
                }
                //JO (SUB CONTRACTING)
                if (isset($miItem -> jo_item_id)) {
                    //Back update in JO ITEM
                    $joItem = JoItem::find($miItem -> jo_item_id);
                    if (isset($joItem)) {
                        $joItem -> mi_qty = $joItem -> mi_qty - $miQty;
                        $joItem -> save();
                    }
                }
                //JO (JOB WORK)
                if (isset($miItem -> jo_product_id)) {
                    //Back update in JO ITEM
                    $joProduct = JoProduct::find($miItem -> jo_product_id);
                    if (isset($joProduct)) {
                        $joProduct -> mi_qty = $joProduct -> mi_qty - $miQty;
                        $joProduct -> save();
                    }
                }
                //Production Slip
                if (isset($miItem -> pslip_item_id)) {
                    //Back update in PSLIP ITEM
                    //Accepted
                    $qtyKey = MIConstants::MI_ACCEPTED_QTY_KEY;
                    if ($miItem -> pslip_issue_type === 'B') {
                        $qtyKey = MIConstants::MI_SUB_STANDARD_QTY_KEY;
                    }
                    $pslipItem = ErpPslipItem::find($miItem -> pslip_item_id);
                    if (isset($pslipItem)) {
                        $pslipItem -> {$qtyKey} = $pslipItem -> {$qtyKey} - $miQty;
                        $pslipItem -> save();
                    }
                }
                // //Delete all location references
                // $miItem->to_item_locations()->delete();
                // $miItem->from_item_locations()->delete();
                // Delete all Attributes
                $miItem->attributes()->delete();
                //Final item delete
                $miItem->delete();
            }
        }
        //Success
        $data = self::successResponse("MI Item deleted successfully.");
        return $data;
    }

    /**
     * Validate stock for issue reversal
     */
    private function checkIssueStock(ErpMiItem $miItem, int $headerId, string $documentStatus, array $selectedAttr)
    {
        $miData = [
            'document_header_id' => $headerId,
            'document_detail_id' => $miItem -> id,
            'item_id'            => $miItem->item_id,
            'store_id'           => $miItem->from_store_id,
            'document_type'      => ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME,
            'attributes'         => $selectedAttr,
            'sub_store_id'       => $miItem->from_sub_store_id,
            'station_id'         => $miItem->from_station_id,
            'wip_station_id'     => $miItem->wip_station_id,
            'stock_type'         => $miItem->stock_type,
            'transaction_type'   => 'issue',
            'document_status'    => $documentStatus,
            'book_type'          => ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME,
            'qty'                => $miItem -> inventory_uom_qty
        ];

        $check = InventoryHelperV2::checkStockForIssueDelete($miData, true);
        return $check['status'] === 'error'
            ? self::errorResponse($check['message'])
            : true;
    }

    /**
     * Validate stock for receipt reversal
     */
    private function checkReceiptStock(ErpMiItem $miItem, int $headerId, string $documentStatus, array $selectedAttr)
    {    
         $miData = [
            'document_header_id' => $headerId,
            'document_detail_id' => $miItem -> id,
            'item_id'            => $miItem->item_id,
            'store_id'           => $miItem->to_store_id,
            'document_type'      => ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME,
            'attributes'         => $selectedAttr,
            'sub_store_id'       => $miItem->to_sub_store_id,
            'station_id'         => $miItem->to_station_id,
            'wip_station_id'     => $miItem->wip_station_id,
            'stock_type'         => $miItem->stock_type,
            'transaction_type'   => 'receipt',
            'document_status'    => $documentStatus,
            'book_type'          => ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME,
            'qty'                => $miItem -> inventory_uom_qty
        ];

        $check = InventoryHelperV2::checkStockForDelete($miData, true);

        
        return $check['status'] === 'error'
            ? self::errorResponse($check['message'])
            : true;
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
