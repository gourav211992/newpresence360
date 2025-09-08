<?php

namespace App\Services\Scrap;

use App\Models\ErpPslipItem;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelperV2;
use App\Models\Scrap\ErpScrapItem;
use App\Models\ErpPslipItemDetail;
use App\Models\ErpPslipItemLocation;
use Illuminate\Support\Facades\DB;

class ScrapDeleteService
{
    /**
     * Delete entire scrap header with dependencies
     */
    public function deleteScrapHeader($scrap)
    {
        if (empty($scrap)) {
            return SELF::errorResponse("No Scrap Header found to delete.");
        }

        if ($scrap->document_status !== ConstantHelper::DRAFT) {
            return SELF::errorResponse("Can not delete, document status is not draft.");
        }

        try {
            // validate each scrap item
            foreach ($scrap->items as $item) {
                $check = SELF::validateAndDeleteScrapItem($item, $scrap, false);
                if ($check !== true) {
                    return $check;
                }
            }

            $scrap->pslipItems()->update(['erp_scrap_id' => null]);

            $scrap->dynamicFields()->delete();
            $scrap->media()->delete();
            $scrap->clearExistingDocuments('bom');
            $scrap->delete();

            return SELF::successResponse("Scrap header and all dependencies deleted successfully.");
        } catch (\Exception $e) {
            return SELF::errorResponse("Error deleting scrap header: " . $e->getMessage());
        }
    }

    /**
     * Delete selected scrap items
     */
    public function deleteScrapItems(array $scrapIds, $scrap)
    {
        if (empty($scrapIds)) {
            return SELF::errorResponse("No scrap items found to delete.");
        }

        if ($scrap->document_status !== ConstantHelper::DRAFT) {
            return SELF::errorResponse("Can not delete items, document status is not draft.");
        }

        try {
            $items = ErpScrapItem::whereIn('id', $scrapIds)
                ->where('erp_scrap_id', $scrap->id)
                ->get();

            foreach ($items as $scrapItem) {
                $check = SELF::validateAndDeleteScrapItem($scrapItem, $scrap, true);
                if ($check !== true) {
                    return $check;
                }
            }

            return SELF::successResponse("Scrap items deleted successfully.");
        } catch (\Exception $e) {
            return SELF::errorResponse("Error deleting scrap items: " . $e->getMessage());
        }
    }

    /**
     * Delete selected scrap items
     */
    public function deleteAttachments(array $mediaIds, $scrap)
    {
        if (empty($mediaIds)) {
            return SELF::errorResponse("No scrap items found to delete.");
        }

        if ($scrap->document_status !== ConstantHelper::DRAFT) {
            return SELF::errorResponse("Can not delete media, document status is not draft.");
        }

        try {

            if (!empty($deletedData['deletedAttachmentIds'])) {
                $medias = ErpScarpMedia::whereIn('id', $mediaIds)->get();
                foreach ($medias as $media) {
                    if ($scrap->document_status == ConstantHelper::DRAFT) {
                        Storage::delete($media->file_name);
                        $media->delete();
                    }
                }
            }

            return SELF::successResponse("Scrap items deleted successfully.");
        } catch (\Exception $e) {
            return SELF::errorResponse("Error deleting scrap items: " . $e->getMessage());
        }
    }

    /**
     * Remove PS item mapping from scrap
     */
    public function removePsMapping(array $pslipItemIds, $scrap)
    {
        if (empty($pslipItemIds)) {
            return SELF::errorResponse("No production slip items found to delete.");
        }

        if ($scrap->document_status !== ConstantHelper::DRAFT) {
            return SELF::errorResponse("Can not remove production slip item, document status is not draft.");
        }

        try {
            $psItems = ErpPslipItem::whereIn('id', $pslipItemIds)->where('erp_scrap_id', $scrap->id)->get();
            foreach ($psItems as $psItem) {
                SELF::unmapPsItem($psItem);
            }

            return SELF::successResponse("Production slip items unmapped from scrap successfully.");
        } catch (\Exception $e) {
            return SELF::errorResponse("Error removing PS item mappings: " . $e->getMessage());
        }
    }

    private function validateAndDeleteScrapItem($scrapItem, $scrap, $delete = true)
    {
        $selectedAttr = $scrapItem->attributes()->pluck('attribute_value')->filter()->all() ?? [];
        $check = SELF::checkReceiptStock($scrapItem, $scrap, $selectedAttr);
        if ($check !== true) {
            return $check;
        }

        if ($delete) {
            $scrapItem->attributes()->delete();
            $scrapItem->delete();
        }

        return true;
    }

    private function unmapPsItem($psItem)
    {
        $psItem->update(['erp_scrap_id' => null]);
    }

    private function checkReceiptStock($scrapItem, $scrap, array $selectedAttr)
    {
        $scrapItemData = [
            'document_header_id' => $scrap->id,
            'document_detail_id' => $scrapItem->id,
            'item_id'            => $scrapItem->item_id,
            'store_id'           => $scrap->store_id,
            'sub_store_id'       => $scrap->sub_store_id,
            'attributes'         => $selectedAttr,
            'transaction_type'   => 'receipt',
            'document_status'    => $scrap->document_status,
            'document_type'      => ConstantHelper::SCRAP_SERVICE_ALIAS,
        ];

        $check = InventoryHelperV2::checkStockForDelete($scrapItemData, true);

        return $check['status'] === 'error' ? SELF::errorResponse($check['message']) : true;
    }

    /**
     * Standardized error response
     */
    private static function errorResponse($message)
    {
        return [
            "status" => "error",
            "code"   => 500,
            "message" => $message,
            "data"   => null,
        ];
    }

    /**
     * Standardized success response
     */
    private static function successResponse($response)
    {
        return [
            "status" => "success",
            "code"   => 200,
            "message" => $response,
        ];
    }
}
