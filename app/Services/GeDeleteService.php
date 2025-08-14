<?php
namespace App\Services;

use App\Models\MrnDetail;
use App\Models\MrnItemLocation;

use App\Helpers\ConstantHelper;
use App\Models\GateEntryDetail;
use App\Models\GateEntryItemLocation;
use App\Models\GateEntryTed;

class GeDeleteService
{
    public function deleteByRequest(array $deletedData, $mrn)
    {
        // Delete header-level TEDs
        // GateEntryTed::whereIn('id', $deletedData['deletedHeaderExpTedIds'] ?? [])->delete();
        // GateEntryTed::whereIn('id', $deletedData['deletedHeaderDiscTedIds'] ?? [])->delete();
        // GateEntryTed::whereIn('id', $deletedData['deletedItemDiscTedIds'] ?? [])->delete();

        // // Delete item location
        // GateEntryItemLocation::whereIn('id', $deletedData['deletedItemLocationIds'] ?? [])->delete();

        // Delete MRN items
        if (!empty($deletedData['deletedMrnItemIds'])) {
            $mrnItems = GateEntryDetail::whereIn('id', $deletedData['deletedMrnItemIds'])->get();

            foreach ($mrnItems as $mrnItem) {
                $itemName = $mrnItem->item->item_name;
                if ($mrnItem->mrn_qty > 0) {
                    $errorMessage = "$itemName has been used in MRN so cannot be deleted from this Gate Entry.";
                    $data = self::errorResponse($errorMessage);
                    return $data;
                }

                $orderQty = $mrnItem->accepted_qty;

                $mrnItem->extraAmounts()->delete();
                $mrnItem->attributes()->delete();

                if ($asnItem = $mrnItem->asnItem) {
                    $asnItem->ge_qty -= $orderQty;
                    $asnItem->save();
                }
                
                switch ($mrn->reference_type) {
                    case ConstantHelper::JO_SERVICE_ALIAS:
                        if ($joItem = $mrnItem->joItem) {
                            $joItem->ge_qty -= $orderQty;
                            $joItem->save();
                        }
                        break;
                
                    case ConstantHelper::SO_SERVICE_ALIAS:
                        if ($soItem = $mrnItem->soItem) {
                            $soItem->ge_qty -= $orderQty;
                            $soItem->save();
                        }
                        break;
                
                    case ConstantHelper::PO_SERVICE_ALIAS:
                        if ($poItem = $mrnItem->poItem) {
                            $poItem->ge_qty -= $orderQty;
                            $poItem->save();
                        }
                        break;
                }

                $mrnItem->delete();
            }
        }

        $data = self::successResponse($response = "Gate Entry deleted successfully.");
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
