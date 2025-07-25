<?php 
namespace App\Services;

use Illuminate\Http\Request;

use App\Models\Item;
use App\Models\MrnDetail;
use App\Models\PRHeader;
use App\Models\PRDetail;


use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\InventoryHelperV2;

class PRCheckAndUpdateService
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
        $returnType = $inputData['return_type'];
        $inputQty = (float) $inputData['qty'] ?? 0.00;

        if (!$item) {
            return self::errorResponse("Item not found.", [
                'order_qty' => $inputQty
            ]);
        }

        // === Case 1: Edit (PR Detail exists) ===
        if (!empty($inputData['pr_item_id'])) {
            $prDetail = PRDetail::find($inputData['pr_item_id']);
            $mrnOrderQty = number_format((float) $prDetail?->mrnDetail?->accepted_qty ?? 0.00, 2);
            if (!$prDetail) {
                return self::errorResponse("PR Detail not found.", [
                    'order_qty' => $mrnOrderQty
                ]);
            }

            $mrnDetail = match ($type) {
                default => MrnDetail::find($inputData['mrn_detail_id'])
            };

            if ($mrnDetail) {
                $mrnQty = $mrnDetail->accepted_qty;
                $availableQty = floatval($mrnDetail->accepted_qty - $mrnDetail->pr_qty);
                if($returnType == 'rejected'){
                    $mrnQty = $mrnDetail->rejected_qty;
                    $availableQty = floatval($mrnDetail->rejected_qty - $mrnDetail->pr_rejected_qty);
                } else{
                    $mrnQty = $mrnDetail->accepted_qty;
                    $availableQty = floatval($mrnDetail->accepted_qty - $mrnDetail->pr_qty);
                }
                $inputDiff = $inputQty - floatval($prDetail->accepted_qty);

                if ($inputQty > $mrnQty) {
                    return self::errorResponse("Qty cannot be greater than MRN quantity.", [
                        'order_qty' => $mrnOrderQty
                    ]);
                }

                if ($availableQty < $inputDiff) {
                    return self::errorResponse("Only {$availableQty} qty can be added. {$mrnDetail->inspection_qty} already used; MRN qty is {$mrnDetail->order_qty}.", [
                        'order_qty' => $mrnOrderQty
                    ]);
                }
            }
        } // === Case 2: Create (Direct PR) ===
        else {
            // Step 1: Identify PR detail by reference type
            $mrnDetail = match ($type) {
                ConstantHelper::MRN_SERVICE_ALIAS => MrnDetail::find($inputData['mrn_detail_id']),
                default => null
            };

            if ($mrnDetail) {
                $prQty = $mrnDetail->pr_qty;
                $mrnQty = $mrnDetail->accepted_qty;
                if($returnType == 'rejected'){
                    $prQty = $mrnDetail->pr_rejected_qty;
                    $mrnQty = $mrnDetail->rejected_qty;
                } else{
                    $prQty = $mrnDetail->pr_qty;
                    $mrnQty = $mrnDetail->accepted_qty;
                }
                $prQty = floatval($prQty);
                $mrnQty = floatval($mrnQty);
                $totalQty = $inputQty + $prQty;
                if ($totalQty > $mrnQty) {
                    return self::errorResponse("PR qty cannot be greater than MRN qty.", [
                        'order_qty' => number_format($mrnQty, 2)
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