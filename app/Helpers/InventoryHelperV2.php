<?php
namespace App\Helpers;

use DB;
use Auth;

use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\ErpSubStoreParent;

use App\Models\Item;
use App\Models\Unit;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\ErpAttribute;
use App\Models\ItemAttribute;

use App\Models\StockLedger;
use App\Models\StockLedgerReservation;
use App\Models\StockLedgerStoragePoint;

use App\Models\WhLevel;
use App\Models\WhDetail;
use App\Models\WhStructure;
use App\Models\WhItemMapping;

use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;

use Illuminate\Support\Facades\Log;


class InventoryHelperV2
{
    public function __construct()
    {
    
    }

    // Get Storage POints
    public static function updateReceiptStock($documentHeader)
    {
        $user = Helper::getAuthenticatedUser();
        DB::beginTransaction();
        try {
            $documentDetails = $documentHeader->items->where('is_inspection', 1);
            foreach($documentDetails as $detail) {
                $approvedStockLedger = StockLedger::withDefaultGroupCompanyOrg()
                    ->where('document_header_id', $detail->mrn_header_id)
                    ->where('document_detail_id', $detail->id)
                    ->where('item_id', $detail->item_id)
                    ->where('store_id', $detail->store_id)
                    ->where('sub_store_id', $detail->sub_store_id)
                    ->where('transaction_type', 'receipt')
                    ->whereNull('utilized_id')
                    ->whereRaw('hold_qty > 0')
                    ->orderBy('document_date', 'ASC')
                    ->first();
                
                if($approvedStockLedger && ($approvedStockLedger->hold_qty > 0)) {
                    $approvedStockLedger->hold_qty = ($approvedStockLedger->hold_qty - $detail->inventory_uom_qty);
                    $approvedStockLedger->receipt_qty += $detail->inventory_uom_qty;
                    $approvedStockLedger->save();

                    $totalItemCost = $detail->basic_value - ($detail->discount_amount + $detail->header_discount_amount);
                    $costPerUnit = $totalItemCost/$approvedStockLedger->receipt_qty;
                    $approvedStockLedger->cost_per_unit = round(@$costPerUnit,6);
                    $approvedStockLedger->total_cost = round(@$totalItemCost, 2);
                    $approvedStockLedger->save();

                    self::updateStockCost($approvedStockLedger);
                }

                if($approvedStockLedger && ($approvedStockLedger->hold_qty == 0)) {
                    $approvedStockLedger->document_status = $documentHeader->document_status;
                    $approvedStockLedger->save();
                }
            } 
            
            \DB::commit();

            $message = "MRN details updated successfully.";
            $data = self::successResponse($message, array());
            return $data;
        } catch (\Exception $e) {
            dd($e);
            \DB::rollback();
            $errorMsg = "Error in InspectionHelper@updateMrnDetail: " . $e->getMessage();
            return self::errorResponse($errorMsg);
        }
        
    }

    // Update document status while update mrn
    private static function updateStockCost($stockLedger)
    {
        $user = Helper::getAuthenticatedUser();
        //costing exchange rate currency
        $orgnizationCurrencyCostPerUnit = $stockLedger->cost_per_unit*$stockLedger->org_currency_exg_rate;
        $orgnizationCurrencyCost = $stockLedger->total_cost*$stockLedger->org_currency_exg_rate;
        $companyCurrencyCostPerUnit = $orgnizationCurrencyCostPerUnit*$stockLedger->comp_currency_exg_rate;
        $companyCurrencyCost = $orgnizationCurrencyCost*$stockLedger->comp_currency_exg_rate;
        $groupCurrencyCostPerUnit = $companyCurrencyCostPerUnit*$stockLedger->group_currency_exg_rate;
        $groupCurrencyCost = $companyCurrencyCost*$stockLedger->group_currency_exg_rate;
        $stockLedger->org_currency_cost_per_unit = round($orgnizationCurrencyCostPerUnit,6);
        $stockLedger->org_currency_cost = round(@$orgnizationCurrencyCost,2);
        $stockLedger->comp_currency_cost_per_unit = round($companyCurrencyCostPerUnit,6);
        $stockLedger->comp_currency_cost = round($companyCurrencyCost,2);
        $stockLedger->group_currency_cost_per_unit = round($groupCurrencyCostPerUnit,6);
        $stockLedger->group_currency_cost = round($groupCurrencyCost,2);

        $stockLedger->save();

        return $stockLedger;
    }

