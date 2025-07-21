<?php 
namespace App\Services;

use Illuminate\Http\Request;

use App\Models\GateEntryHeader;
use App\Models\GateEntryDetail;
use App\Models\GateEntryExtraAmount;

use App\Models\Item;
use App\Models\PoItem;
use App\Models\MrnDetail;
use App\Models\VendorAsnItem;
use App\Models\ErpSoJobWorkItem;
use App\Models\JobOrder\JoProduct;

use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\InventoryHelperV2;

class GeCheckAndUpdateService
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
        if (!empty($inputData['ge_detail_id'])) {
            $geDetail = GateEntryDetail::find($inputData['ge_detail_id']);
            $geOrderQty = number_format((float) $geDetail->accepted_qty ?? 0.00, 2);
            if (!$geDetail) {
                return self::errorResponse("Ge Item not found.", [
                    'order_qty' => $geOrderQty
                ]);
            }

            if ($geDetail->mrn_qty > $inputQty) {
                $mrnQty = number_format((float) $geDetail->mrn_qty, 2);
                return self::errorResponse("Order qty cannot be less than mrn quantity ({$mrnQty}) as it has already been used.", [
                    'order_qty' => $geOrderQty
                ]);
            }

            $poDetail = match ($type) {
                ConstantHelper::JO_SERVICE_ALIAS => JoProduct::find($inputData['jo_detail_id']),
                default => PoItem::find($inputData['po_detail_id'])
            };

            if ($poDetail) {
                $availableQty = floatval($poDetail->order_qty - $poDetail->ge_qty);
                $inputDiff = $inputQty - floatval($geDetail->order_qty);

                if ($inputQty > $poDetail->order_qty) {
                    return self::errorResponse("Order qty cannot be greater than PO quantity.", [
                        'order_qty' => $geOrderQty
                    ]);
                }

                if ($availableQty < $inputDiff) {
                    return self::errorResponse("Only {$availableQty} qty can be added. {$poDetail->ge_qty} already used; PO qty is {$poDetail->order_qty}.", [
                        'order_qty' => $geOrderQty
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
                    return self::errorResponse("Order qty cannot be greater than Gate Entry qty.", [
                        'order_qty' => number_format((float)$geDetail->accepted_qty, 2)
                    ]);
                }

                $geValidated = true;
            }

            // Step 3: ASN validation (if applicable)
            elseif (!empty($inputData['asn_detail_id'])) {
                $asnDetail = VendorAsnItem::find($inputData['asn_detail_id']);
                $balanceQty = floatval($asnDetail->supplied_qty - ($asnDetail->ge_qty ?? 0.00));

                if ($balanceQty < $inputQty) {
                    return self::errorResponse("Order qty cannot be greater than ASN qty.", [
                        'order_qty' => number_format((float)$asnDetail->supplied_qty, 2)
                    ]);
                }

                $asnValidated = true;
            }

            // Step 5: Tolerance check (if tolerance configured)
            if ($poDetail) {
                $grnQty = floatval($poDetail->ge_qty ?? 0);
                $orderQty = floatval($poDetail->order_qty ?? 0);
                $totalQty = $inputQty + $grnQty;

                // $positiveTol = floatval($item->po_positive_tolerance ?? 0);
                // $negativeTol = floatval($item->po_negative_tolerance ?? 0);

                // $maxAllowed = $orderQty + $positiveTol;
                // $minAllowed = max(0, $orderQty - $negativeTol);

                // if ($positiveTol > 0 || $negativeTol > 0) {
                //     if ($totalQty > $maxAllowed) {
                //         return self::errorResponse("Order qty exceeds allowed positive tolerance.", [
                //             'order_qty' => number_format($orderQty, 2)
                //         ]);
                //     }

                //     if ($totalQty < $minAllowed) {
                //         return self::errorResponse("Order qty is below allowed negative tolerance.", [
                //             'order_qty' => number_format($orderQty, 2)
                //         ]);
                //     }
                // } 
                if ($totalQty > $orderQty) {
                    return self::errorResponse("Order qty cannot be greater than po qty.", [
                        'order_qty' => number_format($orderQty, 2)
                    ]);
                }
            }
        }

        // === All Good ===
        return self::successResponse("Quantity Validated", [
            'order_qty' => $inputQty
        ]);
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