<?php
namespace App\Services;

use DB;
use Exception;

use App\Models\PbDetail;
use App\Models\PbTed;

class PBDeleteService
{
    public function deleteByRequest(array $deletedData, $pb)
    {
        try{
            // Delete header-item-level TEDs
            PbTed::whereIn('id', $deletedData['deletedHeaderExpTedIds'] ?? [])->delete();
            PbTed::whereIn('id', $deletedData['deletedHeaderDiscTedIds'] ?? [])->delete();
            PbTed::whereIn('id', $deletedData['deletedItemDiscTedIds'] ?? [])->delete();

            // Delete MRN items
            if (!empty($deletedData['deletedMrnItemIds'])) {
                $pbItems = PbDetail::whereIn('id', $deletedData['deletedMrnItemIds'])->get();
                foreach ($pbItems as $pbItem) {
                    $orderQty = (float) $pbItem->accepted_qty;
                    if ($pb->mrn_header_id) {
                        if ($mrnItem = $pbItem->mrnDetail) {
                            // if($pb->qty_return_type == 'accepted'){
                            $mrnItem->purchase_bill_qty -= $orderQty;
                            // } else{
                            //     $mrnItem->pb_rejected_qty -= $orderQty;
                            // }
                            $mrnItem->save();
                        }
                    }
                    $pbItem->pbTed()->delete();
                    $pbItem->attributes()->delete();
                    $pbItem->delete();
                }
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }

        $data = self::successResponse($response = "PB Items deleted successfully.");
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
