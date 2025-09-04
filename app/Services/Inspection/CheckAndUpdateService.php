<?php
namespace App\Services\Inspection;

use Illuminate\Http\Request;

use App\Models\Item;
use App\Models\MrnDetail;
use App\Models\InspectionHeader;
use App\Models\InspectionDetail;


use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\InventoryHelperV2;

class CheckAndUpdateService
{
    // Validate the quantity of items in MRN against various conditions.
    //  *
    //  * @param Request $request
    //  * @return array
    //  */
    public function validateOrderQuantity($inputData)
    {
        $item = Item::find($inputData['item_id']);
        $type = $inputData['type'];
        $inputQty = (float) $inputData['qty'] ?? 0.00;

        if (!$item) {
            return self::errorResponse("Item not found.", [
                'order_qty' => $inputQty
            ]);
        }

        // === Case 1: Edit (MRN Detail exists) ===
        if (!empty($inputData['inspection_dtl_id'])) {
            $inspDetail = InspectionDetail::find($inputData['inspection_dtl_id']);
            $mrnOrderQty = round((float) $inspDetail->order_qty ?? 0.00, 6);
            if (!$inspDetail) {
                return self::errorResponse("Inspection Item not found.", [
                    'order_qty' => $mrnOrderQty
                ]);
            }

            $mrnDetail = match ($type) {
                default => MrnDetail::find($inputData['mrn_detail_id'])
            };

            if ($mrnDetail) {
                $availableQty = floatval($mrnDetail->order_qty - $mrnDetail->inspection_qty);
                $inputDiff = $inputQty - floatval($inspDetail->order_qty);

                if ($inputQty > $mrnDetail->order_qty) {
                    return self::errorResponse("Order qty cannot be greater than MRN quantity.", [
                        'order_qty' => $availableQty
                    ]);
                }

                if ($availableQty < $inputDiff) {
                    return self::errorResponse("Only {$availableQty} qty can be added. {$mrnDetail->inspection_qty} already used; MRN qty is {$mrnDetail->order_qty}.", [
                        'order_qty' => $availableQty
                    ]);
                }
            }
        } // === Case 2: Create (Direct MRN) ===
        else {
            // Step 1: Identify MRN detail by reference type
            $mrnDetail = match ($type) {
                ConstantHelper::MRN_SERVICE_ALIAS => MrnDetail::find($inputData['mrn_detail_id']),
                default => null
            };

            if ($mrnDetail) {
                $inspectedQty = floatval($mrnDetail->inspection_qty ?? 0);
                $orderQty = floatval($mrnDetail->order_qty ?? 0);
                $totalQty = $inputQty + $inspectedQty;
                $availableQty = floatval($orderQty - $inspectedQty);

                if ($totalQty > $orderQty) {
                    return self::errorResponse("Inspection qty cannot be greater than MRN qty.", [
                        'order_qty' => $availableQty
                    ]);
                }
            }
        }

        // === All Good ===
        return self::successResponse("Quantity Validated", [
            'order_qty' => $inputQty
        ]);
    }

    // Check Confirmed Stock
    private static function checkConfirmedStock($mrnItem, $inputQty)
    {
        $inventoryUomQty = ItemHelper::convertToBaseUom($mrnItem->item_id, $mrnItem->uom_id, $inputQty);
        $documentHeaderId = $mrnItem->mrn_header_id;
        $documentDetailId = $mrnItem->id;
        $itemId = $mrnItem->item_id;
        $uomId = $mrnItem->uom_id;
        $storeId = $mrnItem->store_id;
        $subStoreId = $mrnItem->sub_store_id;
        $documentStatus = $mrnItem->document_status;
        $selectedAttr = collect($mrnItem->attributes)->pluck('attr_value')->filter()->values()->toArray();
        $mrnData = [
            'document_header_id' => $documentHeaderId,
            'document_detail_id' => $documentDetailId,
            'uom_id' => $uomId,
            'item_id' => $itemId,
            'store_id' => $storeId,
            'document_type' => 'mrn',
            'attributes' => $selectedAttr,
            'sub_store_id' => $subStoreId,
            'transaction_type' => 'receipt',
            'document_status' => $documentStatus,
            'inventory_uom_qty' => $inventoryUomQty
        ];
        $checkStockAvailable = InventoryHelperV2::checkStockForUpdate($mrnData);
        return $checkStockAvailable;
    }

    private static function errorResponse($message, $inputQty)
    {
        return [
            "code" => "500",
            "status" => "error",
            "order_qty" => $inputQty,
            "message" => $message,
        ];

    }

    private static function successResponse($response, $inputQty)
    {
        return [
            "code" => "200",
            "status" => "success",
            "order_qty" => $inputQty,
            "message" => $response,
        ];
    }
}
