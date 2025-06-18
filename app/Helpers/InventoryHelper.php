<?php
namespace App\Helpers;

use App\Models\ErpPsvHeader;
use App\Models\ErpPsvItem;
use DB;
use Auth;

use App\Models\ErpSubStore;
use App\Models\ErpSubStoreParent;
use App\Models\ErpMrItem;
use App\Models\ErpMrItemLocation;
use App\Models\ErpMaterialReturnHeader;

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
use App\Models\ErpSrItemLotDetail;
use App\Models\ErpSaleReturnItemLocation;
use App\Models\ErpSaleReturnItemAttribute;

use App\Models\PRHeader;
use App\Models\PRDetail;
use App\Models\PRItemLocation;
use App\Models\PRItemAttribute;

use App\Models\ErpMiItem;
use App\Models\ErpMrItemLot;
use App\Models\ErpMiItemLocation;
use App\Models\ErpMiItemAttribute;
use App\Models\ErpMaterialIssueHeader;

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

use App\Models\ErpSoItem;
use Illuminate\Support\Collection;
use App\Models\PslipBomConsumption;
use Illuminate\Support\Facades\Log;
use stdClass;
class InventoryHelper
{
    public function __construct()
    {

    }

    const STOCK_TYPE_REGULAR = 'R';
    const STOCK_TYPE_REGULAR_LABEL = 'Regular';
    const STOCK_TYPE_WIP = 'W';
    const STOCK_TYPE_WIP_LABEL = 'WIP';
    const STOCK_TYPES = [
        ['label' => self::STOCK_TYPE_REGULAR_LABEL, 'value' => self::STOCK_TYPE_REGULAR],
        ['label' => self::STOCK_TYPE_WIP_LABEL, 'value' => self::STOCK_TYPE_WIP],
    ];

