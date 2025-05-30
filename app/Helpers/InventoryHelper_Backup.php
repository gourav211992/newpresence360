<?php
namespace App\Helpers;

use DB;
use Auth;

use App\Models\ErpStore;
use App\Models\ErpPslipItem;
use App\Models\ErpProductionSlip;
use App\Models\ErpPslipItemLocation;

use App\Models\ErpSaleInvoice;
use App\Models\ErpInvoiceItem;
use App\Models\ErpInvoiceItemLocation;
use App\Models\ErpInvoiceItemAttribute;

use App\Models\Item;
use App\Models\Unit;
use App\Models\ErpAttribute;
use App\Models\ItemAttribute;

use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\MrnItemLocation;
use App\Models\MrnAttribute;

use App\Models\ErpSaleReturn;
use App\Models\ErpSaleReturnItem;
use App\Models\ErpSaleReturnItemLocation;
use App\Models\ErpSaleReturnItemAttribute;

use App\Models\PRHeader;
use App\Models\PRDetail;
use App\Models\PRItemLocation;
use App\Models\PRItemAttribute;

use App\Models\ErpMaterialIssueHeader;
use App\Models\ErpMiItem;
use App\Models\ErpMiItemLocation;
use App\Models\ErpMiItemAttribute;

use App\Models\MfgOrder;
use App\Models\MoItem;
use App\Models\MoItemLocation;
use App\Models\MoItemAttribute;

use App\Models\StockLedger;
use App\Models\StockLedgerReservation;
use App\Models\StockLedgerItemAttribute;

use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Models\MoProductionItem;
use App\Models\MoProductionItemLocation;
use Illuminate\Support\Facades\Log;


class InventoryHelper_Backup
{
    public static function settlementOfInventoryAndStock($documentHeaderId, $documentDetailId=NULL, $bookType, $documentStatus, $transactionType = NULL)
    {
        $user = Helper::getAuthenticatedUser();
        $message = '';
        $transactionType = $transactionType ?? '';
        $data = array();
        $records = array();
        if($bookType == ConstantHelper::MRN_SERVICE_ALIAS){
            $documentDetail = self::settlementForMRN($documentHeaderId, $documentDetailId, $bookType, $documentStatus);
            $records = array();
            $message = "Success";
        }
        else if($bookType == ConstantHelper::SR_SERVICE_ALIAS){
            $documentDetail = self::settlementForSaleReturn($documentHeaderId, $documentDetailId, $bookType, $documentStatus);
            $records = array();
            $message = "Success";
        }
        else if($bookType == ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS){
            $documentDetail = self::settlementForProductionSlip($documentHeaderId, $documentDetailId, $bookType, $documentStatus);
            $records = array();
            $message = "Success";
        }
        else if($bookType == ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS){
            $documentDetail = self::settlementForPurchaseReturn($documentHeaderId, $documentDetailId, $bookType, $documentStatus);
            $message = "Success";
            $invoiceIds = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id', $documentHeaderId)
            ->where('transaction_type', 'issue')
            ->pluck('id')->toArray();
            
            $records = StockLedger::with('issuedBy')
                ->where('organization_id', $user->organization_id)
                ->whereIn('utilized_id', $invoiceIds)
                ->where('transaction_type', 'receipt')
                ->select([
                    'utilized_id',
                    'store_id',
                    'rack_id',
                    'shelf_id',
                    'bin_id',
                    DB::raw('SUM(receipt_qty) as total_receipt_qty'),
                    DB::raw('SUM(org_currency_cost) as total_org_currency_cost')
                ])
                ->groupBy([
                    'utilized_id',
                    'store_id',
                    'rack_id',
                    'shelf_id',
                    'bin_id'
                ])
                ->get();

        }
        else if($bookType == ConstantHelper::MO_SERVICE_ALIAS){

            if($transactionType == 'issue') {
                $documentDetail = self::settlementForMO($documentHeaderId, $documentDetailId, $bookType, $documentStatus);
                $message = "Success";
                $invoiceIds = StockLedger::withDefaultGroupCompanyOrg()
                ->where('document_header_id', $documentHeaderId)
                ->where('transaction_type', 'issue')
                ->pluck('id')->toArray();
                
                $records = StockLedger::with('issuedBy')
                    ->where('organization_id', $user->organization_id)
                    ->whereIn('utilized_id', $invoiceIds)
                    ->where('transaction_type', 'receipt')
                    ->select([
                        'utilized_id',
                        'store_id',
                        'rack_id',
                        'shelf_id',
                        'bin_id',
                        DB::raw('SUM(receipt_qty) as total_receipt_qty'),
                        DB::raw('SUM(org_currency_cost) as total_org_currency_cost')
                    ])
                    ->groupBy([
                        'utilized_id',
                        'store_id',
                        'rack_id',
                        'shelf_id',
                        'bin_id'
                    ])
                    ->get();
            }
            if($transactionType == 'receipt') {
                $documentDetail = self::settlementForMOReceipt($documentHeaderId, $documentDetailId, $bookType, $documentStatus);
                $records = array();
                $message = "Success";
            }

        }
        else if($bookType == ConstantHelper::SI_SERVICE_ALIAS){
            $documentDetail = self::settlementForSaleInvoice($documentHeaderId, $documentDetailId, $bookType, $documentStatus);
            $message = "Success";
            $invoiceIds = StockLedger::withDefaultGroupCompanyOrg()
                ->where('document_header_id', $documentHeaderId)
                ->where('transaction_type', 'issue')
                ->pluck('id')->toArray();
            
            $records = StockLedger::with('issuedBy')
                ->where('organization_id', $user->organization_id)
                ->whereIn('utilized_id', $invoiceIds)
                ->where('transaction_type', 'receipt')
                ->select([
                    'utilized_id',
                    'store_id',
                    'rack_id',
                    'shelf_id',
                    'bin_id',
                    DB::raw('SUM(receipt_qty) as total_receipt_qty'),
                    DB::raw('SUM(org_currency_cost) as total_org_currency_cost')
                ])
                ->groupBy([
                    'utilized_id',
                    'store_id',
                    'rack_id',
                    'shelf_id',
                    'bin_id'
                ])
                ->get();
        }
        else if($bookType == ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME){
            if($transactionType == 'issue'){
                $documentDetail = self::settlementForMIForIssue($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType);
                $message = "Success";
                $invoiceIds = StockLedger::withDefaultGroupCompanyOrg()
                    ->where('document_header_id', $documentHeaderId)
                    ->where('transaction_type', 'issue')
                    ->pluck('id')->toArray();
            
                $records = StockLedger::with('issuedBy')
                    ->where('organization_id', $user->organization_id)
                    ->whereIn('utilized_id', $invoiceIds)
                    ->where('transaction_type', 'receipt')
                    ->select([
                        'utilized_id',
                        'store_id',
                        'rack_id',
                        'shelf_id',
                        'bin_id',
                        DB::raw('SUM(receipt_qty) as total_receipt_qty'),
                        DB::raw('SUM(org_currency_cost) as total_org_currency_cost')
                    ])
                    ->groupBy([
                        'utilized_id',
                        'store_id',
                        'rack_id',
                        'shelf_id',
                        'bin_id'
                    ])
                    ->get();
            }
            if($transactionType == 'receipt'){
                $documentDetail = self::settlementForMIForReceive($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType);
                $records = array();
                $message = "Success";
            }
        } 
        else {
            $message = "Invalid Book Type";
            $records = array();
        }

        $data = array(
            'messsage' => $message,
            'records' => $records,
        );

        return $data;
    }

    // Total Draft And Confirmed Stock
    public static function totalInventoryAndStock($itemId, $selectedAttr=null, $uomId=null, $storeId=null, $rackId=null, $shelfId=null, $binId=null, $orderId=null)
    {
        $user = Helper::getAuthenticatedUser();
        $reservedStocks = 0.00;
        $reservedStockAltUom = 0.00;
        $attributeGroups = ErpAttribute::whereIn('id', $selectedAttr)->pluck('attribute_group_id');
        $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
            ->where('item_id', $itemId)
            ->whereNull('utilized_id')
            ->whereNotNull('receipt_qty');

        // Apply attribute filtering if needed
        if (!empty($attributeGroups) && !empty($selectedAttr)) {
            // sort($selectedAttr);
            foreach ($attributeGroups as $key => $group) {
                // Ensure index exists and handle type consistency
                if (isset($selectedAttr[$key])) {
                    $stockLedger->whereJsonContains('item_attributes', [
                        'attr_name' => (string)$group,
                        'attr_value' => (string)$selectedAttr[$key]
                    ]);
                }
            }
        }

        // Filters for Store, Rack, Shelf, and Bin (if needed)
        if ($storeId) {
            $stockLedger->where('store_id', $storeId);
        }
        if ($rackId) {
            $stockLedger->where('rack_id', $rackId);
        }
        if ($shelfId) {
            $stockLedger->where('shelf_id', $shelfId);
        }
        if ($binId) {
            $stockLedger->where('bin_id', $binId);
        }

        // $stockLedger = $stockLedger->get();
        // $pendingStocks = $stockLedger->whereNotIn('document_status', ['approved','posted','approval_not_required'])->sum('receipt_qty');
        // $confirmedStocks = $stockLedger->whereIn('document_status', ['approved','posted','approval_not_required'])->sum('receipt_qty');

        // Clone the query before executing
        $pendingStocksQuery = clone $stockLedger;
        $confirmedStocksQuery = clone $stockLedger;
        $reservedStocksQuery = clone $stockLedger;

        $pendingStocks = $pendingStocksQuery
            ->whereNotIn('document_status', ['approved', 'posted', 'approval_not_required'])
            ->selectRaw('SUM(receipt_qty - reserved_qty) as total')
            ->value('total'); // Fetch the summed value
        
        $confirmedStocks = $confirmedStocksQuery
            ->whereIn('document_status', ['approved', 'posted', 'approval_not_required'])
            ->selectRaw('SUM(receipt_qty - reserved_qty) as total')
            ->value('total'); // Fetch the summed value

        if($orderId){
            $reservedStocks = $reservedStocksQuery
                ->whereIn('document_status', ['approved', 'posted', 'approval_not_required'])
                ->selectRaw('SUM(reserved_qty) as total')
                ->value('total'); // Fetch the summed value
        }

        $pendingStockAltUom = $pendingStocks;
        $reservedStockAltUom = $reservedStocks;
        $confirmedStockAltUom = $confirmedStocks;
        if($uomId){
            $pendingStockAltUom =  ItemHelper::convertToAltUom($itemId, $uomId, $pendingStocks ?? 0);
            if($orderId){
                $reservedStockAltUom =  ItemHelper::convertToAltUom($itemId, $uomId, $reservedStocks ?? 0);
            }
            $confirmedStockAltUom =  ItemHelper::convertToAltUom($itemId, $uomId, $confirmedStocks ?? 0);
        }
        $data = [
            'pendingStocks' => $pendingStocks ?? 0,
            'reservedStocks' => $reservedStocks ?? 0,
            'confirmedStocks' => $confirmedStocks ?? 0,
            'pendingStockAltUom' => $pendingStockAltUom ?? 0,
            'reservedStockAltUom' => $reservedStockAltUom ?? 0,
            'confirmedStockAltUom' => $confirmedStockAltUom ?? 0,
        ];
        return $data;
    }

