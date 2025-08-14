<?php
namespace App\Services;


use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelperV2;
use App\Models\ErpPslipItem;
use App\Models\ErpPslipItemDetail;
use App\Models\ErpPslipItemLocation;
use App\Models\ErpSoItem;
use App\Models\MoItem;
use App\Models\PslipBomConsumption;
use App\Models\PwoSoMapping;
use App\Models\PwoStationConsumption;

class PslipDeleteService
{
    public function deleteByRequest(array $deletedData, $productionSlip)
    {
        if (count($deletedData['deletedSiItemIds'])) {
            $psItems = ErpPslipItem::whereIn('id',$deletedData['deletedSiItemIds'])->get();
            # all ted remove item level
            foreach($psItems as $psItem) {
                
                $pslipBomMappings = PslipBomConsumption::where('pslip_id', $productionSlip?->id)
                        ->where('pslip_item_id', $psItem?->id)
                        ->get();
                $pslipBomIds = [];
                foreach($pslipBomMappings as $pslipBomMapping) {
                    $pslipBomIds[] = $pslipBomMapping->id;
                    $moProductAttributes = $pslipBomMapping->attributes ?? [];
                    $moItem = MoItem::where('mo_id',$psItem?->mo_product?->mo_id)
                                    ->when($psItem->so_id, function ($query) use ($psItem) {
                                        $query->where('so_id', $psItem->so_id);
                                    })
                                    ->where('item_id', $pslipBomMapping?->item_id)
                                    ->when(count($moProductAttributes), function ($query) use ($moProductAttributes) {
                                        $query->whereHas('attributes', function ($piAttributeQuery) use ($moProductAttributes) {
                                            $piAttributeQuery->where(function ($subQuery) use ($moProductAttributes) {
                                                foreach ($moProductAttributes as $poAttribute) {
                                                    $subQuery->orWhere(function ($q) use ($poAttribute) {
                                                        $q->where('item_attribute_id', $poAttribute['item_attribute_id'] ?? $poAttribute['attribute_id'])
                                                            ->where('attribute_value', $poAttribute['attribute_value']);
                                                    });
                                                }
                                            });
                                        }, '=', count($moProductAttributes));
                                    })
                                    ->first();
                    if($moItem) {
                        $moItem->consumed_qty = $moItem->consumed_qty - $pslipBomMapping->consumption_qty;
                        $moItem->save();
                    }

                    # issue case
                    $selectedAttr = collect($pslipBomMapping->attributes)->pluck('attribute_value')->filter()->values()->toArray();
                    $pslipData = [
                        'document_header_id' => $productionSlip -> id,
                        'document_detail_id' => $pslipBomMapping->id,
                        'item_id' => $pslipBomMapping->item_id,
                        'store_id' => $psItem->store_id,
                        'document_type' => ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS,
                        'attributes' => $selectedAttr,
                        'sub_store_id' => $psItem->sub_store_id,
                        'transaction_type' => 'issue',
                        'document_status' => $productionSlip->document_status,
                        'book_type' => ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS,
                    ];
                    $checkStockAvailable = InventoryHelperV2::checkStockForIssueDelete($pslipData, 'true');
                    if ($checkStockAvailable['status'] === 'error') {
                        $data = self::errorResponse($checkStockAvailable['message']);
                        return $data;
                    }
                }

                # Receipt case
                $pslipItemData = [
                    'document_header_id' => $productionSlip->id,
                    'document_detail_id' => $psItem->id,
                    'item_id' => $psItem->item_id,
                    'store_id' => $psItem->store_id,
                    'document_type' => ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS,
                    'attributes' => $selectedAttr,
                    'sub_store_id' => $psItem->sub_store_id,
                    'transaction_type' => 'receipt',
                    'document_status' => $productionSlip->document_status,
                ];
                $checkStockAvailable = InventoryHelperV2::checkStockForDelete($pslipItemData, 'true');
                if ($checkStockAvailable['status'] === 'error') {
                    $data = self::errorResponse($checkStockAvailable['message']);
                    return $data;
                }

                //Back update in MO PRODUCT
                if($psItem?->mo_product) {
                    $psItem->mo_product->pslip_qty = $psItem->mo_product->pslip_qty - ($psItem->accepted_qty + $psItem->subprime_qty);
                    $psItem->mo_product->save();
                }
                //Back update in MO
                $pwoStation = PwoStationConsumption::where('pwo_mapping_id',$psItem->mo_product?->pwoMapping?->id)
                                    ->where('mo_id',$psItem->mo_product->mo_id)
                                    ->where('station_id',$psItem->mo_product?->mo?->station_id)
                                    ->first();
                if($pwoStation) {
                    $pwoStation->pslip_qty = $pwoStation->pslip_qty - ($psItem->accepted_qty + $psItem->subprime_qty);
                    $pwoStation->save();
                }

                if($psItem->mo_product?->mo?->is_last_station && in_array($productionSlip->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED) && $actionType == 'amendment') {
                    $soItem = ErpSoItem::find($psItem -> so_item_id);
                    if (isset($soItem)) {
                        $soItem -> pslip_qty = $soItem -> pslip_qty - ($psItem -> accepted_qty + $psItem -> subprime_qty);
                        $soItem -> save();
                    }
                    //Update in mapping table 
                    $pwoSoMappingItem = PwoSoMapping::where('id', $psItem->mo_product->pwo_mapping_id) -> first();
                    if (isset($pwoSoMappingItem)) {
                        $pwoSoMappingItem -> pslip_qty = $pwoSoMappingItem -> pslip_qty - ($psItem -> accepted_qty + $psItem -> subprime_qty);
                        $pwoSoMappingItem -> save();
                    }
                }
                
                // # stock ledger remove data for issue
                // $stockLedgersA = StockLedger::where('document_header_id', $productionSlip -> id)
                //     ->whereIn('document_detail_id', $pslipBomIds)
                //     ->where('book_type', ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS)
                //     ->get();
                // foreach($stockLedgersA as $stockLedger) {
                //     $stockLedger->attributes()->delete();
                //     $stockLedger->reservations()->delete();
                //     $stockLedger->details()->delete();
                //     $stockLedger->delete();
                // }
                // # stock ledger remove data for receipt
                // $stockLedgersB = StockLedger::where('document_header_id', $productionSlip -> id)
                //     ->where('document_detail_id', $psItem -> id)
                //     ->where('book_type', ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS)
                //     ->get();
                // foreach($stockLedgersB as $stockLedger) {
                //     $stockLedger->attributes()->delete();
                //     $stockLedger->reservations()->delete();
                //     $stockLedger->details()->delete();
                //     $stockLedger->delete();
                // }
                
                ErpPslipItemLocation::where('pslip_item_id', $psItem -> id)
                    ->delete();
                PslipBomConsumption::where("pslip_id", $productionSlip -> id)
                    ->where("pslip_item_id", $psItem -> id)
                    ->delete();
                ErpPslipItemDetail::where('pslip_item_id', $psItem -> id)
                        ->delete();
                # all attr remove
                $psItem->attributes()->delete();
                $psItem->delete();
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
