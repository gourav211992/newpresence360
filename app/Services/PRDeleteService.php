<?php
namespace App\Services;

use DB;
use Exception;

use App\Models\PRTed;
use App\Models\PRDetail;
use App\Models\MrnDetail;

use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelperV2;

class PRDeleteService
{
    public function deleteByRequest(array $deletedData, $pr)
    {
        try{
            // Delete header-item-level TEDs
            PRTed::whereIn('id', $deletedData['deletedHeaderExpTedIds'] ?? [])->delete();
            PRTed::whereIn('id', $deletedData['deletedHeaderDiscTedIds'] ?? [])->delete();
            PRTed::whereIn('id', $deletedData['deletedItemDiscTedIds'] ?? [])->delete();

            // Delete MRN items
            if (!empty($deletedData['deletedPRItemIds'])) {
                $prItems = PRDetail::whereIn('id', $deletedData['deletedPRItemIds'])->get();
                foreach ($prItems as $prItem) {
                    $orderQty = (float) $prItem->accepted_qty;
                    if ($pr->mrn_header_id) {
                        if ($mrnItem = $prItem->mrnDetail) {
                            if($pr->qty_return_type == 'accepted'){
                                $mrnItem->pr_qty -= $orderQty;
                            } else{
                                $mrnItem->pr_rejected_qty -= $orderQty;
                            }
                            $mrnItem->save();
                        }
                    }
                    // Check Stock and delete
                    $documentHeaderId = $prItem->header_id;
                    $documentDetailId = $prItem->id;
                    $itemId = $prItem->item_id;
                    $storeId = $pr->store_id;
                    $subStoreId = $pr->sub_store_id;
                    $documentStatus = $pr->document_status;
                    $selectedAttr = collect($prItem->attributes)->pluck('attr_value')->filter()->values()->toArray();
                    $prData = [
                        'document_header_id' => $documentHeaderId,
                        'document_detail_id' => $documentDetailId,
                        'item_id' => $itemId,
                        'store_id' => $storeId,
                        'document_type' => 'purchase-return',
                        'attributes' => $selectedAttr,
                        'sub_store_id' => $subStoreId,
                        'transaction_type' => 'issue',
                        'document_status' => $documentStatus,
                        // 'book_type' => $pr->book_code,
                    ];
                    $checkStockAvailable = InventoryHelperV2::checkStockForIssueDelete($prData, 'true');
                    if ($checkStockAvailable['status'] === 'error') {
                        $data = self::errorResponse($checkStockAvailable['message']);
                        return $data;
                    }
                    $prItem->pbTed()->delete();
                    $prItem->attributes()->delete();
                    $prItem->delete();
                }
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }

        $data = self::successResponse($response = "PR Items deleted successfully.");
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