    // Fetch stock summary
    public static function fetchStockSummary($itemId, $selectedAttr=null, $uomId=null, $quantity, $storeId=null, $rackId=null, $shelfId=null, $binId=null)
    {
        $user = Helper::getAuthenticatedUser();

        $availableQty = 0;
        $altUomAllocatedQty = 0;
        $qtyCheck = 0;
        $altUomRemainingQuantity = 0;

        $attributeGroups = ErpAttribute::whereIn('id', $selectedAttr)->pluck('attribute_group_id');
        $query = StockLedger::query()
            ->where('organization_id', $user->organization_id)
            ->whereIn('document_status', ['approved','posted','approval_not_required'])
            ->where('transaction_type', '=', 'receipt')
            ->whereNull('utilized_id');

        // Item Filter
        if ($itemId) {
            $query->where('item_id', $itemId);
        }

        // Apply attribute filtering if needed
        if (!empty($attributeGroups) && !empty($selectedAttr)) {
            foreach ($attributeGroups as $key => $group) {
                // Ensure index exists and handle type consistency
                if (isset($selectedAttr[$key])) {
                    $query->whereJsonContains('item_attributes', [
                        'attr_name' => (string)$group,
                        'attr_value' => (string)$selectedAttr[$key]
                    ]);
                }
            }
        }

        // Filters for Store, Rack, Shelf, and Bin (if needed)
        if ($storeId) {
            $query->where('store_id', $storeId);
        }
        if ($rackId) {
            $query->where('rack_id', $rackId);
        }
        if ($shelfId) {
            $query->where('shelf_id', $shelfId);
        }
        if ($binId) {
            $query->where('bin_id', $binId);
        }

        // Select Records with Grouping and Summing
        $query->select([
            'stock_ledger.*',
            DB::raw('SUM(receipt_qty - reserved_qty) as total_receipt_qty'),
            DB::raw('SUM(org_currency_cost) as total_org_currency_cost')
        ])
        ->orderBy('id')
        ->groupBy([
            'item_id',
            'store_id',
            'rack_id',
            'shelf_id',
            'bin_id'
        ]);
        $query = $query->get();
        if(count($query) < 1){
            // dd('empty');
            $code = 202;
            $status = 'error';
            $message = 'There is no approved stock, Please approve mrn first.';
            $availStock = 0;
            $inputStock = 0;
            $qtyCheck = 0;
            $records = '';
        }
        else{
            // Initialize variables for FIFO breakup
            $costPerUnit = 0;
            $remainingQuantity = $quantity;
            $fifoBreakup = [];
            $data = array();
            foreach ($query as $stockSummary) {
                $availableQty = $stockSummary->total_receipt_qty;
                if ($availableQty <= 0) {
                    continue; // Skip if no available quantity
                }
                $costPerUnit = $stockSummary->total_org_currency_cost/$availableQty;
                // Allocate quantity
                $allocatedQty = $quantity ? min($availableQty, $remainingQuantity) : $availableQty;
                $altUomAllocatedQty = $allocatedQty;
                if($uomId){
                    $altUomAllocatedQty =  ItemHelper::convertToAltUom($itemId, $uomId, $allocatedQty);
                    $altUomRemainingQuantity =  ItemHelper::convertToAltUom($itemId, $uomId, $remainingQuantity);
                }
                $fifoBreakup[] = [
                    'item_id' => $stockSummary->item_id,
                    'item_name' => $stockSummary->item_name,
                    'item_code' => $stockSummary->item_code,
                    'store_id' => $stockSummary->store_id,
                    'store' => $stockSummary->store,
                    'rack_id' => $stockSummary->rack_id,
                    'rack' => $stockSummary->rack,
                    'shelf_id' => $stockSummary->shelf_id,
                    'shelf' => $stockSummary->shelf,
                    'bin_id' => $stockSummary->bin_id,
                    'bin' => $stockSummary->bin,
                    'allocated_quantity' => $allocatedQty,
                    'allocated_quantity_alt_uom' => $altUomAllocatedQty,
                    'cost_per_unit' => $stockSummary->cost_per_unit,
                    'org_currency_cost' => round(($costPerUnit*$allocatedQty),2),
                ];

                // Decrease remaining quantity
                $remainingQuantity -= $allocatedQty;

                // Stop if we've fulfilled the required quantity
                if ($remainingQuantity <= 0) {
                    break;
                }
            }
            // If remaining quantity is still greater than 0, it means not enough stock was available
            if ($altUomRemainingQuantity > $altUomAllocatedQty) {
                $code = 202;
                $status = 'error';
                $message = 'Qty cannot be greater than confirmed stock';
                $availStock = $altUomAllocatedQty;
                $inputStock = $altUomRemainingQuantity;
                $qtyCheck = 1;
                $records = '';
            } else{
                $code = 200;
                $status = 'success';
                $message = 'Record fetched successfuly.';
                $availStock = $altUomAllocatedQty;
                $inputStock = $altUomRemainingQuantity;
                $qtyCheck = 0;
                $records = $fifoBreakup;
            }
        }

        $data = [
            'code' => $code,
            'status' => $status,
            'message' => $message,
            'availStock' => $availStock,
            'inputStock' => $inputStock,
            'records' => $records
        ];
        // Return the FIFO allocation breakup
        return $data;
    }

    // Update document status while update mrn
    public static function updateInventoryAndStock($documentHeaderId, $bookType, $documentStatus)
    {
        $user = Helper::getAuthenticatedUser();

        $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id',$documentHeaderId)
            ->where('book_type','=',$bookType)
            ->where('document_status', '!=', ConstantHelper::DRAFT)
            // ->whereNotIn('document_status', [ConstantHelper::DRAFT, ConstantHelper::REJECTED])
            ->get();

        if(count($stockLedger) > 0){
            foreach($stockLedger as $val){
                $val->document_status = $documentStatus;
                $val->save();
            }
        }
    }

