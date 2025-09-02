<?php
namespace App\Helpers\Inventory;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Models\StockLedger;
use App\Models\StockLedgerReservation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class StockReservation
{
    public static function stockReservation(string $bookType, int $headerId, Collection $items) : array
    {
        foreach ($items as $item) {
            $prepareDataForStock = self::prepareDataForStock($item, $bookType);
            //Retrieve stocks for each item
            $stockLedger = InventoryHelper::totalInventoryAndStock($prepareDataForStock['item_id'],$prepareDataForStock['selected_attributes'],
                $prepareDataForStock['uom_id'], $prepareDataForStock['store_id'], $prepareDataForStock['sub_store_id'], $prepareDataForStock['order_id'], 
                $prepareDataForStock['station_id'], $prepareDataForStock['stock_type'], $prepareDataForStock['wip_station_id']);
            //Check if stocks are availble for the requested qty
            if ($stockLedger['confirmedStocks'] < $prepareDataForStock['requested_qty']) {
                return [
                    'status' => 'error',
                    'message' => 'Enough Stock not available'
                ];
            }
            //Reserve the stocks
            $totalQtyToBeReserved = (float)$prepareDataForStock['requested_qty'];
            $balanceQty = $totalQtyToBeReserved;
            $reservedQty = 0;
            foreach ($stockLedger['stockLedgers'] as $stockLedger) {
                $stkLdgr = StockLedger::find($stockLedger -> id);
                $stockLedgerQty = (float)$stkLdgr -> receipt_qty - (float)$stkLdgr -> reserved_qty;
                if($stockLedgerQty > 0) {
                    $currentQty = min($stockLedgerQty, $balanceQty);
                    StockLedgerReservation::create([
                        'issue_header_id' => $headerId,
                        'receipt_header_id' => $stkLdgr -> document_header_id,
                        'issue_detail_id' => $item -> id,
                        'receipt_detail_id' => $stkLdgr -> document_detail_id,
                        'issue_book_type' => $bookType,
                        'receipt_book_type' => $stkLdgr -> book_type,
                        'stock_ledger_id' => $stkLdgr -> id,
                        'quantity' => $currentQty
                    ]);
                    $reservedQty += $currentQty;
                    $stkLdgr -> reserved_qty += $currentQty;
                    $stkLdgr -> save();
                    $balanceQty -= $currentQty;
                }
                if ($balanceQty <= 0) {
                    break;
                }
            }
            if ($balanceQty > 0) {
                //error message
                return ['status'=> 'error','message'=> 'Enough stock not available for reservation'];
            }
        }
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
        } else if ($bookType === ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME) {
            $attributes = $item -> attributes;
            $selectedAttributes = [];
            foreach ($attributes as $attribute) { 
                array_push($selectedAttributes, $attribute -> attr_val);
            }
            $data['selected_attributes'] = $selectedAttributes;
            $data['uom_id'] = $item -> inventory_uom_id;
            $data['requested_qty'] = $item -> inventory_uom_qty;
            $data['stock_type'] = $item ?-> stock_type;
            $data['wip_station_id'] = $item ?-> wip_station_id;
            $data['station_id'] = $item ?-> from_station_id;
            $data['sub_store_id'] = $item ?-> from_sub_store_id;
            $data['store_id'] = $item ?-> from_store_id;
        }  else if ($bookType === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS || $bookType === ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS) {
            $attributes = $item -> attributes;
            $selectedAttributes = [];
            foreach ($attributes as $attribute) { 
                array_push($selectedAttributes, $attribute -> attr_val);
            }
            $data['selected_attributes'] = $selectedAttributes;
            $data['uom_id'] = $item -> inventory_uom_id;
            $data['requested_qty'] = $item -> inventory_uom_qty;
            $data['stock_type'] = 'R';
            $data['wip_station_id'] = null;
            $data['station_id'] = null;
            $data['sub_store_id'] = $item ?-> header ?-> sub_store_id;
            $data['store_id'] = $item ?-> header ?-> store_id;
        }
        return $data;
    }

    public static function validateReservedStock(string $bookType, int $headerId, int $detailId, float $qty) : array
    {
        //Get all the reserved stocks total qty
        $reservedQty = StockLedgerReservation::where('issue_book_type', $bookType)
            ->where('issue_header_id', $headerId)
            ->where('issue_detail_id', $detailId)
            ->sum('quantity');    
        //Return error if enough qty is not available    
        if ($reservedQty < $qty) {
            return ['status' => 'error', 'message' => 'Enough Stock not reserved'];
        }
        return ['status' => 'success', 'message' => 'Reserved Stock found'];
    }

    // public static function dereserveStock(string $bookType, int $headerId, int $detailId, float $currentQty) : array
    // {
    //     //Get all the reserved stocks
    //     $stockReservations = StockLedgerReservation::where('book_type', $bookType) -> where('header_id', $headerId)
    //         -> where('detail_id', $detailId) -> get();
        
    // }

    public static function settlementOfReservedStocks(string $bookType, int $headerId, int $detailId, float $totalQty, $recieve = false) : array
    {
        $authUser = Helper::getAuthenticatedUser();
        //Get the reserved Stocks
        $reservedStocksQuery = StockLedgerReservation::where('issue_book_type', $bookType)
            ->where('issue_header_id', $headerId)
            ->where('issue_detail_id', $detailId);
        //Get all records and total qty
        $reservedStocksQty = $reservedStocksQuery -> sum('quantity');
        $reservedStocks = $reservedStocksQuery -> get();
        //Issue qty is greater than reserved qty
        if ($totalQty > $reservedStocksQty) {
            return ['status' => 'error', 'message' => 'Enough reserved stocks not available'];
        }
        //Issue first
        $stockLedgerId = $reservedStocks -> first() ?-> stock_ledger_id;
        $mainStockLedger = StockLedger::find($stockLedgerId);

        $issueStockLedger = $mainStockLedger -> replicate();

        //Update required keys
        $issueStockLedger->id = null;
        $issueStockLedger->book_type = $bookType;
        //Retrieve the header
        $modelName = isset(ConstantHelper::SERVICE_ALIAS_MODELS[$bookType]) ? ConstantHelper::SERVICE_ALIAS_MODELS[$bookType] : null;
        $model = resolve("App\\Models\\" . $modelName);
        if (!$model) {
            return ['status' => 'error', 'message' => 'Module not found'];
        }
        $header = $model::find($headerId);
        if (!$header) {
            return ['status' => 'error', 'message' => 'Module Record not found'];
        }
        //Update for issue case
        $issueStockLedger->document_header_id = $headerId;
        $issueStockLedger->document_detail_id = $detailId;
        $issueStockLedger->book_id = $header -> book_id;
        $issueStockLedger->book_code = $header -> book_code;
        $issueStockLedger->document_number = $header -> document_number;
        $issueStockLedger->document_date = $header -> document_date;
        $issueStockLedger->transaction_type = 'issue';
        $issueStockLedger->receipt_qty = 0;
        $issueStockLedger->issue_qty = $totalQty;
        $issueStockLedger->reserved_qty = 0;
        $issueStockLedger->original_receipt_date = $mainStockLedger -> original_receipt_date;
        // $issueStockLedger->lot_number = $mainStockLedger -> lot_number;
        $issueStockLedger->created_at = Carbon::now() -> format('Y-m-d');
        $issueStockLedger->updated_at = Carbon::now() -> format('Y-m-d');
        $issueStockLedger->created_by = $authUser -> auth_user_id;
        $issueStockLedger->updated_by = $authUser -> auth_user_id;
        $issueStockLedger->save();
        $lotNos = "";

        //Loop through the reserved stocks
        foreach ($reservedStocks as $reservedStock) {
            
            $stockLedger = StockLedger::find($reservedStock -> stock_ledger_id);
            if (!$stockLedger) {
                return ['status' => 'error', 'message' => 'Stock Ledger record not found'];
            }
            //Add lot nos
            $lotNos .= (", " . $stockLedger -> lot_number);

            //Split receive
            $remainingQty = (float)$stockLedger -> receipt_qty - (float)$stockLedger -> reserved_qty;
            $remainingReservedQty = (float)$stockLedger -> reserved_qty - (float)$reservedStock -> quantity;
            //Minus reserved stock
            $stockLedger -> reserved_qty -= $reservedStock -> quantity;
            $stockLedger -> save();

            if ($remainingQty > 0) {
                $newReceieveStockLedger = $stockLedger -> replicate();
                $newReceieveStockLedger -> id = null;
                $newReceieveStockLedger -> reserved_qty = $remainingReservedQty;
                $newReceieveStockLedger -> receipt_qty = $remainingQty;
                $newReceieveStockLedger -> issue_qty = 0;
                $newReceieveStockLedger -> save();

                //Update to stock reservations
                StockLedgerReservation::where('id', '!=', $reservedStock -> id) -> where('stock_ledger_id', $stockLedger -> id) -> update([
                    'stock_ledger_id' => $newReceieveStockLedger -> id
                ]);
            }

            //Update utilized ID
            $stockLedger -> utilized_id = $issueStockLedger -> id;
            $stockLedger -> save();

            //Remove the reserved stocks (dereserve the stocks)
            $reservedStock -> save();
        
        }

        //Update Issue ledger lot nos
        $issueStockLedger -> lot_number = $lotNos;
        $issueStockLedger -> save();

        //Now receive (if required)
        if (!$recieve) {
            return ['status' => 'success', 'message' => 'Stock issued successfully'];
        }

        //Now receive
        $receiveStockLedger = $stockLedger -> replicate();
        $receiveStockLedger->id = null;
        $receiveStockLedger->book_type = $bookType;

        $receiveStockLedger->store_id=null;
        $receiveStockLedger->sub_store_id=null;
        $receiveStockLedger->station_id=null;
        $receiveStockLedger->wip_station_id=null;
        $receiveStockLedger->store=null;
        $receiveStockLedger->sub_store=null;

        if ($bookType === ConstantHelper::PL_SERVICE_ALIAS) {
            $receiveStockLedger->store_id =  $header -> store_id;
            $receiveStockLedger->store =  $header -> store ?-> store_name;
            $receiveStockLedger->sub_store_id =  $header -> staging_sub_store_id;
            $receiveStockLedger->sub_store =  $header -> staging_sub_store ?-> name;
        }
        if ($bookType === ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME) {
            $receiveStockLedger->store_id =  $header -> to_store_id;
            $receiveStockLedger->store =  $header -> to_store ?-> store_name;
            $receiveStockLedger->sub_store_id =  $header -> to_sub_store_id;
            $receiveStockLedger->sub_store =  $header -> to_sub_store ?-> name;
            $receiveStockLedger->stock_type =  $issueStockLedger -> stock_type;
            $receiveStockLedger->station_id =  $header -> to_station_id;
            $receiveStockLedger->wip_station_id =  $header -> wip_station_id;
        }

        $receiveStockLedger->document_header_id = $headerId;
        $receiveStockLedger->document_detail_id = $detailId;
        $receiveStockLedger->book_id = $header -> book_id;
        $receiveStockLedger->book_code = $header -> book_code;
        $receiveStockLedger->document_number = $header -> document_number;
        $receiveStockLedger->document_date = $header -> document_date;
        $receiveStockLedger->transaction_type = 'receipt';
        $receiveStockLedger->lot_number = $issueStockLedger->lot_number;
        $receiveStockLedger->document_status=$header->document_status;
        $receiveStockLedger->issue_qty=0;
        $receiveStockLedger->reserved_qty=0;
        $receiveStockLedger->receipt_qty=$issueStockLedger->issue_qty;
        $receiveStockLedger->utilized_id=null;
        $receiveStockLedger->save();

        return ['status' => 'success', 'message' => 'Stock issued and received successfully'];
    }

    // public function issueReservedStock(string $bookType, int $headerId, int $detailId, float $qty) : array
    // {
        
    // }

    // public function receiveReservedStocks() : array
    // {
         
    // }
}
