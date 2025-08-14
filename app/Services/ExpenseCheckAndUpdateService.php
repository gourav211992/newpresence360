<?php
namespace App\Services;

use Illuminate\Http\Request;

use App\Models\Item;
use App\Models\MrnDetail;
use App\Models\InspectionHeader;
use App\Models\InspectionDetail;


use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\InventoryHelperV2;
use App\Models\ExpenseDetail;
use App\Models\JobOrder\JoProduct;
use App\Models\PoItem;

class ExpenseCheckAndUpdateService
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
                'accepted_qty' => $inputQty
            ]);
        }

        // === Case 1: Edit (MRN Detail exists) ===
        if (!empty($inputData['expense_item_id'])) {
            $expDetail = ExpenseDetail::find($inputData['expense_item_id']);
            $poOrderQty = number_format((float) $expDetail->accepted_qty ?? 0.00, 2);
            if (!$expDetail) {
                return self::errorResponse("Expense Item not found.", [
                    'accepted_qty' => $poOrderQty
                ]);
            }

            $poDetail = match ($type) {
                ConstantHelper::JO_SERVICE_ALIAS => JoProduct::find($inputData['jo_detail_id']),
                default => PoItem::find($inputData['po_detail_id'])
            };

            if ($poDetail) {
                $availableQty = floatval($poDetail->order_qty - $poDetail->expense_advise_qty);
                $inputDiff = $inputQty - floatval($expDetail->accepted_qty);
                if ($inputQty > $poDetail->order_qty) {
                    return self::errorResponse("Order qty cannot be greater than Po quantity.", [
                        'accepted_qty' => $poOrderQty
                    ]);
                }
                if ($availableQty < $inputDiff) {
                    return self::errorResponse("Only {$availableQty} qty can be added. {$poDetail->expense_advise_qty} already used; Po qty is {$poDetail->order_qty}.", [
                        'accepted_qty' => $poOrderQty
                    ]);
                }
            }
        } // === Case 2: Create (Direct MRN) ===
        else {
            // Step 1: Identify MRN detail by reference type
            $expenseDetail = match ($type) {
                ConstantHelper::JO_SERVICE_ALIAS => JoProduct::find($inputData['jo_detail_id']),
                ConstantHelper::PO_SERVICE_ALIAS => PoItem::find($inputData['po_detail_id']),
                default => null
            };

            if ($expenseDetail) {
                $expenseQty = floatval($expenseDetail->expense_advise_qty ?? 0);
                $orderQty = floatval($expenseDetail->order_qty ?? 0);
                $totalQty = $inputQty + $expenseQty;

                if ($totalQty > $orderQty) {
                    return self::errorResponse("Expense qty cannot be greater than PO qty.", [
                        'accepted_qty' => number_format($orderQty, 2)
                    ]);
                }
            }
        }

        // === All Good ===
        return self::successResponse("Quantity Validated", [
            'accepted_qty' => $inputQty
        ]);
    }


    private static function errorResponse($message, $inputQty)
    {
        return [
            "code" => "500",
            "status" => "error",
            "accepted_qty" => $inputQty,
            "message" => $message,
        ];

    }

    private static function successResponse($response, $inputQty)
    {
        return [
            "code" => "200",
            "status" => "success",
            "accepted_qty" => $inputQty,
            "message" => $response,
        ];
    }
}