    // Update document status while update mrn
    private static function insertStockLedger($stockLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType, $utilizedQty, $utlStockLedger = NULL)
    {
        $user = Helper::getAuthenticatedUser();

        $documentHeader = '';
        $documentDetail = '';
        $totalItemCost = 0.00;
        $costPerUnit = 0.00;
        $qty = 0.00;
        
        // Receive
        if($bookType == ConstantHelper::MRN_SERVICE_ALIAS){
            $qty = ($documentItemLocation->inventory_uom_qty - $utilizedQty);
            $documentHeader = MrnHeader::find($documentItemLocation->mrn_header_id);
            $documentDetail = MrnDetail::with(['header', 'attributes'])->find($documentItemLocation->mrn_detail_id);
            $stockLedger->vendor_id = @$documentHeader->vendor_id;
            $stockLedger->vendor_code = @$documentHeader->vendor_code;
            $stockLedger->receipt_qty = $qty ?? 0;
            $stockLedger->book_id = @$documentHeader->book_id;
            $totalItemCost = $documentDetail->basic_value - ($documentDetail->discount_amount + $documentDetail->header_discount_amount);
            $costPerUnit = $totalItemCost/$qty;

            // Item Location Data
            $stockLedger->store_id = $documentItemLocation->store_id ?? null;
            $stockLedger->rack_id = $documentItemLocation->rack_id ?? null;
            $stockLedger->shelf_id = $documentItemLocation->shelf_id ?? null;
            $stockLedger->bin_id = $documentItemLocation->bin_id ?? null;
            $stockLedger->store = @$documentItemLocation->erpStore->store_code;
            $stockLedger->rack = @$documentItemLocation->erpRack->rack_code;
            $stockLedger->shelf = @$documentItemLocation->erpShelf->shelf_code;
            $stockLedger->bin = @$documentItemLocation->erpBin->bin_code;
            $stockLedger->original_receipt_date = @$documentHeader->document_date;
        }

        // Receive
        if($bookType == ConstantHelper::SR_SERVICE_ALIAS){
            $qty = ($documentItemLocation->inventory_uom_qty - $utilizedQty);
            $documentHeader = ErpSaleReturn::find($documentItemLocation->sale_return_id);
            $documentDetail = ErpSaleReturnItem::with(['header', 'attributes'])->find($documentItemLocation->sale_return_item_id);
            $stockLedger->vendor_id = @$documentHeader->vendor_id;
            $stockLedger->vendor_code = @$documentHeader->vendor_code;
            $stockLedger->receipt_qty = $qty ?? 0;
            $stockLedger->book_id = @$documentHeader->book_id;
            $totalItemCost = ($documentDetail->order_qty*$documentDetail->rate) - ($documentDetail->item_discount_amount + $documentDetail->header_discount_amount);
            $costPerUnit = $totalItemCost/$qty;

            // Item Location Data
            $stockLedger->store_id = $documentItemLocation->store_id ?? null;
            $stockLedger->rack_id = $documentItemLocation->rack_id ?? null;
            $stockLedger->shelf_id = $documentItemLocation->shelf_id ?? null;
            $stockLedger->bin_id = $documentItemLocation->bin_id ?? null;
            $stockLedger->store = @$documentItemLocation->erpStore->store_code;
            $stockLedger->rack = @$documentItemLocation->erpRack->rack_code;
            $stockLedger->shelf = @$documentItemLocation->erpShelf->shelf_code;
            $stockLedger->bin = @$documentItemLocation->erpBin->bin_code;
        }

        // Issue
        if($bookType == ConstantHelper::SI_SERVICE_ALIAS){
            $qty = @$documentItemLocation->inventory_uom_qty;
            $documentHeader = ErpSaleInvoice::find($documentItemLocation->sale_invoice_id);
            $documentDetail = ErpInvoiceItem::with(['header', 'attributes'])->find($documentItemLocation->id);
            $stockLedger->customer_id = @$documentHeader->customer_id;
            $stockLedger->customer_code = @$documentHeader->customer_code;
            $stockLedger->issue_qty = @$qty;
            $stockLedger->book_id = @$documentHeader->book_id;
            $totalItemCost = ($documentDetail->order_qty*$documentDetail->rate) - ($documentDetail->item_discount_amount + $documentDetail->header_discount_amount);
            $costPerUnit = $totalItemCost/$qty;

            // Item Location Data
            $stockLedger->store_id = $documentItemLocation->store_id ?? null;
            $stockLedger->store = @$documentItemLocation->erpStore->store_code;
        }

        // Issue
        if($bookType == ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS){
            $qty = @$documentItemLocation->inventory_uom_qty;
            $documentHeader = PRHeader::find($documentItemLocation->header_id);
            $documentDetail = PRDetail::with(['header', 'attributes'])->find($documentItemLocation->id);
            $stockLedger->vendor_id = @$documentHeader->vendor_id;
            $stockLedger->vendor_code = @$documentHeader->vendor_code;
            $stockLedger->issue_qty = @$qty;
            $stockLedger->book_id = @$documentHeader->book_id;
            $totalItemCost = ($documentDetail->accepted_qty*$documentDetail->rate) - ($documentDetail->item_discount_amount + $documentDetail->header_discount_amount);
            $costPerUnit = $totalItemCost/$qty;

            // Item Location Data
            $stockLedger->store_id = $documentItemLocation->store_id ?? null;
            $stockLedger->store = @$documentItemLocation->erpStore->store_code;
        }

        // Issue
        if($bookType == ConstantHelper::MO_SERVICE_ALIAS) {
            if($transactionType == 'issue') {
                $qty = @$documentItemLocation->inventory_uom_qty;
                $documentHeader = MfgOrder::find($documentItemLocation->mo_id);
                $documentDetail = MoItem::with(['mo', 'attributes'])->find($documentItemLocation->id);
                $stockLedger->vendor_id = null;
                $stockLedger->vendor_code = null;
                $stockLedger->issue_qty = @$qty;
                $stockLedger->book_id = @$documentHeader->book_id;
                // $totalItemCost = ($documentDetail->qty*$documentDetail->rate) - ($documentDetail->item_discount_amount + $documentDetail->header_discount_amount);
                // $costPerUnit = $totalItemCost/$qty;
                $totalItemCost = 0;
                $costPerUnit = 0;
    
                // Item Location Data
                $stockLedger->store_id = $documentHeader->store_id ?? null;
                $stockLedger->store = @$documentHeader?->store_location?->store_code;
            }
            if($transactionType == 'receipt') {
                $qty = ($documentItemLocation->inventory_uom_qty - $utilizedQty);
                $documentHeader = MfgOrder::find($documentItemLocation->mo_id);
                $documentDetail = MoProductionItem::with(['mo', 'productionAttributes'])->find($documentItemLocation->mo_production_item_id);
                // Over ride attribute
                $documentDetail->attributes = $documentDetail->productionAttributes;
                $stockLedger->vendor_id = null;
                $stockLedger->vendor_code = null;
                $stockLedger->receipt_qty = $qty ?? 0;
                $stockLedger->book_id = @$documentHeader->book_id;
                $totalItemCost = ($documentDetail->produced_qty*$documentDetail->rate);
                $costPerUnit = $totalItemCost/$qty;
                
                // Item Location Data
                $stockLedger->store_id = $documentItemLocation->store_id ?? null;
                $stockLedger->rack_id = $documentItemLocation->rack_id ?? null;
                $stockLedger->shelf_id = $documentItemLocation->shelf_id ?? null;
                $stockLedger->bin_id = $documentItemLocation->bin_id ?? null;
                $stockLedger->store = @$documentItemLocation->erpStore->store_code;
                $stockLedger->rack = @$documentItemLocation->erpRack->rack_code;
                $stockLedger->shelf = @$documentItemLocation->erpShelf->shelf_code;
                $stockLedger->bin = @$documentItemLocation->erpBin->bin_code;
            }
        }

        if($bookType == ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME){
            $qty = @$documentItemLocation->inventory_uom_qty;
            $documentHeader = ErpMaterialIssueHeader::find($documentItemLocation->material_issue_id);
            $detailId = ($transactionType == 'receipt') ? $documentItemLocation->mi_item_id : $documentItemLocation->id;
            $documentDetail = ErpMiItem::with(['header', 'attributes'])->find($detailId);
            $stockLedger->vendor_id = @$documentHeader->vendor_id;
            $stockLedger->vendor_code = @$documentHeader->vendor_code;
            if ($transactionType == 'issue') {
                $stockLedger->issue_qty = @$qty;
            }
            if ($transactionType == 'receipt') {
                $stockLedger->receipt_qty = @$utlStockLedger->receipt_qty;
                $stockLedger->original_receipt_date = @$utlStockLedger->original_receipt_date;
            }
            $stockLedger->book_id = @$documentHeader->book_id;
            $totalItemCost = ($documentDetail->issue_qty*$documentDetail->rate) - ($documentDetail->item_discount_amount + $documentDetail->header_discount_amount);
            $costPerUnit = $totalItemCost/$qty;
            // Item Location Data
            if(($transactionType == 'issue') && $documentDetail->from_store_id){
                $stockLedger->store_id = $documentDetail->from_store_id ?? null;
                $stockLedger->store = @$documentDetail->fromErpStore->store_code;
            }
            
            if(($transactionType == 'receipt') && ($documentDetail->from_store_id && $documentDetail->to_store_id)){
                $stockLedger->store_id = $documentDetail->to_store_id ?? null;
                $stockLedger->store = @$documentDetail->toErpStore->store_code;
            }
        }
        if($bookType == ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS){
            $qty = ($documentItemLocation->inventory_uom_qty - $utilizedQty);
            $documentHeader = ErpProductionSlip::find($documentItemLocation->pslip_id);
            $documentDetail = ErpPslipItem::with(['header', 'attributes'])->find($documentItemLocation->pslip_item_id);
            $stockLedger->vendor_id = null;
            $stockLedger->vendor_code = null;
            $stockLedger->receipt_qty = $qty ?? 0;
            $stockLedger->book_id = @$documentHeader->book_id;
            $totalItemCost = ($documentDetail->order_qty*$documentDetail->rate) - ($documentDetail->item_discount_amount + $documentDetail->header_discount_amount);
            $costPerUnit = $totalItemCost/$qty;

            // Item Location Data
            $stockLedger->store_id = $documentItemLocation->store_id ?? null;
            $stockLedger->rack_id = $documentItemLocation->rack_id ?? null;
            $stockLedger->shelf_id = $documentItemLocation->shelf_id ?? null;
            $stockLedger->bin_id = $documentItemLocation->bin_id ?? null;
            $stockLedger->store = @$documentItemLocation->erpStore->store_code;
            $stockLedger->rack = @$documentItemLocation->erpRack->rack_code;
            $stockLedger->shelf = @$documentItemLocation->erpShelf->shelf_code;
            $stockLedger->bin = @$documentItemLocation->erpBin->bin_code;
        }

        $inventoryUom = Unit::find($documentDetail->item->uom_id);
        //Header Data
        $stockLedger->group_id = @$documentHeader->group_id;
        $stockLedger->company_id = @$documentHeader->company_id;
        $stockLedger->organization_id = @$documentHeader->organization_id;
        $stockLedger->document_header_id = @$documentHeader->id;
        $stockLedger->document_detail_id = @$documentDetail->id;
        $stockLedger->book_code = @$documentHeader->book_code;
        $stockLedger->document_number = @$documentHeader->document_number;
        $stockLedger->document_date = @$documentHeader->document_date;

        $stockLedger->cost_per_unit = round(@$costPerUnit,6);
        $stockLedger->total_cost = round(@$totalItemCost, 2);

        //costing exchange rate currency
        $stockLedger->document_currency_id = @$documentHeader->transaction_currency_id;
        $stockLedger->document_currency = @$documentHeader->transaction_currency;
        $stockLedger->org_currency_id = @$documentHeader->org_currency_id;
        $stockLedger->org_currency_code = @$documentHeader->org_currency_code;
        $stockLedger->org_currency_exg_rate = @$documentHeader->org_currency_exg_rate;
        $stockLedger->comp_currency_id = @$documentHeader->comp_currency_id;
        $stockLedger->comp_currency_code = @$documentHeader->comp_currency_code;
        $stockLedger->comp_currency_exg_rate = @$documentHeader->comp_currency_exg_rate;
        $stockLedger->group_currency_id = @$documentHeader->group_currency_id;
        $stockLedger->group_currency_code = @$documentHeader->group_currency_code;
        $stockLedger->group_currency_exg_rate = @$documentHeader->group_currency_exg_rate;
        $stockLedger->original_receipt_date = @$documentHeader->document_date;

        // Detail Data
        $stockLedger->item_id = @$documentDetail->item_id;
        $stockLedger->item_code = @$documentDetail->item_code;
        $stockLedger->item_name = @$documentDetail->item->item_name;
        $stockLedger->inventory_uom_id = @$inventoryUom->id;
        $stockLedger->inventory_uom = @$inventoryUom->name;

        $stockLedger->book_type = $bookType;
        $stockLedger->document_status = $documentStatus;
        $stockLedger->transaction_type = $transactionType;

        $stockLedger->created_by = @$user->id;
        $stockLedger->updated_by = @$user->id;
        $stockLedger->save();

        self::updateStockCost($stockLedger);

        $attributeArray = array();
        $attributeJsonArray = array();
        if(isset($documentDetail->attributes) && !empty($documentDetail->attributes)){
            foreach($documentDetail->attributes as $key1 => $attribute){
                $attributeName = @$attribute->attr_name ?? @$attribute->attribute_group_id ?? @$attribute->attribute_name;
                $attributeValue = @$attribute->attr_value ?? @$attribute->attribute_id ?? @$attribute->attribute_value;
                // if($bookType == ConstantHelper::MO_SERVICE_ALIAS){
                //     $attributeName = @$attribute->attribute_name;
                //     $attributeValue = @$attribute->attribute_value;
                // }
                $ledgerAttribute = new StockLedgerItemAttribute();
                $ledgerAttribute->stock_ledger_id = $stockLedger->id;
                $ledgerAttribute->item_id = @$documentDetail->item_id;
                $ledgerAttribute->item_code = @$documentDetail->item_code;
                $ledgerAttribute->item_attribute_id = @$attribute->item_attribute_id;
                $ledgerAttribute->attribute_name = @$attributeName;
                $ledgerAttribute->attribute_value = @$attributeValue;
                $ledgerAttribute->status = "active";
                $ledgerAttribute->save();

                $attributeArray[] = [
                    "attr_name" => (string)$ledgerAttribute->attribute_name,
                    "attribute_name" => (string)@$ledgerAttribute->attributeName->name,
                    "attr_value" => (string)@$ledgerAttribute->attribute_value,
                    "attribute_value" => (string)@$ledgerAttribute->attributeValue->value,
                ];

                $attributeJsonArray[] = [
                    "attr_name" => (string)$ledgerAttribute->attribute_name,
                    "attribute_name" => (string)@$ledgerAttribute->attributeName->name,
                    "attr_value" => (string)@$ledgerAttribute->attribute_value,
                    "attribute_value" => (string)@$ledgerAttribute->attributeValue->value,
                ];
            }
        }

        $stockLedger->item_attributes = $attributeArray;
        $stockLedger->json_item_attributes = $attributeJsonArray;
        $stockLedger->save();
        return $stockLedger;
    }

