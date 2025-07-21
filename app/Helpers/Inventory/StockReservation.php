<?php
namespace App\Helpers\Inventory;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Models\StockLedger;
use App\Models\StockLedgerReservation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class StockReservation
{
    public static function stockReservation(string $bookType, int $headerId, Collection $items) : array
    {
        $totalRequestedQty = 0;
        foreach ($items as $item) {
            $prepareDataForStock = self::prepareDataForStock($item, $bookType);
            //Retrieve stocks for each item
            $stockLedger = InventoryHelper::totalInventoryAndStock($prepareDataForStock['item_id'],$prepareDataForStock['selected_attributes'],
                $prepareDataForStock['uom_id'], $prepareDataForStock['store_id'], $prepareDataForStock['sub_store_id'], $prepareDataForStock['order_id'], 
                $prepareDataForStock['station_id'], $prepareDataForStock['stock_type'], $prepareDataForStock['wip_station_id']);
            //Increment Total requested qty
            $totalRequestedQty += $prepareDataForStock['requested_qty']; 
            //Check if stocks are availble for the requested qty
            if ($stockLedger['confirmedStocks'] < $prepareDataForStock['requested_qty']) {
                return [
                    'status' => 'error',
                    'message' => 'Enough Stock not available'
                ];
            }
            //Reserve the stocks
            foreach ($stockLedger['stockLedgers'] as $stockLedger) {
                $stkLdgr = StockLedger::find($stockLedger -> id);
                self::reserveStock($bookType, $headerId, $item -> id, $prepareDataForStock['requested_qty'], $stkLdgr);
            }
        }
        //Final success message
        return ['status'=> 'success','message'=> 'Stock Reserved successfully'];
    }

    public static function reserveStock(string $bookType, int $headerId, int $detailId, $qty, StockLedger $stockLedger) 
    {
        StockLedgerReservation::create([
            'issue_header_id' => $headerId,
            'receipt_header_id' => $stockLedger -> document_header_id,
            'issue_detail_id' => $detailId,
            'receipt_detail_id' => $stockLedger -> document_detail_id,
            'issue_book_type' => $bookType,
            'receipt_book_type' => $stockLedger -> book_type,
            'stock_ledger_id' => $stockLedger -> id,
            'quantity' => $qty
        ]);
        $stockLedger -> reserved_qty += $qty;
        $stockLedger -> save(); 
    }

    //Function to prepare data according to specified module
    private static function prepareDataForStock(Model $item, string $bookType) : array
    {
        //Default setup
        $data = [
            'item_id' => $item ?-> item_id,
            'selected_attributes' => [],
            'uom_id' => $item ?-> uom_id,
            'store_id' => $item ?-> store_id,
            'sub_store_id' => $item ?-> sub_store_id,
            'order_id' => null,
            'station_id' => null,
            'stock_type' => InventoryHelper::STOCK_TYPE_REGULAR,
            'wip_station_id' => null,
            'requested_qty' => isset($item -> qty) ? $item -> qty : 0
        ];
        //Override if required
        if ($bookType === ConstantHelper::PL_SERVICE_ALIAS) {
            $attributes = $item -> attributes;
            $selectedAttributes = [];
            foreach ($attributes as $attribute) { 
                array_push($selectedAttributes, $attribute['attribute_value_id']);
            }
            $data['selected_attributes'] = $selectedAttributes;
            $data['uom_id'] = $item -> inventory_uom_id;
            $data['sub_store_id'] = $item ?-> header -> main_sub_store_id;
            $data['store_id'] = $item ?-> header -> store_id;
            $data['requested_qty'] = $item -> inventory_uom_qty;
        }
        return $data;
    }

    public static function validateReservedStock(string $bookType, int $headerId, int $detailId, float $qty) : array
    {
        $reservedStock = StockLedgerReservation::where('issue_book_type', $bookType) -> where('issue_header_id', $headerId) -> where('issue_detail_id', $detailId) -> first();
        if (!isset($reservedStock)) {
            return ['status' => 'error', 'message' => 'Reserved stock not available'];
        }
        if ($reservedStock -> quantity < $qty) {
            return ['status' => 'error', 'message' => 'Enough Stock not reserved'];
        }
        return ['status' => 'success', 'message' => 'Reserved Stock found'];
    }

}
