<?php 
namespace App\Services;

use App\Models\MrnDetail;
use App\Models\MrnExtraAmount;
use App\Models\MrnItemLocation;

use App\Helpers\ConstantHelper;

class MrnDeleteService
{
    public function deleteByRequest(array $deletedData, $mrn)
    {
        // Delete header-level TEDs
        MrnExtraAmount::whereIn('id', $deletedData['deletedHeaderExpTedIds'] ?? [])->delete();
        MrnExtraAmount::whereIn('id', $deletedData['deletedHeaderDiscTedIds'] ?? [])->delete();
        MrnExtraAmount::whereIn('id', $deletedData['deletedItemDiscTedIds'] ?? [])->delete();

        // Delete item location
        MrnItemLocation::whereIn('id', $deletedData['deletedItemLocationIds'] ?? [])->delete();

        // Delete MRN items
        if (!empty($deletedData['deletedMrnItemIds'])) {
            $mrnItems = MrnDetail::whereIn('id', $deletedData['deletedMrnItemIds'])->get();

            foreach ($mrnItems as $mrnItem) {
                if ($mrnItem->purchase_bill_qty > 0 || $mrnItem->pr_qty > 0) {
                    $errorMessage = "Cannot delete MRN item with purchase bill or PR quantity.";
                    $data = self::errorResponse($errorMessage);
                    return $data;
                }

                $orderQty = $mrnItem->order_qty;

                $mrnItem->teds()->delete();
                $mrnItem->attributes()->delete();

                if ($geItem = $mrnItem->geItem) {
                    $geItem->update(['mrn_qty' => $geItem->accepted_qty - $orderQty]);
                }

                if ($asnItem = $mrnItem->asnItem) {
                    $asnItem->update(['grn_qty' => $asnItem->supplied_qty - $orderQty]);
                }

                switch ($mrn->reference_type) {
                    case ConstantHelper::JO_SERVICE_ALIAS:
                        if ($joItem = $mrnItem->joItem) {
                            $joItem->update(['grn_qty' => $joItem->order_qty - $orderQty]);
                        }
                        break;

                    case ConstantHelper::SO_SERVICE_ALIAS:
                        if ($soItem = $mrnItem->soItem) {
                            $soItem->update(['grn_qty' => $soItem->qty - $orderQty]);
                        }
                        break;

                    case ConstantHelper::PO_SERVICE_ALIAS:
                        if ($poItem = $mrnItem->poItem) {
                            $poItem->update(['grn_qty' => $poItem->order_qty - $orderQty]);
                        }
                        break;
                }

                $mrnItem->delete();
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