    // Step 1: Check if stock is available (confirmed and unconfirmed)
    private static function checkStockAvailable($documentDetail)
    {
        $selectedAttr = $documentDetail['selectedAttr'] ?? [];
        $attributeGroups = Attribute::whereIn('id', $selectedAttr)->pluck('attribute_group_id')->values();

        $baseQuery = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id', $documentDetail['document_header_id'])
            ->where('document_detail_id', $documentDetail['document_detail_id'])
            ->where('item_id', $documentDetail['item_id'])
            ->where('store_id', $documentDetail['store_id'])
            ->where('sub_store_id', $documentDetail['sub_store_id'])
            ->where('transaction_type', $documentDetail['transaction_type'])
            ->where('book_type', $documentDetail['document_type'])
            ->whereNull('utilized_id')
            ->where(function($q) {
                $q->whereNull('hold_qty')->orWhere('hold_qty', '<=', 0);
            });

        // Apply attribute filters
        if ($attributeGroups->isNotEmpty() && !empty($selectedAttr)) {
            foreach ($attributeGroups as $index => $groupId) {
                if (isset($selectedAttr[$index])) {
                    $baseQuery->whereJsonContains('item_attributes', [
                        'attr_name' => (string)$groupId,
                        'attr_value' => (string)$selectedAttr[$index],
                    ]);
                }
            }
        }

        // Clone query to avoid re-execution conflict
        $confirmedStock = (clone $baseQuery)
            ->whereIn('document_status', ['approved', 'posted', 'approval_not_required'])
            ->first();

        $unConfirmedStock = (clone $baseQuery)
            ->whereNotIn('document_status', ['approved', 'posted', 'approval_not_required'])
            ->first();

        return [
            'confirmedStock' => $confirmedStock,
            'unConfirmedStock' => $unConfirmedStock,
        ];
    }

    public static function checkStockForDelete($documentDetail, $isDelete)
    {
        $availStock = self::checkStockAvailable($documentDetail);
        $confirmedStock = $availStock['confirmedStock'] ?? null;
        $unConfirmedStock = $availStock['unConfirmedStock'] ?? null;

        if (!$confirmedStock && $unConfirmedStock) {
            $unConfirmedStock->attributes()->delete();
            $unConfirmedStock->delete();

            return self::successResponse("Pending stock deleted successfully", [
                'stockLedger' => $documentDetail,
                'isDelete' => $isDelete ? 1 : 0,
            ]);
        }

        return self::errorResponse(
            "You cannot delete this {$documentDetail['document_type']} because stock is already available."
        );
    }

    public static function checkStockForUpdate($mrnItem)
    {
        $availStock = self::checkStockAvailable($mrnItem);
        $confirmedStock = $availStock['confirmedStock'] ?? null;
        if ($confirmedStock) {
            if ($confirmedStock->receipt_qty < $mrnItem['inventory_uom_qty']) {
                return self::successResponse("Available stock", [
                    'stockLedger' => $confirmedStock
                ]);
            }
            $actualQty =  ItemHelper::convertToAltUom($mrnItem['item_id'], $mrnItem['uom_id'], $confirmedStock->receipt_qty ?? 0);
            return self::errorResponse(
                "You cannot update this as available stock is only {$actualQty}."
            );
        }

        return self::successResponse("Available stock", [
            'stockLedger' => $mrnItem
        ]);
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

    private static function successResponse($response,$data)
    {
        return [
            "status" => "success",
            "code" => "200",
            "message" => $response,
            "data" => $data
        ];
    }

}