    // Update Issue Stock
    private static function updateStockLedger($invoiceLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType, $issueQty)
    {
        $user = Helper::getAuthenticatedUser();

        $balanceQty = 0;
        $extraQty = 0;
        $receiptQty = 0;
        $adjustedQty = 0;
        $reservedQty = 0;
        $extraReservedQty = 0;
        $message = '';
        if($issueQty && ($issueQty > $documentItemLocation->inventory_uom_qty)){
            $balanceQty = $issueQty - $documentItemLocation->inventory_uom_qty;
            $message = self::updateIssueStockForLessQty($invoiceLedger, $balanceQty, $documentItemLocation);
        } else{
            $balanceQty = $documentItemLocation->inventory_uom_qty - $issueQty;
            $approvedStockLedger = StockLedger::withDefaultGroupCompanyOrg()
                ->whereIn('document_status', ['approved','posted','approval_not_required'])
                ->where('item_id', $invoiceLedger->item_id)
                ->where('store_id', $invoiceLedger->store_id)
                ->where('transaction_type', 'receipt')
                ->whereNull('utilized_id')
                ->whereRaw('receipt_qty > 0')
                ->orderBy('document_date', 'ASC');

                $attributeGroups = $invoiceLedger->item_attributes;
                // Apply attribute filtering if needed
                if (!empty($attributeGroups)) {
                    foreach ($attributeGroups as $key => $group) {
                        // Ensure index exists and handle type consistency
                        $approvedStockLedger->whereJsonContains('item_attributes', [
                            'attr_name' => (string)$group['attr_name'],
                            'attr_value' => (string)$group['attr_value']
                        ]);
                    }
                }
                $approvedStockLedger = $approvedStockLedger->get();
            if ($approvedStockLedger->isNotEmpty()) {
                foreach ($approvedStockLedger as $val) {
                    $stockLedger = StockLedger::find($val -> id);
                    if(isset($documentItemLocation->so_item_id) && $documentItemLocation->so_item_id){
                        if ($stockLedger->reserved_qty < $balanceQty) {
                            $receiptQty = $stockLedger->reserved_qty;
                            $reservedQty = $stockLedger->reserved_qty;
                            $balanceQty -= $reservedQty;
                        } else {
                            $receiptQty = $balanceQty;
                            $reservedQty = $balanceQty;
                            $extraQty = $stockLedger->receipt_qty - $balanceQty;
                            $extraReservedQty = $stockLedger->reserved_qty - $balanceQty;
                            $balanceQty = 0; // Fully issued
                        }

                    } else{
                        if ($stockLedger->receipt_qty < $balanceQty) {
                            $receiptQty = $stockLedger->receipt_qty;
                            $balanceQty -= $receiptQty;
                        } else {
                            $receiptQty = $balanceQty;
                            $extraQty = $stockLedger->receipt_qty - $balanceQty;
                            $balanceQty = 0; // Fully issued
                        }
                    }

                    // Update stock ledger for issued quantity
                    $stockLedger->receipt_qty = $receiptQty;
                    $stockLedger->reserved_qty = $reservedQty;
                    $stockLedger->utilized_id = $invoiceLedger->id;
                    $stockLedger->utilized_date = $invoiceLedger->created_at->format('Y-m-d');
                    $stockLedger->save();

                    $stockLedger->total_cost = round(($stockLedger->cost_per_unit*$stockLedger->receipt_qty), 2);
                    $stockLedger->save();
                    self::updateStockCost($stockLedger);

                    if(isset($documentItemLocation->so_item_id) && $documentItemLocation->so_item_id){
                        // Handle extra quantity by creating a new stock ledger entry
                        if ($extraReservedQty > 0) {
                            $newStockLedger = $stockLedger->replicate();
                            $newStockLedger->receipt_qty -= $extraReservedQty;
                            $newStockLedger->reserved_qty = $extraReservedQty;
                            $newStockLedger->issue_qty = 0.00;
                            $newStockLedger->utilized_id = null;
                            $newStockLedger->utilized_date = null;
                            $newStockLedger->save();

                            $newStockLedger->total_cost = round(($newStockLedger->cost_per_unit*$newStockLedger->receipt_qty), 2);
                            $newStockLedger->save();
                        } 
                    } else{
                        // Handle extra quantity by creating a new stock ledger entry
                        if ($extraQty > 0) {
                            $newStockLedger = $stockLedger->replicate();
                            $newStockLedger->receipt_qty = $extraQty;
                            $newStockLedger->issue_qty = 0.00;
                            $newStockLedger->utilized_id = null;
                            $newStockLedger->utilized_date = null;
                            $newStockLedger->save();

                            $newStockLedger->total_cost = round(($newStockLedger->cost_per_unit*$newStockLedger->receipt_qty), 2);
                            $newStockLedger->save();
                        }
                    }
                    self::updateStockCost($newStockLedger);

                    // Stop the loop if the balance has been fully issued
                    if ($balanceQty <= 0) {
                        break;
                    }
                }
                $message = "Success";
            } else{
                $message = "This item does not have approved stocks, Please approve the mrn first.";
            }
        }
        if($transactionType == 'issue'){
            self::updateIssueCost($invoiceLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType);
        }
        return $message;
    }

    // Update Issue Cost
    private static function updateIssueCost($invoiceLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType)
    {
        $user = Helper::getAuthenticatedUser();

        $totalCost = 0;
        $costPerUnit = 0;

        $utilizedStockLedger = StockLedger::withDefaultGroupCompanyOrg()
            ->where('utilized_id', $invoiceLedger->id)
            ->whereNotNull('receipt_qty')
            ->orderBy('document_date', 'DESC')
            ->get();
        if ($utilizedStockLedger->isNotEmpty()) {
            foreach($utilizedStockLedger as $val){
                $totalCost += $val->total_cost;
            }
        }
        $costPerUnit = ($totalCost/$invoiceLedger->issue_qty);
        $stockLedger = StockLedger::find($invoiceLedger->id);
        $stockLedger->cost_per_unit = round($costPerUnit,6);
        $stockLedger->total_cost = round($totalCost, 2);
        $stockLedger->save();
        self::updateStockCost($stockLedger);

    }

