<?php
namespace App\Services;

use App\Models\MrnDetail;
use App\Models\MrnItemLocation;

use App\Helpers\ConstantHelper;
use App\Models\ExpenseDetail;
use App\Models\GateEntryDetail;
use App\Models\GateEntryItemLocation;
use App\Models\GateEntryTed;
use Exception;

class ExpenseDeleteService
{
    public function deleteByRequest(array $deletedData, $mrn)
    {
        try{
            // Delete header-level TEDs
            // GateEntryTed::whereIn('id', $deletedData['deletedHeaderExpTedIds'] ?? [])->delete();
            // GateEntryTed::whereIn('id', $deletedData['deletedHeaderDiscTedIds'] ?? [])->delete();
            // GateEntryTed::whereIn('id', $deletedData['deletedItemDiscTedIds'] ?? [])->delete();
            // Delete MRN items
            if (!empty($deletedData['deletedMrnItemIds'])) {
                $mrnItems = ExpenseDetail::whereIn('id', $deletedData['deletedMrnItemIds'])->get();

                foreach ($mrnItems as $mrnItem) {
                $orderQty = $mrnItem->accepted_qty;

                $mrnItem->extraAmounts()->delete();
                $mrnItem->attributes()->delete();

                switch ($mrn->reference_type) {
                    case ConstantHelper::JO_SERVICE_ALIAS:
                        if ($joItem = $mrnItem->joItem) {
                            $joItem->expense_advise_qty -= $orderQty;
                            $joItem->save();
                        }
                        break;

                    case ConstantHelper::PO_SERVICE_ALIAS:
                        if ($poItem = $mrnItem->poItem) {
                            $poItem->expense_advise_qty -= $orderQty;
                            $poItem->save();
                        }
                        break;
                }

                $mrnItem->delete();
            }
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(). ' on line ' . $e->getLine(),
            ], 500);
        }

        $data = self::successResponse($response = "Expense Advise deleted successfully.");
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
