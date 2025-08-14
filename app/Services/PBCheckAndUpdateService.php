<?php
namespace App\Services;

use Illuminate\Http\Request;

use App\Models\Item;
use App\Models\MrnDetail;

use App\Helpers\ConstantHelper;
use App\Models\PbDetail;

class PBCheckAndUpdateService
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
        if (!empty($inputData['detail_id'])) {
            $pbDetail = PbDetail::find($inputData['detail_id']);
            $mrnOrderQty = (float) $pbDetail->accepted_qty ?? 0.00;
            if (!$pbDetail) {
                return self::errorResponse("Purchase Bill not found.", [
                    'order_qty' => $mrnOrderQty
                ]);
            }

            $mrnDetail = match ($type) {
                default => MrnDetail::find($inputData['mrn_detail_id'])
            };

            if ($mrnDetail) {
                $availableQty = floatval($mrnDetail->order_qty - $mrnDetail->purchase_bill_qty);
                $inputDiff = $inputQty - floatval($pbDetail->accepted_qty);

                if ($inputQty > $mrnDetail->order_qty) {
                    return self::errorResponse("Order qty cannot be greater than MRN quantity.", [
                        'order_qty' => $mrnOrderQty
                    ]);
                }

                if ($availableQty < $inputDiff) {
                    return self::errorResponse("Only {$availableQty} qty can be added. {$mrnDetail->purchase_bill_qty} already used; MRN qty is {$mrnDetail->order_qty}.", [
                        'order_qty' => $mrnOrderQty
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
                $inspectedQty = floatval($mrnDetail->purchase_bill_qty ?? 0);
                $orderQty = floatval($mrnDetail->order_qty ?? 0);
                $totalQty = $inputQty + $inspectedQty;

                if ($totalQty > $orderQty) {
                    return self::errorResponse("PB qty cannot be greater than MRN qty.", [
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