    // Update Issue Stock For Less Qty
    private static function updateIssueStockForLessQty($invoiceLedger, $balanceQty, $documentItemLocation=NULL)
    {
        $user = Helper::getAuthenticatedUser();

        $extraQty = 0;
        $receiptQty = 0;
        $adjustedQty = 0;
        $reservedQty = 0;
        $extraReservedQty = 0;

        $utilizedStockLedger = StockLedger::withDefaultGroupCompanyOrg()
            ->where('utilized_id', $invoiceLedger->id)
            ->whereNotNull('receipt_qty')
            ->orderBy('document_date', 'DESC')
            ->get();

        if ($utilizedStockLedger->isNotEmpty()) {
            foreach ($utilizedStockLedger as $val) {
                $adjustedQty = 0;
                $adjustedType = 0;
                $stockLedger = StockLedger::find($val -> id);
                if(isset($documentItemLocation->so_item_id) && $documentItemLocation->so_item_id){
                    if ($stockLedger->reserved_qty <= $balanceQty) {
                        // $stockLedger->attributes()->delete();
                        $adjustedQty = $stockLedger->reserved_qty;
                    } else {
                        $adjustedQty = $balanceQty;
                        // Update stock ledger for issued quantity
                        $stockLedger->reserved_qty -= $adjustedQty;
                        $stockLedger->receipt_qty -= $adjustedQty;
                        $stockLedger->save();
                        $adjustedType = 1;

                        $stockLedger->total_cost = round(($stockLedger->cost_per_unit*$stockLedger->receipt_qty), 2);
                        $stockLedger->save();

                        self::updateStockCost($stockLedger);
                    }
                    $untilizedStockLedger = StockLedger::withDefaultGroupCompanyOrg()
                        ->where('document_header_id', $stockLedger->document_header_id)
                        ->where('document_detail_id', $stockLedger->document_detail_id)
                        ->whereNull('utilized_id')
                        ->first();
                    if($untilizedStockLedger){
                        $untilizedStockLedger->reserved_qty += $adjustedQty;
                        $untilizedStockLedger->receipt_qty += $adjustedQty;
                        $untilizedStockLedger->save();

                        $untilizedStockLedger->total_cost = round(($untilizedStockLedger->cost_per_unit*$untilizedStockLedger->receipt_qty), 2);
                        $untilizedStockLedger->save();

                        self::updateStockCost($untilizedStockLedger);

                    } else{
                        if($adjustedType == 0){
                            $stockLedger->issue_qty = 0.00;
                            $stockLedger->utilized_id = null;
                            $stockLedger->utilized_date = null;
                            $stockLedger->save();

                        } else{
                            $newStockLedger = $untilizedStockLedger->replicate();
                            $newStockLedger->reserved_qty = $adjustedQty;
                            $newStockLedger->receipt_qty = $adjustedQty;
                            $newStockLedger->issue_qty = 0.00;
                            $newStockLedger->utilized_id = null;
                            $newStockLedger->utilized_date = null;
                            $newStockLedger->save();

                            $newStockLedger->total_cost = round(($newStockLedger->cost_per_unit*$newStockLedger->receipt_qty), 2);
                            $newStockLedger->save();

                            self::updateStockCost($newStockLedger);
                        }
                    }
                    $balanceQty -= $adjustedQty;
                    // Stop the loop if the balance has been fully issued
                    if ($balanceQty <= 0) {
                        break;
                    }

                } else{
                    if ($stockLedger->receipt_qty <= $balanceQty) {
                        // $stockLedger->attributes()->delete();
                        $adjustedQty = $stockLedger->receipt_qty;
                    } else {
                        $adjustedQty = $balanceQty;
                        // Update stock ledger for issued quantity
                        $stockLedger->receipt_qty -= $adjustedQty;
                        $stockLedger->save();
                        $adjustedType = 1;

                        $stockLedger->total_cost = round(($stockLedger->cost_per_unit*$stockLedger->receipt_qty), 2);
                        $stockLedger->save();

                        self::updateStockCost($stockLedger);
                    }
                    $untilizedStockLedger = StockLedger::withDefaultGroupCompanyOrg()
                        ->where('document_header_id', $stockLedger->document_header_id)
                        ->where('document_detail_id', $stockLedger->document_detail_id)
                        ->whereNull('utilized_id')
                        ->first();
                    if($untilizedStockLedger){
                        $untilizedStockLedger->receipt_qty += $adjustedQty;
                        $untilizedStockLedger->save();

                        $untilizedStockLedger->total_cost = round(($untilizedStockLedger->cost_per_unit*$untilizedStockLedger->receipt_qty), 2);
                        $untilizedStockLedger->save();

                        self::updateStockCost($untilizedStockLedger);

                    } else{
                        if($adjustedType == 0){
                            $stockLedger->issue_qty = 0.00;
                            $stockLedger->utilized_id = null;
                            $stockLedger->utilized_date = null;
                            $stockLedger->save();

                        } else{
                            $newStockLedger = $untilizedStockLedger->replicate();
                            $newStockLedger->receipt_qty = $adjustedQty;
                            $newStockLedger->receipt_qty = $adjustedQty;
                            $newStockLedger->issue_qty = 0.00;
                            $newStockLedger->utilized_id = null;
                            $newStockLedger->utilized_date = null;
                            $newStockLedger->save();

                            $newStockLedger->total_cost = round(($newStockLedger->cost_per_unit*$newStockLedger->receipt_qty), 2);
                            $newStockLedger->save();

                            self::updateStockCost($newStockLedger);
                        }
                    }
                    $balanceQty -= $adjustedQty;
                    // Stop the loop if the balance has been fully issued
                    if ($balanceQty <= 0) {
                        break;
                    }
                }

                
            }
            return "Success";
        }
    }

    // Delete Issue Stock
    public static function deleteIssueStock($documentHeaderId, $documentDetailId, $bookType)
    {
        $user = Helper::getAuthenticatedUser();
        $code = 200;
        $status = 'success';
        $message = 'Stock ledger successfully deleted.';

        // Get stock ledger for the specified filters
        $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id', $documentHeaderId)
            ->where('document_detail_id', $documentDetailId)
            ->where('book_type', $bookType)
            ->where('transaction_type', 'issue')
            ->get();

        if ($stockLedger->isEmpty) {
            return [
                'code' => 404,
                'status' => 'error',
                'message' => 'Stock ledger not found.'
            ];
        }
        foreach($stockLedger as $val){
            $balanceQty = $val->issue_qty;
            $message = self::updateIssueStockForLessQty($val, $balanceQty);
            $val->delete();
        }

        return [
            'code' => $code,
            'status' => $status,
            'message' => $message
        ];

    }

    // Delete Receipt Stock
    public static function deleteReceiptStock($documentHeaderId, $documentDetailId, $bookType)
    {
        $user = Helper::getAuthenticatedUser();
        $code = 200;
        $status = 'success';
        $message = 'Stock ledger successfully deleted.';

        // Check if the stock is already utilized
        $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id', $documentHeaderId)
            ->where('document_detail_id', $documentDetailId)
            ->where('book_type', $bookType)
            ->where('transaction_type', 'receipt')
            ->whereNotNull('utilized_id')
            ->first();
        if ($stockLedger) {
            // If the stock is utilized, return an error
            return [
                'code' => 200,
                'status' => 'error',
                'message' => "Item cannot be deleted as it has already been utilized."
            ];
        } else {
            // Delete non-utilized stock ledgers
            $stockLedgers = StockLedger::withDefaultGroupCompanyOrg()
                ->where('document_header_id', $documentHeaderId)
                ->where('document_detail_id', $documentDetailId)
                ->where('book_type', $bookType)
                ->where('transaction_type', 'receipt')
                ->whereNull('utilized_id')
                ->get();

            foreach ($stockLedgers as $ledger) {
                $ledger->delete();
            }
        }

        return [
            'code' => $code,
            'status' => $status,
            'message' => $message
        ];
    }

    // Check Stock Status Item/Attributes/Store/Rack/Bin/Shelf Wise
    public static function checkItemStockStatus($documentHeaderId, $documentDetailId, $itemId, $selectedAttr, $quantity, $storeId, $rackId, $shelfId, $binId, $bookType, $transactionType)
    {
        $user = Helper::getAuthenticatedUser();
        $flag = true; // Flag to track if we can proceed with changes
        $code = 200;
        $status = 'success';
        $message = 'Item is changeable as it is unutilized.';
        $detail = '';
        $attributeGroups = ErpAttribute::whereIn('id', $selectedAttr)->pluck('attribute_group_id');

        // Check if the stock is already utilized
        $isExistStockLedger = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id', $documentHeaderId)
            ->where('document_detail_id', $documentDetailId)
            ->where('item_id', $itemId)
            ->where('book_type', $bookType)
            ->where('transaction_type', $transactionType)
            ->whereIn('document_status', ['approved','posted','approval_not_required'])
            ->whereNotNull('utilized_id')
            ->first();
        if (!$isExistStockLedger) {
            // If the stock is utilized, return an error
            $detail = "Item";
            $flag = false;
        }

        // Check if specific attributes are used
        if ($selectedAttr && $flag) {
            $stockWithAttributes = StockLedger::withDefaultGroupCompanyOrg()
                ->where('document_header_id', $documentHeaderId)
                ->where('document_detail_id', $documentDetailId)
                ->where('item_id', $itemId)
                ->where('book_type', $bookType)
                ->where('transaction_type', $transactionType)
                ->whereIn('document_status', ['approved','posted','approval_not_required'])
                ->whereHas('attributes', function ($query) use ($selectedAttr, $attributeGroups) {
                    $query->whereIn('stock_ledger_item_attributes.attribute_value', $selectedAttr)
                        ->whereIn('stock_ledger_item_attributes.attribute_name', $attributeGroups)
                        ->groupBy('stock_ledger.id')
                        ->havingRaw('COUNT(DISTINCT stock_ledger_item_attributes.attribute_value) = ?', [count(@$selectedAttr)]);
                })
                ->first();

            if (!$stockWithAttributes) {
                $detail = "Attributes";
                $flag = false;
            }
        }

        // Filters for Store, Rack, Shelf, and Bin
        if ($flag && $storeId) {
            $stockWithStore = StockLedger::withDefaultGroupCompanyOrg()
                ->where('document_header_id', $documentHeaderId)
                ->where('document_detail_id', $documentDetailId)
                ->where('item_id', $itemId)
                ->where('store_id', $storeId)
                ->whereIn('document_status', ['approved','posted','approval_not_required'])
                ->first();

            if (!$stockWithStore) {
                $detail = "Store";
                $flag = false;
            }
        }

        if ($flag && $rackId) {
            $stockWithRack = StockLedger::withDefaultGroupCompanyOrg()
                ->where('document_header_id', $documentHeaderId)
                ->where('document_detail_id', $documentDetailId)
                ->where('item_id', $itemId)
                ->where('rack_id', $rackId)
                ->whereIn('document_status', ['approved','posted','approval_not_required'])
                ->first();

            if (!$stockWithRack) {
                $detail = "Rack";
                $flag = false;
            }
        }

        if ($flag && $shelfId) {
            $stockWithShelf = StockLedger::withDefaultGroupCompanyOrg()
                ->where('document_header_id', $documentHeaderId)
                ->where('document_detail_id', $documentDetailId)
                ->where('item_id', $itemId)
                ->where('shelf_id', $shelfId)
                ->whereIn('document_status', ['approved','posted','approval_not_required'])
                ->first();

            if (!$stockWithShelf) {
                $detail = "Shelf";
                $flag = false;
            }
        }

        if ($flag && $binId) {
            $stockWithBin = StockLedger::withDefaultGroupCompanyOrg()
                ->where('document_header_id', $documentHeaderId)
                ->where('document_detail_id', $documentDetailId)
                ->where('item_id', $itemId)
                ->where('bin_id', $binId)
                ->whereIn('document_status', ['approved','posted','approval_not_required'])
                ->first();

            if (!$stockWithBin) {
                $detail = "Bin";
                $flag = false;
            }
        }

        // Return message based on flag
        if (!$flag) {
            return [
                'code' => 200,
                'status' => 'error',
                'message' => "$detail cannot be changed as it has already been utilized."
            ];
        }

        return [
            'code' => $code,
            'status' => $status,
            'message' => $message
        ];
    }