    public static function settlementOfInventoryAndStock($documentHeaderId, $documentDetailId=NULL, $bookType, $documentStatus, $transactionType = NULL, $stockReservation = NULL)
    {
        $user = Helper::getAuthenticatedUser();
        $message = '';
        $transactionType = $transactionType ?? '';
        $data = array();
        $records = array();
        if($bookType == ConstantHelper::MRN_SERVICE_ALIAS){
            $documentDetail = self::settlementForMRN($documentHeaderId, $documentDetailId, $bookType, $documentStatus);
            $records = array();
            $message = $documentDetail;
        }
        else if($bookType == ConstantHelper::SR_SERVICE_ALIAS){
            $documentDetail = self::settlementForSaleReturn($documentHeaderId, $documentDetailId, $bookType, $documentStatus);
            $records = array();
            $message = $documentDetail;
        }
        else if($bookType == ConstantHelper::PSV_SERVICE_ALIAS){
            if($transactionType == 'issue')
            {

                $documentDetail = self::settlementForPsvForIssue($documentHeaderId, $documentDetailId, $bookType, $documentStatus,$transactionType);
                $message = $documentDetail;
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
                    'sub_store_id',
                    'lot_number',
                    DB::raw('SUM(receipt_qty) as total_receipt_qty'),
                    DB::raw('SUM(org_currency_cost) as total_org_currency_cost')
                ])
                ->groupBy([
                    'utilized_id',
                    'store_id',
                    'sub_store_id'
                ])
                ->get();
            }
            else{
                $documentDetail = self::settlementForPsvForReceive($documentHeaderId, $documentDetailId, $bookType, $documentStatus,$transactionType);
                $records = array();
                $message = $documentDetail;
            }
        }
        else if($bookType == ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS){
            $documentDetail = self::settlementForPurchaseReturn($documentHeaderId, $documentDetailId, $bookType, $documentStatus);
            $message = $documentDetail;
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
                    'sub_store_id',
                    'lot_number',
                    DB::raw('SUM(receipt_qty) as total_receipt_qty'),
                    DB::raw('SUM(org_currency_cost) as total_org_currency_cost')
                ])
                ->groupBy([
                    'utilized_id',
                    'store_id',
                    'sub_store_id'
                ])
                ->get();

        }
        else if($bookType == ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS){

            if($transactionType == 'issue') {
                $documentDetail = self::settlementForPslip($documentHeaderId, $documentDetailId, $bookType, $documentStatus);
                $message = $documentDetail;
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
                        'sub_store_id',
                        'lot_number',
                        DB::raw('SUM(receipt_qty) as total_receipt_qty'),
                        DB::raw('SUM(org_currency_cost) as total_org_currency_cost')
                    ])
                    ->groupBy([
                        'utilized_id',
                        'store_id',
                        'sub_store_id'
                    ])
                    ->get();
            }
            if($transactionType == 'receipt') {
                $documentDetail = self::settlementForPslipReceipt($documentHeaderId, $documentDetailId, $bookType, $documentStatus);
                $records = array();
                $documentDetail;
            }

        }
        else if($bookType == ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS){
            $stockReservation = 'yes';
            $documentDetail = self::settlementForSaleInvoice($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $stockReservation);
            $message = $documentDetail;
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
                    'sub_store_id',
                    'lot_number',
                    DB::raw('SUM(receipt_qty) as total_receipt_qty'),
                    DB::raw('SUM(org_currency_cost) as total_org_currency_cost')
                ])
                ->groupBy([
                    'utilized_id',
                    'store_id',
                    'sub_store_id'
                ])
                ->get();
        }
        else if($bookType == ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME){
            if($transactionType == 'issue'){
                $documentDetail = self::settlementForMIForIssue($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType);
                $message = $documentDetail;
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
                        'sub_store_id',
                        'lot_number',
                        DB::raw('SUM(receipt_qty) as total_receipt_qty'),
                        DB::raw('SUM(org_currency_cost) as total_org_currency_cost')
                    ])
                    ->groupBy([
                        'utilized_id',
                        'store_id',
                        'sub_store_id'
                    ])
                    ->get();
            }
            if($transactionType == 'receipt'){
                $documentDetail = self::settlementForMIForReceive($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType);
                $records = array();
                $message = $documentDetail;
            }
        }
        else if($bookType == ConstantHelper::MATERIAL_RETURN_SERVICE_ALIAS_NAME){
            if($transactionType == 'issue'){
                $documentDetail = self::settlementForMRForIssue($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType);
                $message = $documentDetail;
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
                        'sub_store_id',
                        'lot_number',
                        DB::raw('SUM(receipt_qty) as total_receipt_qty'),
                        DB::raw('SUM(org_currency_cost) as total_org_currency_cost')
                    ])
                    ->groupBy([
                        'utilized_id',
                        'store_id',
                        'sub_store_id'
                    ])
                    ->get();
            }
            if($transactionType == 'receipt'){
                $documentDetail = self::settlementForMRForReceive($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType);
                $records = array();
                $message = $documentDetail;
            }
        }
        else {
            $message = "Invalid Book Type";
            return self::errorResponse($message);
        }

        $data = self::successResponse($message, $records);
        return $data;
    }

    // Total Draft And Confirmed Stock
    public static function totalInventoryAndStock($itemId, $selectedAttr=null, $uomId=null, $storeId=null, $subStoreId=null, $orderId=null, $stationId = null, $stockType = self::STOCK_TYPE_REGULAR, $itemWipStationId = null)
    {
        $user = Helper::getAuthenticatedUser();
        $reservedStocks = 0.00;
        $reservedStockAltUom = 0.00;
        $attributeGroups = ErpAttribute::whereIn('id', $selectedAttr)->pluck('attribute_group_id');
        $stockLedger = StockLedger::withDefaultGroupCompanyOrg()
            ->with('details')
            ->where('item_id', $itemId)
            ->whereNull('utilized_id')
            ->whereNotNull('receipt_qty');

        // Apply attribute filtering if needed
        if (!empty($attributeGroups) && !empty($selectedAttr)) {
            sort($selectedAttr);
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
        if ($subStoreId) {
            $stockLedger->where('sub_store_id', $subStoreId);
        }
        if ($stationId) {
            $stockLedger->where('station_id', $stationId);
        }
        $stockLedger -> where('stock_type', $stockType);
        if ($itemWipStationId && $stockType === self::STOCK_TYPE_WIP) {
            $stockLedger -> where('wip_station_id', $itemWipStationId);
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

        if ($orderId) {
            $stocks = $reservedStocksQuery
                ->whereIn('document_status', ['approved', 'posted', 'approval_not_required'])
                ->with(['reservations' => function ($q) use ($orderId) {
                    $q->where('so_item_id', $orderId);
                }])
                ->get();

            foreach ($stocks as $stock) {
                $reservedStocks += $stock->reservations->sum('quantity');
            }
        }
        $rate = $stockLedger->pluck('cost_per_unit')->first();
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
            'rate' => $rate ?? 0,
        ];
        return $data;
    }

    // Fetch stock summary
    public static function fetchStockSummary($itemId, $selectedAttr=null, $uomId=null, $quantity, $storeId=null, $subStoreId=null, $stationId = null, $stockType = self::STOCK_TYPE_REGULAR, $itemWipStationId = null)
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
        if ($subStoreId) {
            $query->where('sub_store_id', $subStoreId);
        }
        if ($stationId) {
            $query->where('station_id', $stationId);
        }
        $query -> where('stock_type', $stockType);
        if ($itemWipStationId && $stockType === self::STOCK_TYPE_WIP) {
            $query -> where('wip_station_id', $itemWipStationId);
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
            'sub_store_id'
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
                    'sub_store_id' => $stockSummary->sub_store_id,
                    'sub_store' => $stockSummary->sub_store,
                    'lot_number' => $stockSummary->lot_number,
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
        $holdQty = 0.00;
        $lotNumber = null;

        // Receive
        if($bookType == ConstantHelper::MRN_SERVICE_ALIAS){
            $documentHeader = MrnHeader::find($documentItemLocation->mrn_header_id);
            $documentDetail = MrnDetail::with(['header', 'attributes'])->find($documentItemLocation->id);
            $stockLedger->book_id = @$documentHeader->book_id;
            if(!$documentItemLocation->inventory_uom_qty || $documentItemLocation->inventory_uom_qty < 1){
                $qty = 0.00;
                $holdQty = ItemHelper::convertToBaseUom($documentItemLocation->item_id, $documentItemLocation->uom_id, $documentItemLocation->order_qty);
                $stockLedger->receipt_qty = $qty;
                $stockLedger->hold_qty = $holdQty;
                $totalItemCost = $documentDetail->basic_value - ($documentDetail->discount_amount + $documentDetail->header_discount_amount);
                $costPerUnit = $totalItemCost/$holdQty;
            }else {
                $qty = ($documentItemLocation->inventory_uom_qty - $utilizedQty);
                $holdQty = 0.00;
                $stockLedger->receipt_qty = $qty;
                $stockLedger->hold_qty = $holdQty;
                $stockLedger->book_id = @$documentHeader->book_id;
                $totalItemCost = $documentDetail->basic_value - ($documentDetail->discount_amount + $documentDetail->header_discount_amount);
                $costPerUnit = $totalItemCost/$qty;
            }
            $stockLedger->vendor_id = @$documentHeader->vendor_id;
            $stockLedger->vendor_code = @$documentHeader->vendor_code;

            // Item Location Data
            $stockLedger->store_id = $documentItemLocation->store_id ?? null;
            $stockLedger->sub_store_id = $documentItemLocation->sub_store_id ?? null;
            $stockLedger->store = @$documentItemLocation->erpStore->store_code;
            $stockLedger->original_receipt_date = @$documentHeader->document_date;
            $stockLedger->lot_number = $documentItemLocation?->mrnHeader?->lot_number ?? null;
            $stockLedger->so_id = $documentItemLocation?->so_id ?? null;
        }

        // Receive
        if($bookType == ConstantHelper::SR_SERVICE_ALIAS){
            $qty = ($documentItemLocation->inventory_uom_qty - $utilizedQty);
            $documentLotDetail = ErpSrItemLotDetail::with(
                [
                    'detail',
                    'detail.header',
                    'detail.attributes'
                ]
            )
            ->find($documentItemLocation->id);
            $documentDetail = ErpSaleReturnItem::with(
                [
                    'header',
                    'attributes'
                ]
            )
            ->find($documentItemLocation->sr_item_id);
            $documentHeader = ErpSaleReturn::find($documentItemLocation?->detail?->sale_return_id);

            $stockLedger->vendor_id = @$documentHeader->vendor_id;
            $stockLedger->vendor_code = @$documentHeader->vendor_code;
            $stockLedger->receipt_qty = $qty ?? 0;
            $stockLedger->book_id = @$documentHeader->book_id;
            $totalItemCost = ($documentLotDetail->lot_qty*$documentDetail->rate) - ($documentDetail->item_discount_amount + $documentDetail->header_discount_amount);
            $costPerUnit = $totalItemCost/$qty;

            // Item Location Data
            $stockLedger->store_id = $documentDetail->store_id ?? null;
            $stockLedger->sub_store_id = $documentDetail->sub_store_id ?? null;
            $stockLedger->store = @$documentDetail->erpStore->store_code;
            $stockLedger->original_receipt_date = @$documentLotDetail->original_receipt_date;
            $stockLedger->lot_number = @$documentLotDetail->lot_number;
        }

        // Issue
        if($bookType == ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS){
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
            $stockLedger->sub_store_id = $documentItemLocation->sub_store_id ?? null;
            $stockLedger->store = @$documentItemLocation->erpStore->store_code;
            $stockLedger->lot_number = $documentItemLocation?->mrnHeader?->lot_number ?? null;
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
            $stockLedger->sub_store_id = $documentItemLocation->sub_store_id ?? null;
            $stockLedger->store = @$documentItemLocation->erpStore->store_code;
        }

        // Issue
        if($bookType == ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS) {
            if($transactionType == 'issue') {
                $qty = @$documentItemLocation->inventory_uom_qty;
                $documentHeader = ErpProductionSlip::find($documentItemLocation->pslip_id);
                $documentDetail = PslipBomConsumption::with(['pslip'])->find($documentItemLocation->id);
                $stockLedger->vendor_id = null;
                $stockLedger->vendor_code = null;
                $stockLedger->issue_qty = @$qty;
                $stockLedger->book_id = @$documentHeader->book_id;
                $totalItemCost = ($documentDetail->qty*$documentDetail->rate);
                $costPerUnit = $totalItemCost/$qty;

                // Item Location Data
                $stockLedger->store_id = $documentHeader->store_id ?? null;
                $stockLedger->sub_store_id = $documentHeader->sub_store_id ?? null;
                $stockLedger->station_id = $documentHeader->station_id ?? null;
                $stockLedger->store = @$documentHeader?->store?->store_code;
                $stockType = 'R';
                if($documentDetail->rm_type == 'sf') {
                    $stockType = 'W';
                }
                $stockLedger->stock_type = $stockType;
            }
            if($transactionType == 'receipt') {
                $qty = ($documentItemLocation->inventory_uom_qty - $utilizedQty);
                $documentHeader = ErpProductionSlip::find($documentItemLocation->pslip_id);
                $documentDetail = ErpPslipItem::with(['pslip', 'attributes'])->find($documentItemLocation->pslip_item_id);
                // Over ride attribute
                $stockLedger->vendor_id = null;
                $stockLedger->vendor_code = null;
                $stockLedger->receipt_qty = $qty ?? 0;
                $stockLedger->book_id = @$documentHeader->book_id;
                $totalItemCost = ($documentDetail->qty * $documentDetail->rate);
                $costPerUnit = $totalItemCost/$qty;
                // Item Location Data
                $stockLedger->store_id = $documentHeader->store_id ?? null;
                $stockLedger->sub_store_id = $documentHeader->sub_store_id ?? null;
                $stockLedger->station_id = $documentHeader->station_id ?? null;
                $stockLedger->store = $documentHeader?->store?->store_code;
                $stockLedger->lot_number = InventoryHelper::generateLotNumber($documentHeader -> document_date, $documentHeader -> book_code, $documentHeader -> document_number);

                if(!$documentHeader->is_last_station) {
                    $stockLedger->stock_type = 'W';
                    $stockLedger->wip_station_id = $documentHeader?->station_id ?? null;
                }
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
                $stockLedger->receipt_qty = @$qty;
                $stockLedger->original_receipt_date = @$utlStockLedger->original_receipt_date;
                $stockLedger->lot_number = InventoryHelper::generateLotNumber($documentHeader -> document_date, $documentHeader -> book_code, $documentHeader -> document_number);

            }
            $stockLedger->book_id = @$documentHeader->book_id;
            $totalItemCost = ($documentDetail->inventory_uom_qty*$documentDetail->rate) - ($documentDetail->item_discount_amount + $documentDetail->header_discount_amount);
            $costPerUnit = $totalItemCost/$qty;

            $stockLedger->stock_type=$documentDetail->stock_type;
            $stockLedger->wip_station_id=$documentDetail->wip_station_id;

            // Item Location Data
            if(($transactionType == 'issue') && $documentDetail->from_store_id){
                $stockLedger->store_id = $documentDetail->from_store_id ?? null;
                $stockLedger->store = @$documentDetail->fromErpStore->store_code;
            }

            if(($transactionType == 'receipt') && ($documentDetail->from_store_id && $documentDetail->to_store_id)){
                $stockLedger->store_id = $documentDetail->to_store_id ?? null;
                $stockLedger->store = @$documentDetail->toErpStore->store_code;
            }

            if(($transactionType == 'issue') && $documentDetail->from_sub_store_id){
                $stockLedger->sub_store_id = $documentDetail->from_sub_store_id ?? null;
                $stockLedger->sub_store = @$documentDetail->fromErpSubStore->store_code;
            }

            if(($transactionType == 'receipt') && ($documentDetail->from_sub_store_id && $documentDetail->to_sub_store_id)){
                $stockLedger->sub_store_id = $documentDetail->to_sub_store_id ?? null;
                $stockLedger->sub_store = @$documentDetail->toErpSubStore->store_code;
            }

            if(($transactionType == 'issue') && $documentDetail->from_station_id){
                $stockLedger->station_id = $documentDetail->from_station_id ?? null;
            }

            if(($transactionType == 'receipt') && ($documentDetail->to_station_id)){
                $stockLedger->station_id = $documentDetail->to_station_id ?? null;
            }

        }
        if($bookType == ConstantHelper::MATERIAL_RETURN_SERVICE_ALIAS_NAME){
            $qty = @$documentItemLocation->inventory_uom_qty;
            $detailQty = '';
            $detailId = ($transactionType == 'receipt') ? $documentItemLocation->mr_item_id : $documentItemLocation->id;
            $documentLotDetail = ErpMrItemLot::with(
                [
                    'detail',
                    'detail.header',
                    'detail.attributes'
                ]
            )
            ->find($detailId);
            $documentDetail = ErpMrItem::with(
                [
                    'header',
                    'attributes'
                ]
            )
            ->find($detailId);
            $documentHeader = ErpMaterialReturnHeader::find(@$documentDetail->material_return_id);
            $stockLedger->vendor_id = @$documentHeader?->vendor_id;
            $stockLedger->vendor_code = @$documentHeader?->vendor_code;
            if ($transactionType == 'issue') {
                $stockLedger->issue_qty = @$qty;
                $detailQty = $documentDetail->qty;
            }
            if ($transactionType == 'receipt') {
                $stockLedger->receipt_qty = @$qty;
                $detailQty = @$documentLotDetail->lot_qty;
                $stockLedger->original_receipt_date = @$documentLotDetail->original_receipt_date;
                $stockLedger->lot_number = @$documentLotDetail->lot_number;
            }
            $totalItemCost = ($detailQty*$documentDetail->rate) - ($documentDetail->item_discount_amount + $documentDetail->header_discount_amount);
            $stockLedger->book_id = @$documentHeader->book_id;
            $costPerUnit = $totalItemCost/$qty;
            // Item Location Data
            if(($transactionType == 'issue') && $documentDetail->store_id){
                $stockLedger->store_id = $documentDetail->store_id ?? null;
                $stockLedger->store = @$documentDetail->erpStore->store_code;
            }
            if(($transactionType == 'receipt') && ($documentDetail->to_store_id)){
                $stockLedger->store_id = $documentDetail->to_store_id ?? null;
                $stockLedger->store = @$documentDetail->toErpStore->store_code;
            }
            if(($transactionType == 'issue') && $documentDetail->from_sub_store_id){
                $stockLedger->sub_store_id = $documentDetail->from_sub_store_id ?? null;
                $stockLedger->sub_store = @$documentDetail->fromErpSubStore->store_code;
            }

            if(($transactionType == 'receipt') && ($documentDetail->from_sub_store_id && $documentDetail->to_sub_store_id)){
                $stockLedger->sub_store_id = $documentDetail->to_sub_store_id ?? null;
                $stockLedger->sub_store = @$documentDetail->toErpSubStore->store_code;
            }
        }

        if($bookType == ConstantHelper::PSV_SERVICE_ALIAS){
            $qty =ItemHelper::convertToBaseUom($documentItemLocation->item_id,$documentItemLocation->uom_id,abs($documentItemLocation->adjusted_qty));
            $documentHeader = ErpPsvHeader::find($documentItemLocation->psv_header_id);
            $detailId = $documentItemLocation->id;
            $documentDetail = ErpPsvItem::with(['header', 'attributes'])->find($detailId);
            $stockLedger->vendor_id = null;
            $stockLedger->vendor_code = null;
            if ($transactionType == 'issue') {
                $stockLedger->issue_qty = @$qty;
            }
            if ($transactionType == 'receipt') {
                $stockLedger->receipt_qty = @$qty;
                $stockLedger->original_receipt_date = @$utlStockLedger->original_receipt_date;
            }
            $stockLedger->book_id = @$documentHeader->book_id;
            $totalItemCost = ($qty*$documentDetail->rate) - ($documentDetail?->item_discount_amount + $documentDetail->header_discount_amount);
            $costPerUnit = $totalItemCost/$qty;
            // Item Location Data
            if(($transactionType == 'issue') && $documentDetail->header->store_id){
                $stockLedger->store_id = $documentDetail->header->store_id ?? null;
                $stockLedger->store = @$documentDetail->header->store->store_code;
            }
            if(($transactionType == 'receipt') && $documentDetail->header->store_id){
                $stockLedger->store_id = $documentDetail->header->store_id ?? null;
                $stockLedger->store = @$documentDetail->header->store->store_code;
            }
            if(($transactionType == 'issue') && $documentDetail->header->sub_store_id){
                $stockLedger->sub_store_id = $documentDetail->header->sub_store_id ?? null;
                $stockLedger->sub_store = @$documentDetail->header->sub_store->store_code;
            }

            if(($transactionType == 'receipt') && $documentDetail->header->sub_store_id){
                $stockLedger->sub_store_id = $documentDetail->header->sub_store_id ?? null;
                $stockLedger->sub_store = @$documentDetail->header->sub_store->store_code;
            }


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
        $stockLedger->refresh();

        self::updateStockCost($stockLedger);

        $attributeArray = array();
        $attributeJsonArray = array();
        if(isset($documentDetail->attributes) && !empty($documentDetail->attributes)){
            foreach($documentDetail->attributes as $key1 => $attribute) {
                $attributeName = @$attribute->attr_name ?? @$attribute->attribute_group_id ?? @$attribute->attribute_name;
                $attributeValue = @$attribute->attr_value ?? @$attribute->attribute_id ?? @$attribute->attribute_value;
                $itemAttributeId = @$attribute->item_attribute_id;

                if($bookType == ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS && $transactionType == 'issue') {
                    $itemAttributeId = $attribute['attribute_id'];
                    $attributeName = $attribute['attribute_name'];
                    $attributeValue = $attribute['attribute_value'];
                }

                $ledgerAttribute = new StockLedgerItemAttribute();
                $ledgerAttribute->stock_ledger_id = $stockLedger->id;
                $ledgerAttribute->item_id = @$documentDetail->item_id;
                $ledgerAttribute->item_code = @$documentDetail->item_code;
                $ledgerAttribute->item_attribute_id = $itemAttributeId;
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

        // Store Storage Points If Available
        // $storagePoint = StoragePointHelper::saveStoragePoints($stockLedger, $documentDetail->storage_points ?? []);

        return $stockLedger;
    }

    // Update Issue Stock
    private static function updateStockLedger($invoiceLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType, $issueQty, $stockReservation = null)
    {
        $user = Helper::getAuthenticatedUser();

        $balanceQty = 0;
        $extraQty = 0;
        $receiptQty = 0;
        $adjustedQty = 0;
        $reservedQty = 0;
        $message = '';
        if($issueQty && ($issueQty > $documentItemLocation->inventory_uom_qty)){
            $balanceQty = $issueQty - $documentItemLocation->inventory_uom_qty;
            $message = self::updateIssueStockForLessQty($invoiceLedger, $balanceQty, $documentItemLocation);
        } else {
            $balanceQty = $documentItemLocation->inventory_uom_qty - $issueQty;
            $approvedStockLedger = StockLedger::withDefaultGroupCompanyOrg()
                ->whereIn('document_status', ['approved','posted','approval_not_required'])
                ->where('item_id', $invoiceLedger->item_id)
                ->where('store_id', $invoiceLedger->store_id)
                ->where('sub_store_id', $invoiceLedger->sub_store_id)
                ->where('transaction_type', 'receipt')
                ->where('stock_type', $invoiceLedger -> stock_type)
                ->whereNull('utilized_id')
                ->whereRaw('receipt_qty > 0')
                ->orderBy('document_date', 'ASC');
                if (isset($invoiceLedger -> station_id) && $invoiceLedger -> station_id) {
                    $approvedStockLedger = $approvedStockLedger -> where('station_id', $invoiceLedger -> station_id);
                }
            if (isset($invoiceLedger -> wip_station_id) && $invoiceLedger -> wip_station_id && $invoiceLedger -> stock_type === self::STOCK_TYPE_WIP) {
                $approvedStockLedger = $approvedStockLedger -> where('wip_station_id', $invoiceLedger -> wip_station_id);
            }
            if(($bookType == ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS) && ($transactionType == 'issue') && ($documentItemLocation->mrn_detail_id)){
                $approvedStockLedger = $approvedStockLedger->where('document_detail_id', $documentItemLocation->mrn_detail_id)
                ->where('book_type', ConstantHelper::MRN_SERVICE_ALIAS);
            }

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
                // dd($approvedStockLedger);
            if ($approvedStockLedger->isNotEmpty()) {
                foreach ($approvedStockLedger as $val) {
                    $stockLedger = StockLedger::find($val -> id);
                    if(isset($stockReservation) && ($stockReservation == 'yes')){
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
                        // Update stock ledger for issued quantity
                        $stockLedger->reserved_qty -= $reservedQty;
                        $stockLedger->utilized_id = $invoiceLedger->id;
                        $stockLedger->utilized_date = $invoiceLedger->created_at->format('Y-m-d');
                        $stockLedger->save();

                        $stockLedger->receipt_qty = $invoiceLedger->issue_qty;
                        $stockLedger->save();

                    } else{
                        if ($stockLedger->receipt_qty < $balanceQty) {
                            $receiptQty = $stockLedger->receipt_qty;
                            $balanceQty -= $receiptQty;
                        } else {
                            $receiptQty = $balanceQty;
                            $extraQty = $stockLedger->receipt_qty - $balanceQty;
                            $balanceQty = 0; // Fully issued
                        }
                        // Update stock ledger for issued quantity
                        $stockLedger->receipt_qty = $receiptQty;
                        $stockLedger->utilized_id = $invoiceLedger->id;
                        $stockLedger->utilized_date = $invoiceLedger->created_at->format('Y-m-d');
                        $stockLedger->save();
                    }

                    $stockLedger->total_cost = round(($stockLedger->cost_per_unit*$stockLedger->receipt_qty), 2);
                    $stockLedger->save();
                    self::updateStockCost($stockLedger);

                    if(isset($stockReservation) && ($stockReservation == 'yes')){
                        $soItem = ErpSoItem::find($documentItemLocation->so_item_id);
                        $stockReservation = StockLedgerReservation::where('stock_ledger_id', $stockLedger->id)
                        ->where('so_id', $soItem?->sale_order_id)
                        ->where('so_item_id', $documentItemLocation->so_item_id)
                        ->first();
                        // dd($stockReservation);

                        if($stockReservation){
                            $stockReservation->quantity -= $invoiceLedger->issue_qty;
                            $stockReservation->save();
                        }
                    }

                    if(isset($stockReservation) && ($stockReservation == 'yes')){
                        // Handle extra quantity by creating a new stock ledger entry
                        if ($extraQty > 0) {
                            $newStockLedger = $stockLedger->replicate();
                            $newStockLedger->receipt_qty = $extraQty;
                            $newStockLedger->reserved_qty = $extraReservedQty;
                            $newStockLedger->issue_qty = 0.00;
                            $newStockLedger->utilized_id = null;
                            $newStockLedger->utilized_date = null;
                            $newStockLedger->save();

                            $newStockLedger->total_cost = round(($newStockLedger->cost_per_unit*$newStockLedger->receipt_qty), 2);
                            $newStockLedger->save();
                            self::updateStockCost($newStockLedger);
                        }
                    } else{
                        // Handle extra quantity by creating a new stock ledger entry
                        if (($extraQty > 0) || ($stockLedger->hold_qty > 0)) {
                            $newStockLedger = $stockLedger->replicate();
                            $newStockLedger->receipt_qty = $extraQty;
                            $newStockLedger->issue_qty = 0.00;
                            $newStockLedger->utilized_id = null;
                            $newStockLedger->utilized_date = null;
                            $newStockLedger->save();

                            if($stockLedger->hold_qty > 0){
                                $newStockLedger->hold_qty = $stockLedger->hold_qty;
                                $newStockLedger->save();
                                $stockLedger->hold_qty = 0;
                                $stockLedger->save();
                            }

                            $newStockLedger->total_cost = round(($newStockLedger->cost_per_unit*$newStockLedger->receipt_qty), 2);
                            $newStockLedger->save();
                            self::updateStockCost($newStockLedger);
                        }
                    }

                    // Stop the loop if the balance has been fully issued
                    if ($balanceQty <= 0) {
                        break;
                    }
                }
                $message = "Success";
            } else{
                $message = "This item does not have approved stocks, Please approve the mrn first.";
            }
            // dd($message);
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
        $lotNumber = 0;

        $utilizedStockLedger = StockLedger::withDefaultGroupCompanyOrg()
            ->where('utilized_id', $invoiceLedger->id)
            ->whereNotNull('receipt_qty')
            ->orderBy('document_date', 'DESC')
            ->get();
        if ($utilizedStockLedger->isNotEmpty()) {
            $lotNumbers = []; // Initialize an array to store lot numbers
            foreach($utilizedStockLedger as $val){
                $totalCost += $val->total_cost;
                $lotNumbers[] = $val->lot_number; // Append lot number to array
            }
            $lotNumber = implode(',', $lotNumbers); // Convert array to a comma-separated string
        }
        $costPerUnit = ($totalCost/$invoiceLedger->issue_qty);
        $stockLedger = StockLedger::find($invoiceLedger->id);
        $stockLedger->lot_number = $lotNumber;
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
            ->whereNull('hold_qty')
            ->orderBy('document_date', 'DESC')
            ->get();

        if ($utilizedStockLedger->isNotEmpty()) {
            foreach ($utilizedStockLedger as $val) {
                $adjustedQty = 0;
                $adjustedType = 0;
                $stockLedger = StockLedger::find($val -> id);
                if(isset($stockReservation) && ($stockReservation == 'yes')){
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
            $message = 'Stock ledger not found.';
            self::errorResponse($message);
        }
        foreach($stockLedger as $val){
            $balanceQty = $val->issue_qty;
            $message = self::updateIssueStockForLessQty($val, $balanceQty);
            $val->delete();
        }

        self::successResponse($message, $data=NULL);

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

    public static function getAccessibleLocations(string|array $locationType = NULL, $storeId = NULL, bool|null $withMultiStore = null)
    {
        //Retrieve Editable Store
        $employee = Helper::getAuthenticatedUser();
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
            if (is_string($locationType)) {
                $typeQuery = $typeQuery -> where('store_location_type', $locationType);
            } else if (is_array($locationType)) {
                $typeQuery = $typeQuery -> whereIn('store_location_type', $locationType);
            } else if (!$locationType) {
                $typeQuery = $typeQuery -> whereIn('store_location_type', ConstantHelper::STOCKK);
            }
        })
        ->when(isset($editStore), function ($storeQuery) use($editStore) { // Location with same country and state
            $storeQuery -> whereHas('address', function ($addressQuery) use($editStore) {
                $addressQuery -> where('country_id', $editStore -> address ?-> country_id)
                -> where('state_id', $editStore -> address ?-> state_id);
            });
        })
        // ->when(($employee->authenticable_type == "employee"), function ($locationQuery) use($employee) { // Location with same country and state
        //     $locationQuery->whereHas('employees', function ($employeeQuery) use ($employee) {
        //         $employeeQuery->where('employee_id', $employee->id);
        //     });
        // })
        ->get();
        if ($withMultiStore) {
            $stores = $stores -> filter(function ($store) {
                return (count(self::getAccesibleSubLocations($store -> id)) > 1);
            }) -> values();
        }
        return $stores;
    }

    public static function getAccesibleSubLocations(int $storeId, int|null $itemId = null, string| array $locationType = [ConstantHelper::STOCKK, ConstantHelper::SHOP_FLOOR], $subStoreId = NULL)
    {
        $subStoreIds = ErpSubStoreParent::withDefaultGroupCompanyOrg()->where('store_id', $storeId)
            -> get() -> pluck('sub_store_id') -> toArray();
        $subStores = ErpSubStore::select('id', 'name', 'code','station_wise_consumption','is_warehouse_required') -> whereIn('id', $subStoreIds) -> when($locationType, function ($typeQuery) use($locationType) {
            if (is_string($locationType)) {
                $typeQuery = $typeQuery -> where('type', $locationType);
            } else if (is_array($locationType)) {
                $typeQuery = $typeQuery -> whereIn('type', $locationType);
            } else {
                $typeQuery = $typeQuery -> where('type', [ConstantHelper::STOCKK]);
            }
            })->where(function($query) use($subStoreId) {
                $query->where('status',ConstantHelper::ACTIVE);
                if($subStoreId) {
                    $query->orWhere('id', $subStoreId);
                }
            }) -> get();
        return $subStores;
    }

    // Settlement For MRN (Receive)
    private static function settlementForMRN($documentHeaderId, $documentDetailId, $bookType, $documentStatus)
    {
        $user = Helper::getAuthenticatedUser();

        try {
            $transactionType = 'receipt';
            $documentItemLocations = MrnDetail::where('mrn_header_id',$documentHeaderId)
                ->with('mrnHeader',
                    'item',
                    'attributes',
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

                if(!$documentItemLocation->inventory_uom_qty || $documentItemLocation->inventory_uom_qty < 1){
                    $holdQty = ItemHelper::convertToBaseUom($documentItemLocation->item_id, $documentItemLocation->uom_id, $documentItemLocation->order_qty);
                    if($holdQty > $utilizedQty){
                        $stockLedger = new StockLedger();
                        $invoiceLedger = self::insertStockLedger($stockLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType, $holdQty);
                    }
                }
                if($documentItemLocation->inventory_uom_qty > $utilizedQty){
                    $stockLedger = new StockLedger();
                    $invoiceLedger = self::insertStockLedger($stockLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType, $utilizedQty);
                }
            }
        } catch (\Exception $e) {
            dd($e);
            $errorMsg = "ERROR: " . $e->getMessage();
            return self::errorResponse($errorMsg);

        }
        $message = 'success';
        return $message;
    }

    // Settlement For Sale Return (Receive)
    private static function settlementForSaleReturn($documentHeaderId, $documentDetailId, $bookType, $documentStatus)
    {
        $user = Helper::getAuthenticatedUser();
        try {
            $transactionType = 'receipt';
            $documentItemLocations = ErpSrItemLotDetail::whereIn('sr_item_id',$documentDetailId)
                ->whereHas('detail', function ($query) use ($documentHeaderId) {
                    $query->where('sale_return_id', $documentHeaderId);
                })
                ->with(
                    'detail',
                    'detail.header',
                    'detail.item',
                    'detail.attributes',
                    'detail.erpStore',
                    'detail.subStore',
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
        } catch (\Exception $e) {
            $errorMsg = "ERROR: " . $e->getMessage();
            return self::errorResponse($errorMsg);

        }
        $message = 'success';
        return $message;
    }

    // Settlement For Sale Invoice (Issue)
    private static function settlementForSaleInvoice($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $stockReservation)
    {
        $user = Helper::getAuthenticatedUser();

        try{
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
                    $updatedInvoiceLedger = self::updateStockLedger($invoiceLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType, $issueQty, $stockReservation);
                }
            }
        } catch (\Exception $e) {
            $errorMsg = "ERROR: " . $e->getMessage();
            return self::errorResponse($errorMsg);

        }
        $message = 'success';
        return $message;
    }

    // Settlement For Purchase Return (Issue)
    private static function settlementForPurchaseReturn($documentHeaderId, $documentDetailId, $bookType, $documentStatus)
    {
        $user = Helper::getAuthenticatedUser();

        try{
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
        } catch (\Exception $e) {
            $errorMsg = "ERROR: " . $e->getMessage();
            return self::errorResponse($errorMsg);

        }
        $message = 'success';
        return $message;
    }

    // Settlement For Pslip (Issue)
    private static function settlementForPslip($documentHeaderId, $documentDetailId, $bookType, $documentStatus)
    {
        $user = Helper::getAuthenticatedUser();

        try{
            $transactionType = 'issue';
            $documentItems = PslipBomConsumption::where('pslip_id', $documentHeaderId)
                ->with('pslip',
                    'item'
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
        } catch (\Exception $e) {
            $errorMsg = "ERROR: " . $e->getMessage();
            return self::errorResponse($errorMsg);

        }
        $message = 'success';
        return $message;
    }

    // Settlement For Pslip (Receive)
    private static function settlementForPslipReceipt($documentHeaderId, $documentDetailId, $bookType, $documentStatus)
    {
        $user = Helper::getAuthenticatedUser();

        try{
            $transactionType = 'receipt';
            $documentItemLocations = ErpPslipItemLocation::where('pslip_id', $documentHeaderId)
                ->whereIn('pslip_item_id', $documentDetailId)
                ->with('header',
                    'detail',
                    'detail.item',
                    'detail.attributes',
                    'erpStore',
                    'erpSubStore',
                    'station',
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
        } catch (\Exception $e) {
            $errorMsg = "ERROR: " . $e->getMessage();
            return self::errorResponse($errorMsg);

        }
        $message = 'success';
        return $message;
    }

    // Settlement For Material Issue For Issue
    private static function settlementForMIForIssue($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType)
    {
        $user = Helper::getAuthenticatedUser();

        try{
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
        } catch (\Exception $e) {
            $errorMsg = "ERROR: " . $e->getMessage();
            return self::errorResponse($errorMsg);

        }
        $message = 'success';
        return $message;
    }
    private static function settlementForPsvForIssue($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType)
    {
        $user = Helper::getAuthenticatedUser();

        try{
            $documentItems = ErpPsvItem::whereIn('id',$documentDetailId)
                ->with('header',
                    'item',
                    'attributes'
                )
                ->get();

            if(isset($documentItems) && $documentItems){
                foreach ($documentItems as $documentItem) {
                    if($documentItem->header->store_id){
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
                        $documentItem->inventory_uom_qty = ItemHelper::convertToBaseUom($documentItem->item_id,$documentItem->uom_id,abs($documentItem->adjusted_qty));
                        $invoiceLedger = self::insertStockLedger($stockLedger, $documentItem,  $bookType, $documentStatus, $transactionType, $utilizedQty);
                        $updatedInvoiceLedger = self::updateStockLedger($invoiceLedger, $documentItem, $bookType, $documentStatus, $transactionType, $issueQty);
                    }
                }
            }
        } catch (\Exception $e) {
            $errorMsg = "ERROR: " . $e->getMessage();
            return self::errorResponse($errorMsg);

        }
        $message = 'success';
        return $message;
    }

    // Settlement For Material Issue For Issue
    private static function settlementForMRForIssue($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType)
    {
        $user = Helper::getAuthenticatedUser();

        try{
            $documentItems = ErpMrItem::where('material_return_id',$documentHeaderId)
                ->with('header',
                    'item',
                    'attributes'
                )
                ->get();

            if(isset($documentItems) && $documentItems){
                foreach ($documentItems as $documentItem) {
                    if($documentItem->store_id){
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
        } catch (\Exception $e) {
            $errorMsg = "ERROR: " . $e->getMessage();
            return self::errorResponse($errorMsg);

        }
        $message = 'success';
        return $message;
    }

    // Settlement For Material Issue For Receive
    private static function settlementForMIForReceive($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType)
    {
        $user = Helper::getAuthenticatedUser();

        try{
            $documentItemLocations = ErpMiItemLocation::where('material_issue_id',$documentHeaderId)
                ->whereIn('mi_item_id',$documentDetailId)
                ->where('type', 'to')
                ->get();
            // dd($documentItemLocations);
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
                    $stockLedger = new StockLedger();
                    $invoiceLedger = self::insertStockLedger($stockLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType, $utilizedQty, $utlStockLedger = null);
                    // $issueStockLedger = StockLedger::withDefaultGroupCompanyOrg()
                    //     ->where('document_header_id',$documentHeaderId)
                    //     ->where('document_detail_id',$documentDetailId)
                    //     ->where('book_type','=',$bookType)
                    //     ->where('transaction_type','=','issue')
                    //     ->first();
                    // if($issueStockLedger){
                    //     $utilizedStockLedger = StockLedger::withDefaultGroupCompanyOrg()
                    //         ->where('utilized_id',$issueStockLedger->id)
                    //         ->where('transaction_type','=','receipt')
                    //         ->get();
                    //         dd($utilizedStockLedger);
                    //     if(!empty($utilizedStockLedger)){
                    //         foreach($utilizedStockLedger as $utlStockLedger){
                    //             $stockLedger = new StockLedger();
                    //             $invoiceLedger = self::insertStockLedger($stockLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType, $utilizedQty, $utlStockLedger);
                    //         }
                    //     }
                    // }
                }
            }
        } catch (\Exception $e) {
            $errorMsg = "ERROR: " . $e->getMessage();
            return self::errorResponse($errorMsg);

        }
        $message = 'success';
        return $message;
    }
    private static function settlementForPsvForReceive($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType)
    {
        $user = Helper::getAuthenticatedUser();

        try{
            $documentItem = ErpPsvItem::where('psv_header_id',$documentHeaderId)
                ->whereIn('id',$documentDetailId)
                ->get();
            // dd($documentItemLocations);
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
            foreach ($documentItem as $documentItems) {
                $utilizedQty = StockLedger::withDefaultGroupCompanyOrg()
                    ->where('document_header_id',$documentHeaderId)
                    ->where('document_detail_id',$documentDetailId)
                    ->where('book_type','=',$bookType)
                    ->where('transaction_type','=',$transactionType)
                    // ->where('document_status','draft')
                    ->whereNotNull('utilized_id')
                    ->sum('receipt_qty');
                    $documentItems->inventory_uom_qty = ItemHelper::convertToBaseUom($documentItems->item_id,$documentItems->uom_id,abs($documentItems->adjusted_qty));
                if($documentItems->inventory_uom_qty > $utilizedQty){
                    $stockLedger = new StockLedger();
                    $invoiceLedger = self::insertStockLedger($stockLedger, $documentItems, $bookType, $documentStatus, $transactionType, $utilizedQty, $utlStockLedger = null);
                }
            }
        } catch (\Exception $e) {
            $errorMsg = "ERROR: " . $e->getMessage();
            return self::errorResponse($errorMsg);

        }
        $message = 'success';
        return $message;
    }

    // Settlement For Material Issue For Receive
    private static function settlementForMRForReceive($documentHeaderId, $documentDetailId, $bookType, $documentStatus, $transactionType)
    {
        $user = Helper::getAuthenticatedUser();
        try{
            $documentItemLocations = ErpMrItemLot::whereIn('mr_item_id',$documentDetailId)
                ->whereHas('detail', function ($query) use ($documentHeaderId) {
                    $query->where('material_return_id', $documentHeaderId)
                        ->where('type', 'to');
                })
                ->with('detail')
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
                    $header = ErpMaterialReturnHeader::find($documentHeaderId);
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
                    else if($header->return_type == 'Consumption')
                    {
                        $stockLedger = new StockLedger();
                        $invoiceLedger = self::insertStockLedger($stockLedger, $documentItemLocation, $bookType, $documentStatus, $transactionType, $utilizedQty);
                    }
                }
            }
        } catch (\Exception $e) {
            $errorMsg = "ERROR: " . $e->getMessage();
            return self::errorResponse($errorMsg);

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

    public static function getIssueTransactionLotNumbers(string $serviceAlias, int $headerId, int $detailId, int $altUomId) : array
    {
        $stockLedger = StockLedger::where('book_type', $serviceAlias) -> where('document_header_id', $headerId)
            -> where('document_detail_id', $detailId) -> first();
        if (!isset($stockLedger)) {
            return [];
        }
        $utlStockLedgers = StockLedger::select('item_id','receipt_qty', 'lot_number','original_receipt_date','so_id') -> where('utilized_id', $stockLedger -> id) -> get();
        $lotNoWithQtys = [];
        foreach ($utlStockLedgers as $utlStock) {
            array_push($lotNoWithQtys, [
                'lot_qty' => ItemHelper::convertToAltUom($utlStock -> item_id, $altUomId, $utlStock -> receipt_qty),
                'lot_number' => $utlStock -> lot_number,
                'original_receipt_date' => $utlStock -> original_receipt_date,
                'so_no' => $utlStock -> so ? $utlStock -> so -> book_code . "-" . $utlStock -> so -> document_number : ' ',
                'so_qty' => $utlStock -> so ? $utlStock -> so -> order_qty : 0,

            ]);
        }
        return $lotNoWithQtys;
    }

    public static function generateLotNumber(string $documentDate, string $bookCode, string $documentNumber) : string
    {
        $lotNumber = date('Y/M/d', strtotime($documentDate)) . '/' . $bookCode . '/' . $documentNumber;
        return strtoupper($lotNumber);
    }

    public static function getStockType()
    {
        $stockTypes = self::STOCK_TYPES;
        $formattedStockType = collect([]);
        foreach ($stockTypes as $stockType) {
            $currentStockType = new stdClass();
            $currentStockType -> label = $stockType['label'];
            $currentStockType -> value = $stockType['value'];
            $formattedStockType -> push($currentStockType);
        }
        return $formattedStockType;
    }

}
