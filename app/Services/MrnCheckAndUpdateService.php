<?php
namespace App\Services;

use Illuminate\Http\Request;

use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\MrnExtraAmount;
use App\Models\MrnItemLocation;

use App\Models\Item;
use App\Models\PoItem;
use App\Models\VendorAsnItem;
use App\Models\GateEntryDetail;
use App\Models\ErpSoJobWorkItem;
use App\Models\JobOrder\JoProduct;

use App\Helpers\Helper;
use App\Helpers\TaxHelper;
use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\InventoryHelperV2;

class MrnCheckAndUpdateService
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
        if (!empty($inputData['mrn_detail_id'])) {
            $mrnDetail = MrnDetail::find($inputData['mrn_detail_id']);
            $mrnOrderQty = (float) $mrnDetail->order_qty ?? 0.00;
            if (!$mrnDetail) {
                return self::errorResponse("Mrn Item not found.", [
                    'order_qty' => $mrnOrderQty
                ]);
            }

            if ($mrnDetail->purchase_bill_qty > $inputQty) {
                $pbQty = (float) $mrnDetail->purchase_bill_qty ?? 0.00;
                return self::errorResponse("Order qty cannot be less than purchase bill quantity ({$pbQty}) as it has already been used.", [
                    'order_qty' => $mrnOrderQty
                ]);
            }

            $checkStock = self::checkConfirmedStock($mrnDetail, $inputQty);
            if ($checkStock['status'] === 'error') {
                return self::errorResponse($checkStock['message'], [
                    'order_qty' => $mrnOrderQty
                ]);
            }

            $poDetail = match ($type) {
                ConstantHelper::JO_SERVICE_ALIAS => JoProduct::find($inputData['jo_detail_id']),
                default => PoItem::find($inputData['po_detail_id'])
            };

            if ($poDetail) {
                $availableQty = floatval($poDetail->order_qty - $poDetail->grn_qty);
                $inputDiff = $inputQty - floatval($mrnDetail->order_qty);

                if ($inputQty > $poDetail->order_qty) {
                    return self::errorResponse("Order qty cannot be greater than PO quantity.", [
                        'order_qty' => $mrnOrderQty
                    ]);
                }

                if ($availableQty < $inputDiff) {
                    return self::errorResponse("Only {$availableQty} qty can be added. {$poDetail->grn_qty} already used; PO qty is {$poDetail->order_qty}.", [
                        'order_qty' => $mrnOrderQty
                    ]);
                }
            }
        }

        // === Case 2: Create (GE / ASN / Direct PO) ===
        else {
            // Step 1: Identify PO detail by reference type
            $poDetail = match ($type) {
                ConstantHelper::JO_SERVICE_ALIAS => JoProduct::find($inputData['jo_detail_id']),
                ConstantHelper::PO_SERVICE_ALIAS => PoItem::find($inputData['po_detail_id']),
                ConstantHelper::SO_SERVICE_ALIAS => ErpSoJobWorkItem::find($inputData['so_detail_id']),
                default => null
            };

            $geValidated = false;
            $asnValidated = false;

            // Step 2: Gate Entry validation (if applicable)
            if (!empty($inputData['ge_detail_id'])) {
                $geDetail = GateEntryDetail::find($inputData['ge_detail_id']);
                $balanceQty = floatval($geDetail->accepted_qty - ($geDetail->mrn_qty ?? 0.00));

                if ($balanceQty < $inputQty) {
                    // return self::errorResponse("Order qty cannot be greater than Gate Entry qty.", [
                    //     'order_qty' => (float) $geDetail->accepted_qty ?? 0.00
                    // ]);
                    $geValidated = false;
                }

            }

            // Step 3: ASN validation (if applicable)
            elseif (!empty($inputData['asn_detail_id'])) {
                $asnDetail = VendorAsnItem::find($inputData['asn_detail_id']);
                $balanceQty = floatval($asnDetail->supplied_qty - ($asnDetail->grn_qty ?? 0.00));

                if ($balanceQty < $inputQty) {
                    return self::errorResponse("Order qty cannot be greater than ASN qty.", [
                        'order_qty' => (float) $asnDetail->supplied_qty ?? 0.00
                    ]);
                }

                $asnValidated = true;
            }

            // // Step 4: Fallback check — If not ASN or GE validated, ensure PO exists and GRN < PO
            // if (!$geValidated && !$asnValidated && !empty($poDetail)) {
            //     if (($poDetail->order_qty - $poDetail->grn_qty) < $inputQty) {
            //         return self::errorResponse("Order qty exceeds PO quantity.", [
            //             'order_qty' => number_format((float) $poDetail->order_qty, 2)
            //         ]);
            //     }
            // }

            // Step 5: Tolerance check (if tolerance configured)
            if ($poDetail) {
                $grnQty = floatval($poDetail->grn_qty ?? 0);
                $orderQty = floatval($poDetail->order_qty ?? 0);
                $totalQty = $inputQty + $grnQty;

                $positiveTol = floatval($item->po_positive_tolerance ?? 0);
                $negativeTol = floatval($item->po_negative_tolerance ?? 0);

                $maxAllowed = $orderQty + $positiveTol;
                $minAllowed = max(0, $orderQty - $negativeTol);

                if ($positiveTol > 0 || $negativeTol > 0) {
                    if ($totalQty > $maxAllowed) {
                        return self::errorResponse("Order qty exceeds allowed positive tolerance.", [
                            'order_qty' => (float) $orderQty ?? 0.00
                        ]);
                    }

                    if ($totalQty < $minAllowed) {
                        return self::errorResponse("Order qty is below allowed negative tolerance.", [
                            'order_qty' => (float) $orderQty ?? 0.00
                        ]);
                    }
                } elseif ($totalQty > $orderQty) {
                    return self::errorResponse("Order qty cannot be greater than po qty.", [
                        'order_qty' => (float) $orderQty ?? 0.00
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