    // Check Stock Status Item/Attributes/Store/Rack/Bin/Shelf Wise
    public static function checkItemStockQuantity($documentHeaderId, $documentDetailId, $itemId, $selectedAttr, $quantity, $storeId, $rackId, $shelfId, $binId, $bookType, $transactionType)
    {
        $user = Helper::getAuthenticatedUser();
        $flag = true; // Flag to track if we can proceed with changes
        $code = 200;
        $status = 'success';
        $message = '';
        $approvedStock = 0.00;
        $attributeGroups = ErpAttribute::whereIn('id', $selectedAttr)->pluck('attribute_group_id');
        if($quantity){
            // dd($quantity);
            $stockWithQuantity = StockLedger::query()
                ->where('organization_id', $user->organization_id)
                ->where('document_header_id', $documentHeaderId)
                ->where('document_detail_id', $documentDetailId)
                ->where('book_type', $bookType)
                ->where('transaction_type', $transactionType)
                ->whereIn('document_status', ['approved','posted','approval_not_required'])
                ->whereNotNull('utilized_id')
                ->select([
                    'stock_ledger.*',
                    DB::raw('SUM(receipt_qty) as total_receipt_qty')
                ])
                ->groupBy([
                    'item_id',
                    'document_header_id',
                    'document_detail_id',
                ])
                ->first();
            // dd($stockWithQuantity->toArray());

            if($stockWithQuantity){
                $approvedStock = $stockWithQuantity->total_receipt_qty;
                $bal = (int)$quantity - (int)$approvedStock;
                // dd([$quantity, $approvedStock, $bal]);
                if($bal < 0){
                    $status = 'error';
                    $message = 'Quantity can not be less than approved stock qty. which is '.$approvedStock;
                }
            }
        }

        return [
            'code' => $code,
            'status' => $status,
            'message' => $message,
            'approvedStock' => $approvedStock,
        ];
    }

    // Check Stock Status Item/Attributes/Store/Rack/Bin/Shelf Wise
    public static function checkIssueStockQuantity($documentHeaderId, $documentDetailId, $itemId, $selectedAttr, $quantity, $storeId, $rackId, $shelfId, $binId, $bookType, $transactionType)
    {
        $user = Helper::getAuthenticatedUser();
        $flag = true; // Flag to track if we can proceed with changes
        $code = 200;
        $status = 'success';
        $message = '';
        $approvedStock = 0.00;
        $attributeGroups = ErpAttribute::whereIn('id', $selectedAttr)->pluck('attribute_group_id');
        if($quantity){
            // dd($quantity);
            $stockWithQuantity = StockLedger::query()
                ->where('organization_id', $user->organization_id)
                ->where('document_header_id', $documentHeaderId)
                ->where('document_detail_id', $documentDetailId)
                ->where('book_type', $bookType)
                ->where('transaction_type', $transactionType)
                ->whereIn('document_status', ['approved','posted','approval_not_required'])
                ->whereNotNull('utilized_id')
                ->select([
                    'stock_ledger.*',
                    DB::raw('SUM(receipt_qty) as total_receipt_qty'),
                    DB::raw('SUM(total_cost) as total_item_cost')
                ])
                ->groupBy([
                    'item_id',
                    'document_header_id',
                    'document_detail_id',
                ])
                ->first();
            // dd($stockWithQuantity->toArray());

            if($stockWithQuantity){
                $approvedStock = $stockWithQuantity->total_receipt_qty;
                $bal = (int)$quantity - (int)$approvedStock;
                // dd([$quantity, $approvedStock, $bal]);
                if($bal < 0){
                    $status = 'error';
                    $message = 'Quantity can not be less than approved stock qty. which is '.$approvedStock;
                }
            }
        }

        return [
            'code' => $code,
            'status' => $status,
            'message' => $message,
            'approvedStock' => $approvedStock,
        ];
    }

    // Check Stock Status Item/Attributes/Store/Rack/Bin/Shelf Wise
    public static function changeIssueQuantity($documentHeaderId, $documentDetailId, $itemId, $quantity, $bookType, $transactionType)
    {
        $user = Helper::getAuthenticatedUser();
        $flag = true; // Flag to track if we can proceed with changes
        $code = 200;
        $status = 'success';
        $message = 'Quantity Updated Successfuly.';
        $stockQty = 0;
        $records = '';

        // Check if the stock is already utilized
        $stockLedgers = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id', $documentHeaderId)
            ->where('document_detail_id', $documentDetailId)
            ->where('book_type', $bookType)
            ->where('transaction_type', $transactionType)
            ->whereNull('utilized_id')
            ->get();

        if ($stockLedgers->isEmpty()) {
            return [
                'code' => 404,
                'status' => 'error',
                'message' => 'Stock ledger not found.',
                'records' => ''
            ];
        }

        foreach ($stockLedgers as $ledger) {
            $utilizedRecord = StockLedger::withDefaultGroupCompanyOrg()
                ->where('utilized_id', $ledger->id)
                ->first();

            if ($utilizedRecord) {
                $stockQty += $utilizedRecord->receipt_qty;
                $similarUtilizedRecord = StockLedger::withDefaultGroupCompanyOrg()
                    ->where('document_header_id', $utilizedRecord->document_header_id)
                    ->where('document_detail_id', $utilizedRecord->document_detail_id)
                    ->where('book_type', $utilizedRecord->book_type)
                    ->where('transaction_type', $utilizedRecord->transaction_type)
                    ->whereNull('utilized_id')
                    ->first();

                if ($similarUtilizedRecord) {
                    // Merge quantities and reset utilization
                    $stockQty += $similarUtilizedRecord->receipt_qty;
                }
            }
        }

        if($quantity > $stockQty){
            return [
                'code' => 404,
                'status' => 'error',
                'message' => 'Quantity not available in stock ledger.',
                'records' => ''
            ];
        } else{
            $documentHeaderIds = $stockLedgers->pluck('document_header_id');
            $documentDetailIds = $stockLedgers->pluck('document_detail_id');
            $utilizedStockLedgers = StockLedger::select(
                [
                    'stock_ledger.*',
                    DB::raw('SUM(receipt_qty) as total_receipt_qty'),
                ]
            )
            ->where('organization_id', $user->organization_id)
            ->whereIn('document_header_id', $documentHeaderIds)
            ->whereIn('document_detail_id', $documentDetailIds)
            // ->orderBy('id', 'DESC')
            ->groupBy([
                'stock_ledger.document_header_id',
                'stock_ledger.document_detail_id',
                'stock_ledger.item_id',
                'stock_ledger.store_id',
                'stock_ledger.rack_id',
                'stock_ledger.shelf_id',
                'stock_ledger.bin_id'
            ])
            ->get();

            $remainingQuantity = $quantity;
            $lifoBreakup = [];
            $data = array();
            foreach ($utilizedStockLedgers as $stockSummary) {
                $availableQty = $stockSummary->total_receipt_qty;

                if ($availableQty <= 0) {
                    continue; // Skip if no available quantity
                }

                // Allocate quantity
                $allocatedQty = min($availableQty, $remainingQuantity);
                $lifoBreakup[] = [
                    'item_id' => $stockSummary->item_id,
                    'item_name' => $stockSummary->item_name,
                    'item_code' => $stockSummary->item_code,
                    'store_id' => $stockSummary->store_id,
                    'store' => $stockSummary->store,
                    'rack_id' => $stockSummary->rack_id,
                    'rack' => $stockSummary->rack,
                    'shelf_id' => $stockSummary->shelf_id,
                    'shelf' => $stockSummary->shelf,
                    'bin_id' => $stockSummary->bin_id,
                    'bin' => $stockSummary->bin,
                    'allocated_quantity' => $allocatedQty
                ];

                // Decrease remaining quantity
                $remainingQuantity -= $allocatedQty;

                // Stop if we've fulfilled the required quantity
                if ($remainingQuantity <= 0) {
                    break;
                }
            }

            // If remaining quantity is still greater than 0, it means not enough stock was available
            if ($remainingQuantity > 0) {
                $code = 202;
                $status = 'error';
                $message = 'Not enough stock available to fulfill the order quantity';
                $records = '';
            } else{
                $code = 200;
                $status = 'success';
                $message = 'Record fetched successfuly.';
                $records = $lifoBreakup;
            }
        }

        return [
            'code' => $code,
            'status' => $status,
            'message' => $message,
            'records' => $records
        ];
    }

    // Get Quantity In Issue Stock
    private static function quantityBasedIssueStock($StockLedger, $quantity)
    {
        $user = Helper::getAuthenticatedUser();

        $availableStockData = StockLedger::withDefaultGroupCompanyOrg()
            ->whereIn('document_header_id', $StockLedger['documentHeaderIds'])
            ->where('document_detail_id', $StockLedger['documentDetailIds'])
            ->where('transaction_type', 'receipt')
            ->whereNull('utilized_id')
            ->orderBy(['id', 'document_date'], 'DESC')
            ->get();


        $remainingQuantity = $quantity;
        $lifoBreakup = [];
        $data = array();
        foreach ($availableStockData as $stockSummary) {
            $availableQty = $stockSummary->receipt_qty;

            if ($availableQty <= 0) {
                continue; // Skip if no available quantity
            }

            // Allocate quantity
            $allocatedQty = min($availableQty, $remainingQuantity);
            $lifoBreakup[] = [
                'item_id' => $stockSummary->item_id,
                'item_name' => $stockSummary->item_name,
                'item_code' => $stockSummary->item_code,
                'store_id' => $stockSummary->store_id,
                'store' => $stockSummary->store,
                'rack_id' => $stockSummary->rack_id,
                'rack' => $stockSummary->rack,
                'shelf_id' => $stockSummary->shelf_id,
                'shelf' => $stockSummary->shelf,
                'bin_id' => $stockSummary->bin_id,
                'bin' => $stockSummary->bin,
                'allocated_quantity' => $allocatedQty
            ];

            // Decrease remaining quantity
            $remainingQuantity -= $allocatedQty;

            // Stop if we've fulfilled the required quantity
            if ($remainingQuantity <= 0) {
                break;
            }
        }

        // If remaining quantity is still greater than 0, it means not enough stock was available
        if ($remainingQuantity > 0) {
            $code = 202;
            $status = 'error';
            $message = 'Not enough stock available to fulfill the order quantity';
            $records = '';
        } else{
            $code = 200;
            $status = 'success';
            $message = 'Record fetched successfuly.';
            $records = $lifoBreakup;
        }
    }

    public static function updateIssueStock($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType)
    {

        $user = Helper::getAuthenticatedUser();
        $message = '';
        $transactionType = 'issue';
        $checkInvoiceLedger = self::updateIssueStockLedger($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType);

    }

    // Get Quantity In Issue Stock
    private static function updateIssueStockLedger($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType)
    {
        $user = Helper::getAuthenticatedUser();

        $documentItemLocations = ErpInvoiceItemLocation::where('sale_invoice_id',$documentHeaderId)
            ->where('invoice_item_id',$documentDetailId)
            ->with(
                'header',
                'detail',
                'detail.item',
                'detail.attributes'
            )
            ->get();

        if(isset($documentItemLocations) && $documentItemLocations){
            foreach ($documentItemLocations as $documentItemLocation) {
                $oldStockLedger = StockLedger::withDefaultGroupCompanyOrg()
                    ->where('document_header_id', $documentItemLocation->document_header_id)
                    ->where('document_detail_id', $documentItemLocation->document_detail_id)
                    ->where('item_id', $documentItemLocation->item_id)
                    ->where('book_type', $documentItemLocation->book_type)
                    ->where('transaction_type', $documentItemLocation->transaction_type)
                    ->whereNull('utilized_id')
                    ->first();

                if ($oldStockLedger) {
                    $oldStockLedger->issue_qty = $documentItemLocation->quantity;
                    $oldStockLedger->save();
                } else {
                    // Reset utilization for the utilized record
                }
            }
        }
    }
    public static function addReturnedStock($documentHeaderId, $documentDetailId, $itemId, $bookType, $transactionType)
    {
        $user = Helper::getAuthenticatedUser();
        $code = 200;
        $status = 'success';
        $message = 'Stock ledger successfully updated.';

        // Get stock ledgers for the specified filters
        $stockLedgers = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id', $documentHeaderId)
            ->where('document_detail_id', $documentDetailId)
            ->where('item_id', $itemId)
            ->where('book_type', $bookType)
            ->where('transaction_type', $transactionType)
            ->whereNull('utilized_id')
            ->orderBy('id')
            ->get();

        if ($stockLedgers->isEmpty()) {
            return [
                'code' => 404,
                'status' => 'error',
                'message' => 'Stock ledger not found.'
            ];
        }

        // Store IDs of ledgers and attributes to delete
        $ledgerIdsToRestore = [];
        $attributeIdsToRestore = [];

        foreach ($stockLedgers as $ledger) {
            $utilizedRecord = StockLedger::withDefaultGroupCompanyOrg()
                ->where('utilized_id', $ledger->id)
                ->first();

            if ($utilizedRecord) {
                $similarUtilizedRecord = StockLedger::withDefaultGroupCompanyOrg()
                    ->where('document_header_id', $utilizedRecord->document_header_id)
                    ->where('document_detail_id', $utilizedRecord->document_detail_id)
                    ->where('item_id', $utilizedRecord->item_id)
                    ->where('book_type', $utilizedRecord->book_type)
                    ->where('transaction_type', $utilizedRecord->transaction_type)
                    ->whereNull('utilized_id')
                    ->first();

                if ($similarUtilizedRecord) {
                    // Merge quantities and reset utilization
                    $utilizedRecord->receipt_qty += $similarUtilizedRecord->receipt_qty;
                    $utilizedRecord->utilized_id = null;
                    $utilizedRecord->utilized_date = null;
                    $utilizedRecord->save();

                    // Add to batch delete
                    $ledgerIdsToRestore[] = $similarUtilizedRecord->id;
                    $attributeIdsToRestore = array_merge($attributeIdsToRestore, $similarUtilizedRecord->attributes()->pluck('id')->toArray());
                } else {
                    // Reset utilization for the utilized record
                    $utilizedRecord->utilized_id = null;
                    $utilizedRecord->utilized_date = null;
                    $utilizedRecord->save();
                }

                // Add current ledger and attributes to batch delete
                $ledgerIdsToRestore[] = $ledger->id;
                $attributeIdsToRestore = array_merge($attributeIdsToRestore, $ledger->attributes()->pluck('id')->toArray());
            } else {
                $status = 'error';
                $message = 'Utilized record not found.';
                break;
            }
        }

        // Delete all gathered attributes and ledgers in batch
        if (!empty($attributeIdsToRestore)) {
            StockLedgerItemAttribute::whereIn('id', $attributeIdsToRestore)->restore();
        }
        if (!empty($ledgerIdsToRestore)) {
            StockLedger::whereIn('id', $ledgerIdsToRestore)->restore();
        }

        return [
            'code' => $code,
            'status' => $status,
            'message' => $message
        ];

    }

    public static function getAccessibleLocations(string $locationType = NULL, $storeId = NULL)
    {
        //Retrieve Editable Store
        $editStore = ErpStore::with('address') -> find($storeId);
        $stores = ErpStore::withDefaultGroupCompanyOrg()
        ->withWhereHas('address')
        ->where(function($query) use($storeId) {
            $query->where('status',ConstantHelper::ACTIVE);
            if($storeId) {
                $query->orWhere('id', $storeId);
            }
        })
        ->when($locationType, function ($typeQuery) use($locationType) {
            $typeQuery = $typeQuery -> where('store_location_type', $locationType);
        })
        ->when(isset($editStore), function ($storeQuery) use($editStore) { // Location with same country and state
            $storeQuery -> whereHas('address', function ($addressQuery) use($editStore) {
                $addressQuery -> where('country_id', $editStore -> address ?-> country_id)
                -> where('state_id', $editStore -> address ?-> state_id);
            });
        })
        ->get();
        return $stores;
    }

    // Settlement For MRN (Receive)
    private static function settlementForMRN($documentHeaderId, $documentDetailId, $bookType, $documentStatus)
    {
        $user = Helper::getAuthenticatedUser();

        $transactionType = 'receipt';
        $documentItemLocations = MrnItemLocation::where('mrn_header_id',$documentHeaderId)
            ->whereIn('mrn_detail_id',$documentDetailId)
            ->with('mrnHeader',
                'mrnDetail',
                'mrnDetail.item',
                'mrnDetail.attributes',
                'erpStore',
                'erpRack',
                'erpShelf',
                'erpBin'
            )
            ->get();

        $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id',$documentHeaderId)
            ->whereIn('document_detail_id',$documentDetailId)
            ->where('book_type','=',$bookType)
            ->where('transaction_type','=',$transactionType)
            // ->where('document_status','draft')
            ->whereNull('utilized_id')
            ->get();

        foreach($stockLedger as $val){
            StockLedgerItemAttribute::where('stock_ledger_id', $val->id)->delete();
            $val->delete();
        }

        foreach ($documentItemLocations as $documentItemLocation) {
            $utilizedQty = StockLedger::withDefaultGroupCompanyOrg()
                ->where('document_header_id',$documentHeaderId)
                ->where('document_detail_id',$documentDetailId)
                ->where('book_type','=',$bookType)
                ->where('transaction_type','=',$transactionType)
                ->where('document_status','draft')
                ->whereNotNull('utilized_id')
                ->sum('receipt_qty');

            if($documentItemLocation->inventory_uom_qty > $utilizedQty){
                $stockLedger = new StockLedger();
                $invoiceLedger = self::insertStockLedger($stockLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType, $utilizedQty);
            }
        }

        $message = 'success';
        return $message;
    }

    // Settlement For Sale Return (Receive)
    private static function settlementForSaleReturn($documentHeaderId, $documentDetailId, $bookType, $documentStatus)
    {
        $user = Helper::getAuthenticatedUser();

        $transactionType = 'receipt';
        $documentItemLocations = ErpSaleReturnItemLocation::where('sale_return_id',$documentHeaderId)
            ->whereIn('sale_return_item_id',$documentDetailId)
            ->with('header',
                'detail',
                'detail.item',
                'detail.attributes',
                'erpStore',
                'erpRack',
                'erpShelf',
                'erpBin'
            )
            ->get();

        $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id',$documentHeaderId)
            ->whereIn('document_detail_id',$documentDetailId)
            ->where('book_type','=',$bookType)
            ->where('transaction_type','=',$transactionType)
            // ->where('document_status','draft')
            ->whereNull('utilized_id')
            ->get();

        foreach($stockLedger as $val){
            StockLedgerItemAttribute::where('stock_ledger_id', $val->id)->delete();
            $val->delete();
        }

        foreach ($documentItemLocations as $documentItemLocation) {
            $utilizedQty = StockLedger::withDefaultGroupCompanyOrg()
                ->where('document_header_id',$documentHeaderId)
                ->where('document_detail_id',$documentDetailId)
                ->where('book_type','=',$bookType)
                ->where('transaction_type','=',$transactionType)
                ->where('document_status','draft')
                ->whereNotNull('utilized_id')
                ->sum('receipt_qty');

            if($documentItemLocation->inventory_uom_qty > $utilizedQty){
                $stockLedger = new StockLedger();
                $invoiceLedger = self::insertStockLedger($stockLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType, $utilizedQty);
            }
        }

        $message = 'success';
        return $message;
    }

    // Settlement For Packing Slip (Receive)
    private static function settlementForProductionSlip($documentHeaderId, $documentDetailId, $bookType, $documentStatus)
    {
        $user = Helper::getAuthenticatedUser();

        $transactionType = 'receipt';
        $documentItemLocations = ErpPslipItemLocation::where('pslip_id',$documentHeaderId)
            ->whereIn('pslip_item_id',$documentDetailId)
            ->with('header',
                'detail',
                'detail.item',
                'detail.attributes',
                'erpStore',
                'erpRack',
                'erpShelf',
                'erpBin'
            )
            ->get();

        $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id',$documentHeaderId)
            ->whereIn('document_detail_id',$documentDetailId)
            ->where('book_type','=',$bookType)
            ->where('transaction_type','=',$transactionType)
            // ->where('document_status','draft')
            ->whereNull('utilized_id')
            ->get();

        foreach($stockLedger as $val){
            StockLedgerItemAttribute::where('stock_ledger_id', $val->id)->delete();
            $val->delete();
        }

        foreach ($documentItemLocations as $documentItemLocation) {
            $utilizedQty = StockLedger::withDefaultGroupCompanyOrg()
                ->where('document_header_id',$documentHeaderId)
                ->where('document_detail_id',$documentDetailId)
                ->where('book_type','=',$bookType)
                ->where('transaction_type','=',$transactionType)
                ->where('document_status','draft')
                ->whereNotNull('utilized_id')
                ->sum('receipt_qty');

            if($documentItemLocation->inventory_uom_qty > $utilizedQty){
                $stockLedger = new StockLedger();
                $invoiceLedger = self::insertStockLedger($stockLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType, $utilizedQty);
            }
        }

        $message = 'success';
        return $message;
    }

    // Settlement For Sale Invoice (Issue)
    private static function settlementForSaleInvoice($documentHeaderId, $documentDetailId, $bookType, $documentStatus)
    {
        $user = Helper::getAuthenticatedUser();

        $transactionType = 'issue';
        $documentItemLocations = ErpInvoiceItem::where('sale_invoice_id',$documentHeaderId)
            ->whereIn('id',$documentDetailId)
            ->with('header',
                'item',
                'attributes'
            )
            ->get();

        if(isset($documentItemLocations) && $documentItemLocations){
            foreach ($documentItemLocations as $documentItemLocation) {
                $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
                    ->where('document_header_id',$documentHeaderId)
                    ->where('document_detail_id',$documentDetailId)
                    ->where('book_type','=',$bookType)
                    ->first();
                if(!$stockLedger){
                    $stockLedger = new StockLedger();
                }
                $utilizedQty = 0;
                $issueQty = $stockLedger->issue_qty;
                $invoiceLedger = self::insertStockLedger($stockLedger, $documentItemLocation,  $bookType, $documentStatus, $transactionType, $utilizedQty);
                $updatedInvoiceLedger = self::updateStockLedger($invoiceLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType, $issueQty);
            }
        }

        $message = 'success';
        return $message;
    }

    // Settlement For Purchase Return (Issue)
    private static function settlementForPurchaseReturn($documentHeaderId, $documentDetailId, $bookType, $documentStatus)
    {
        $user = Helper::getAuthenticatedUser();

        $transactionType = 'issue';
        $documentItems = PRDetail::where('header_id',$documentHeaderId)
            ->with('header',
                'item',
                'attributes'
            )
            ->get();

        if(isset($documentItems) && $documentItems){
            foreach ($documentItems as $documentItem) {
                $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
                    ->where('document_header_id',$documentHeaderId)
                    ->where('document_detail_id',$documentItem->id)
                    ->where('book_type','=',$bookType)
                    ->first();
                if(!$stockLedger){
                    $stockLedger = new StockLedger();
                }
                $utilizedQty = 0;
                $issueQty = $stockLedger->issue_qty;
                $invoiceLedger = self::insertStockLedger($stockLedger, $documentItem,  $bookType, $documentStatus, $transactionType, $utilizedQty);
                $updatedInvoiceLedger = self::updateStockLedger($invoiceLedger, $documentItem, $bookType, $documentStatus, $transactionType, $issueQty);
            }
        }

        $message = 'success';
        return $message;
    }

    // Settlement For MO (Issue)
    private static function settlementForMO($documentHeaderId, $documentDetailId, $bookType, $documentStatus)
    {
        $user = Helper::getAuthenticatedUser();

        $transactionType = 'issue';
        $documentItems = MoItem::where('mo_id',$documentHeaderId)
            ->with('mo',
                'item',
                'attributes'
            )
            ->get();

        if(isset($documentItems) && $documentItems){
            foreach ($documentItems as $documentItem) {
                $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
                    ->where('document_header_id',$documentHeaderId)
                    ->where('document_detail_id',$documentItem->id)
                    ->where('book_type','=',$bookType)
                    ->first();
                if(!$stockLedger){
                    $stockLedger = new StockLedger();
                }
                $utilizedQty = 0;
                $issueQty = $stockLedger->issue_qty;
                $invoiceLedger = self::insertStockLedger($stockLedger, $documentItem,  $bookType, $documentStatus, $transactionType, $utilizedQty);
                $updatedInvoiceLedger = self::updateStockLedger($invoiceLedger, $documentItem, $bookType, $documentStatus, $transactionType, $issueQty);
            }
        }

        $message = 'success';
        return $message;
    }

    // Settlement For MO (Receive)
    private static function settlementForMOReceipt($documentHeaderId, $documentDetailId, $bookType, $documentStatus)
    {
        $user = Helper::getAuthenticatedUser();

        $transactionType = 'receipt';
        $documentItemLocations = MoProductionItemLocation::where('mo_id', $documentHeaderId)
            ->whereIn('mo_production_item_id', $documentDetailId)
            ->with('header',
                'detail',
                'detail.item',
                'detail.productionAttributes',
                'erpStore',
                'erpRack',
                'erpShelf',
                'erpBin'
            )
            ->get();

        $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id', $documentHeaderId)
            ->whereIn('document_detail_id', $documentDetailId)
            ->where('book_type','=',$bookType)
            ->where('transaction_type','=',$transactionType)
            // ->where('document_status','draft')
            ->whereNull('utilized_id')
            ->get();

        foreach($stockLedger as $val){
            StockLedgerItemAttribute::where('stock_ledger_id', $val->id)->delete();
            $val->delete();
        }

        foreach ($documentItemLocations as $documentItemLocation) {
            $utilizedQty = StockLedger::withDefaultGroupCompanyOrg()
                ->where('document_header_id',$documentHeaderId)
                ->where('document_detail_id',$documentDetailId)
                ->where('book_type','=',$bookType)
                ->where('transaction_type','=',$transactionType)
                ->where('document_status','draft')
                ->whereNotNull('utilized_id')
                ->sum('receipt_qty');

            if($documentItemLocation->inventory_uom_qty > $utilizedQty){
                $stockLedger = new StockLedger();
                $invoiceLedger = self::insertStockLedger($stockLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType, $utilizedQty);
            }
        }

        $message = 'success';
        return $message;
    }

    // Settlement For Material Issue For Issue
    private static function settlementForMIForIssue($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType)
    {
        $user = Helper::getAuthenticatedUser();

        $documentItems = ErpMiItem::where('material_issue_id',$documentHeaderId)
            ->with('header',
                'item',
                'attributes'
            )
            ->get();

        if(isset($documentItems) && $documentItems){
            foreach ($documentItems as $documentItem) {
                if($documentItem->from_store_id){
                    $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
                        ->where('document_header_id',$documentHeaderId)
                        ->where('document_detail_id',$documentItem->id)
                        ->where('book_type','=',$bookType)
                        ->first();
                    if(!$stockLedger){
                        $stockLedger = new StockLedger();
                    }
                    $utilizedQty = 0;
                    $issueQty = $stockLedger->issue_qty;
                    $invoiceLedger = self::insertStockLedger($stockLedger, $documentItem,  $bookType, $documentStatus, $transactionType, $utilizedQty);
                    $updatedInvoiceLedger = self::updateStockLedger($invoiceLedger, $documentItem, $bookType, $documentStatus, $transactionType, $issueQty);
                }
            }
        }

        $message = 'success';
        return $message;
    }

    // Settlement For Material Issue For Receive
    private static function settlementForMIForReceive($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType)
    {
        $user = Helper::getAuthenticatedUser();

        $documentItemLocations = ErpMiItemLocation::where('material_issue_id',$documentHeaderId)
            ->whereIn('mi_item_id',$documentDetailId)
            ->where('type', 'to')
            ->get();

        $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
            ->where('document_header_id',$documentHeaderId)
            ->whereIn('document_detail_id',$documentDetailId)
            ->where('book_type','=',$bookType)
            ->where('transaction_type','=',$transactionType)
            ->where('document_status','draft')
            ->whereNull('utilized_id')
            ->get();

        foreach($stockLedger as $val){
            StockLedgerItemAttribute::where('stock_ledger_id', $val->id)->delete();
            $val->delete();
        }

        foreach ($documentItemLocations as $documentItemLocation) {
            $utilizedQty = StockLedger::withDefaultGroupCompanyOrg()
                ->where('document_header_id',$documentHeaderId)
                ->where('document_detail_id',$documentDetailId)
                ->where('book_type','=',$bookType)
                ->where('transaction_type','=',$transactionType)
                // ->where('document_status','draft')
                ->whereNotNull('utilized_id')
                ->sum('receipt_qty');
            if($documentItemLocation->inventory_uom_qty > $utilizedQty){
                $issueStockLedger = StockLedger::withDefaultGroupCompanyOrg()
                    ->where('document_header_id',$documentHeaderId)
                    ->where('document_detail_id',$documentDetailId)
                    ->where('book_type','=',$bookType)
                    ->where('transaction_type','=','issue')
                    ->first();
                if($issueStockLedger){
                    $utilizedStockLedger = StockLedger::withDefaultGroupCompanyOrg()
                        ->where('utilized_id',$issueStockLedger->id)
                        ->where('transaction_type','=','receipt')
                        ->get();
                    if(!empty($utilizedStockLedger)){
                        foreach($utilizedStockLedger as $utlStockLedger){
                            $stockLedger = new StockLedger();
                            $invoiceLedger = self::insertStockLedger($stockLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType, $utilizedQty, $utlStockLedger);
                        }
                    }    
                }   
            }
        }

        $message = 'success';
        return $message;
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

        return "success";
    }

}