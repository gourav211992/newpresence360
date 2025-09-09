<?php

use App\Helpers\Helper;
use App\Helpers\ConstantHelper;
use App\Http\Controllers\ErpTripPlanController;
use App\Http\Controllers\PurchaseOrderImportController;
use App\Models\DefectNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HsnController;
use App\Http\Controllers\MrnController;
use App\Http\Controllers\PWOController;
use App\Http\Controllers\TaxController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LandController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ErpPlController;
use App\Http\Controllers\ErpPqController;
use App\Http\Controllers\ErpRCController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ErpRgrStoreMappingController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ErpBinController;
use App\Http\Controllers\ErpPdsController;
use App\Http\Controllers\ErpPqcController;
use App\Http\Controllers\ErpPSVController;
use App\Http\Controllers\ErpRFQController;
use App\Http\Controllers\VendorController;
use App\Helpers\Inventory\StockReservation;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ErpRackController;
use App\Http\Controllers\PutAwayController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\TestingController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\BookTypeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ErpShelfController;
use App\Http\Controllers\ErpStoreController;
use App\Http\Controllers\SubStoreController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\ErpDriverController;
use App\Http\Controllers\GateEntryController;
use App\Http\Controllers\GrAccountController;
use App\Http\Controllers\IssueTypeController;
use App\Http\Controllers\TDSReportController;
use App\Http\Controllers\AmendementController;
use App\Http\Controllers\CrDrReportController;
use App\Http\Controllers\ErpMachineController;
use App\Http\Controllers\ErpVehicleController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\ProfitLossController;
use App\Http\Controllers\WipAccountController;
use App\Http\Controllers\CogsAccountController;
use App\Http\Controllers\DPRTemplateController;
use App\Http\Controllers\EinvoicePdfController;
use App\Http\Controllers\JobOrder\JoController;
use App\Http\Controllers\PackingListController;
use App\Http\Controllers\PaymentTermController;
use App\Http\Controllers\AutocompleteController;
use App\Http\Controllers\BalanceSheetController;
use App\Http\Controllers\DynamicFieldController;
use App\Http\Controllers\ErpDprMasterController;
use App\Http\Controllers\ErpEquipmentController;
use App\Http\Controllers\ErpSaleOrderController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\FileTrackingController;
use App\Http\Controllers\Finance\GstrController;
use App\Http\Controllers\Ledger\GroupController;
use App\Http\Controllers\PurchaseBillController;
use App\Http\Controllers\SalesAccountController;
use App\Http\Controllers\StationGroupController;
use App\Http\Controllers\StockAccountController;
use App\Http\Controllers\TrialBalanceController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\DefectTrackerController;
use App\Http\Controllers\DocumentDriveController;
use App\Http\Controllers\ErpDefectTypeController;
use App\Http\Controllers\ErpSaleReturnController;
use App\Http\Controllers\ExpenseAdviseController;
use App\Http\Controllers\ExpenseMasterController;
use App\Http\Controllers\GenericImportController;
use App\Http\Controllers\GstValidationController;
use App\Http\Controllers\Land\LandPlotController;
use App\Http\Controllers\Ledger\LedgerController;
use App\Http\Controllers\Plant\MaintWoController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\UserSignatureController;
use App\Http\Controllers\CashflowReportController;
use App\Http\Controllers\DiscountMasterController;
use App\Http\Controllers\ErpMaintananceController;
use App\Http\Controllers\ErpRouteMasterController;
use App\Http\Controllers\ErpSaleInvoiceController;
use App\Http\Controllers\ErpVehicleTypeController;
use App\Http\Controllers\OverheadMasterController;
use App\Http\Controllers\PaymentVoucherController;
use App\Http\Controllers\Plant\MaintBomController;
use App\Http\Controllers\ProductSectionController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\ServiceAccountController;
use App\Http\Controllers\ApprovalProcessController;
use App\Http\Controllers\CloseFy\CloseFyController;
use App\Http\Controllers\EInvoiceServiceController;
use App\Http\Controllers\ErpLorryReceiptController;
use App\Http\Controllers\ErpTransportersController;
use App\Http\Controllers\FeedbackProcessController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\Land\LandParcelController;
use App\Http\Controllers\Land\LandReportController;
use App\Http\Controllers\MaterialReceiptController;
use App\Http\Controllers\ProductionRouteController;
use App\Http\Controllers\SaleOrderImportController;
use App\Http\Controllers\DocumentApprovalController;
use App\Http\Controllers\ErpMaterialIssueController;
use App\Http\Controllers\FixedAsset\SetupController;
use App\Http\Controllers\FixedAsset\SplitController;
use App\Http\Controllers\Land\Lease\LeaseController;
use App\Http\Controllers\PurchaseOrder\PoController;
use App\Http\Controllers\WarehouseMappingController;
use App\Http\Controllers\ErpFreightChargesController;
use App\Http\Controllers\ErpMaterialReturnController;
use App\Http\Controllers\ErpProductionSlipController;
use App\Http\Controllers\FixedAsset\MergerController;
use App\Http\Controllers\FixedAsset\RevImpController;
use App\Http\Controllers\HomeLoan\HomeLoanController;
use App\Http\Controllers\PurchaseIndent\PiController;
use App\Http\Controllers\TermLoan\TermLoanController;
use App\Http\Controllers\TermsAndConditionController;
use App\Http\Controllers\BillOfMaterial\BomController;
use App\Http\Controllers\ErpMaintenanceTypeController;
use App\Http\Controllers\ErpMultiPointFixedController;
use App\Http\Controllers\TransporterInvoiceController;
use App\Http\Controllers\WarehouseStructureController;
use App\Http\Controllers\ComplaintManagementController;
use App\Http\Controllers\InspectionChecklistController;
use App\Http\Controllers\OrganizationServiceController;
use App\Http\Controllers\refined_index\IndexController;
use App\Http\Controllers\CostCenter\CostGroupController;
use App\Http\Controllers\ErpMultiPointPricingController;
use App\Http\Controllers\FixedAsset\InsuranceController;
use App\Http\Controllers\PhysicalStockAccountController;
use App\Http\Controllers\PriceVarianceAccountController;
use App\Http\Controllers\ProductSpecificationController;
use App\Http\Controllers\WarehouseItemMappingController;
use App\Http\Controllers\CostCenter\CostCenterController;
use App\Http\Controllers\ErpTransporterRequestController;
use App\Http\Controllers\LoanProgress\ApprovalController;
use App\Http\Controllers\ManufacturingOrder\MoController;
use App\Http\Controllers\PurchaseReturnAccountController;
use App\Http\Controllers\WarehouseMultiMappingController;
use App\Http\Controllers\FixedAsset\MaintenanceController;
use App\Http\Controllers\LoanProgress\AppraisalController;
use App\Http\Controllers\FixedAsset\DepreciationController;
use App\Http\Controllers\FixedAsset\RegistrationController;
use App\Http\Controllers\LoanProgress\AssessmentController;
use App\Http\Controllers\Stakeholder\StakeholderController;
use App\Http\Controllers\VehicleLoan\VehicleLoanController;
use App\Http\Controllers\BillOfMaterial\BomImportController;
use App\Http\Controllers\FixedAsset\IssueTransferController;
use App\Http\Controllers\Plant\DefectNotificationController;
use App\Http\Controllers\Report\TransactionReportController;
use App\Http\Controllers\FixedAsset\ITDepreciationController;
use App\Http\Controllers\LoanManagement\LoanReportController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\LoanProgress\ProcessingFeeController;
use App\Http\Controllers\LoanManagement\LoanRecoveryController;
use App\Http\Controllers\LoanProgress\SanctionLetterController;
use App\Http\Controllers\ReManufacturing\Scrap\ScrapController;
use App\Http\Controllers\LoanManagement\LoanDashboardController;
use App\Http\Controllers\LoanManagement\LoanManagementController;
use App\Http\Controllers\LoanManagement\LoanSettlementController;
use App\Http\Controllers\ProductionSlip\ProductionSlipController;
use App\Http\Controllers\LoanManagement\LoanDisbursementController;
use App\Http\Controllers\LoanManagement\LoanInterestRateController;
use App\Http\Controllers\LoanProgress\LegalDocumentationController;
use App\Http\Controllers\ErpPublicOutreachAndCommunicationController;
use App\Http\Controllers\LoanManagement\LoanFinancialSetupController;
use App\Http\Controllers\PurchaseOrder\PurchaseOrderReportController;
use App\Http\Controllers\LoanManagement\LoanRepaymentReportController;
use App\Http\Controllers\LoanManagement\LoanDisbursementReportController;
use App\Http\Controllers\ErpItemBundleController;
use App\Http\Controllers\RgrController;
use App\Http\Controllers\PrintBarcodeController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/clear', function () {
    Artisan::call('optimize:clear');
    return "Cleared!";
});

Route::get('/lorry-receipt/approve/{id}/{email}', [ErpLorryReceiptController::class, 'approveReceipt'])->name('lorry-receipt.approve');

// Route::get('/testing', [TestingController::class, 'testing']);

// Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
// Route::post('/register', [AuthController::class, 'register']);

// Route::get('/pos/report', [PoController::class, 'poReport'])->name('po.report');

Route::post('/broadcasting/auth', function (Illuminate\Http\Request $request) {
    return Broadcast::auth($request);
})->middleware(['user.auth']);



Route::middleware(['user.auth'])->group(function () {

    // ADD INSIDE AUTH MIDDLEWARE AS AUTH USER IS BEING USED INSIDE
    Route::get('/assign-menu', function () {
        $menuName = request()->menu_name ?? '';
        $menuAlias = request()->menu_alias ?? '';
        $serviceIds = request()->service_ids ?? '';
        if ($serviceIds) {
            $serviceIds = explode(',', $serviceIds);
        }
        return Helper::setMenuAccessToEmployee($menuName, $menuAlias, $serviceIds);
    });



    Route::get('/sales-order/create', [ErpSaleOrderController::class, 'create'])->name('sale.order.create');
    Route::get('/sales-quotation/create', [ErpSaleOrderController::class, 'create'])->name('sale.quotation.create');
    Route::post('/sales-order/store', [ErpSaleOrderController::class, 'store'])->name('sale.order.store');
    // Route::get('/sales-order/{type}', [ErpSaleOrderController::class, 'index'])->name('sale.order.index');
    Route::get('/sales-order', [ErpSaleOrderController::class, 'index'])->name('sale.order.index');
    Route::get('/sales-quotation', [ErpSaleOrderController::class, 'index'])->name('sale.quotation.index');
    Route::get('/sales-order/edit/{id}', [ErpSaleOrderController::class, 'edit'])->name('sale.order.edit');
    Route::get('/sales-quotation/edit/{id}', [ErpSaleOrderController::class, 'edit'])->name('sale.quotation.edit');
    Route::get('/sales-order/quotation', [ErpSaleOrderController::class, 'processQuotation'])->name('sale.order.quotation.get');
    Route::get('/sales-order/quotations/get', [ErpSaleOrderController::class, 'getQuotations'])->name('sale.order.quotation.get.all');
    Route::get('/customer/addresses/{customerId}', [ErpSaleOrderController::class, 'getCustomerAddresses'])->name('get_customer_addresses');
    Route::get('/vendor/addresses/{vendorId}', [ErpPqController::class, 'getVendorAddresses'])->name('get_vendor_addresses');
    Route::get('/item/attributes/{itemId}', [ErpSaleOrderController::class, 'getItemAttributes'])->name('get_item_attributes');
    Route::get('/customer/address/{id}', [ErpSaleOrderController::class, 'getCustomerAddress'])->name('get_customer_address');
    Route::get('/item/inventory/details', [ErpSaleOrderController::class, 'getItemDetails'])->name('get_item_inventory_details');
    Route::get('/item/store/details', [ErpSaleOrderController::class, 'getItemStoreData'])->name('get_item_store_details');
    Route::post('/address/customers/save', [ErpSaleOrderController::class, 'addAddress'])->name('sales_order.add.address');
    Route::post('/address/vendor/save', [ErpPqController::class, 'addAddress'])->name('pq.add.address');
    Route::get('/sale-order/generate-pdf/{id}', [ErpSaleOrderController::class, 'generatePdf'])->name('sale.order.generate-pdf');
    Route::get('/sales-order/amend/{id}', [ErpSaleOrderController::class, 'amendmentSubmit'])->name('sale.order.amend');
    Route::get('/sales-order/bom/check', [ErpSaleOrderController::class, 'checkItemBomExists'])->name('sale.order.bom.check');
    Route::post('/sales-order/revoke', [ErpSaleOrderController::class, 'revokeSalesOrderOrQuotation'])->name('sale.order.revoke');
    Route::post('/sales-order/get/customizable-bom', [ErpSaleOrderController::class, 'getProductionBomOfItem'])->name('sale.order.get.production.bom');
    Route::post('/sales-order/short-close', [ErpSaleOrderController::class, 'shortCloseSubmit'])->name('sale.order.get.shortClose.submit');
    Route::post('/sales-order/report', [ErpSaleOrderController::class, 'salesOrderReport'])->name('sale.order.report');
    Route::post('/sales-order/report/attribute-grouped', [ErpSaleOrderController::class, 'salesOrderReportAttributeGrouped'])->name('sale.order.report.attribute.grouped');
    Route::post('/items/stock-details', [ErpSaleOrderController::class, 'getItemOrgLocationStoreWiseStock'])->name('item.stock.details');
    Route::post('/item/stock-details', [ErpSaleOrderController::class, 'getCurrentItemStock'])->name('current.item.stock.details');
    Route::post('/item-sale-price', [ErpSaleOrderController::class, 'getItemSalePrice'])->name('current.item.getItemSalePrice');
    Route::get('/sales-invoice/amend/{id}', [ErpSaleInvoiceController::class, 'amendmentSubmit'])->name('sale.invoice.amend');
    Route::get('/sales-invoice/posting/get', [ErpSaleInvoiceController::class, 'getPostingDetails'])->name('sale.invoice.posting.get');
    Route::post('/sales-invoice/post', [ErpSaleInvoiceController::class, 'postInvoice'])->name('sale.invoice.post');
    Route::get('/', [HomeController::class, 'index'])->name('/');
    Route::post('/update-organization', [CustomerController::class, 'updateOrganization'])->name('update-organization');
    Route::post('/approveVoucher', [VoucherController::class, 'approveVoucher'])->name('approveVoucher');

    // Notification
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notification/read/{id}', [NotificationController::class, 'markAsRead'])->name('notification.read');
    Route::get('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');


    Route::post('uploadVouchers', [PaymentVoucherController::class, 'uploadVouchers'])->name('uploadVouchers');
    Route::get('receipt-vouchers/{type}', [PaymentVoucherController::class, 'index'])->name('paymentVoucher.receipt');
    Route::post('approvePaymentVoucher', [PaymentVoucherController::class, 'approvePaymentVoucher'])->name('approvePaymentVoucher');
    Route::post('getParties', [PaymentVoucherController::class, 'getParties'])->name('getParties');
    Route::get('paymentVouchersAmendment/{id}', [PaymentVoucherController::class, 'amendment'])->name('paymentVouchers.amendment');
    Route::resource('payments', PaymentVoucherController::class)->except(['show', 'destroy', 'edit']);
    Route::resource('receipts', PaymentVoucherController::class)->except(['show', 'destroy', 'edit']);
    Route::get('payments/{payment}/edit', [PaymentVoucherController::class, 'edit'])->name('payments.edit');
    Route::get('receipts/{payment}/edit', [PaymentVoucherController::class, 'edit'])->name('receipts.edit');
    Route::get('/payment-vouchers/voucher/get', [PaymentVoucherController::class, 'getPostingDetails'])->name('paymentVouchers.getPostingDetails');
    Route::post('/payment-vouchers/voucher/post', [PaymentVoucherController::class, 'postPostingDetails'])->name('paymentVouchers.post');
    Route::post('getExchangeRate', [ExchangeRateController::class, 'getExchangeRate'])->name('getExchangeRate');
    Route::get('/payment-receipt/revoke', [PaymentVoucherController::class, 'revokeDocument'])->name('paymentVouchers.revoke.document');
    Route::get('/payment-receipt/cancel', [PaymentVoucherController::class, 'cancelDocument'])->name('paymentVouchers.cancel.document');
    Route::get('/payment-receipt/print/{id}/{ledger}/{group}', [PaymentVoucherController::class, 'getPrint'])->name('paymentVouchers.print');
    Route::post('/payment-receipt/email', [PaymentVoucherController::class, 'sendMail'])->name('paymentVouchers.email');
    Route::post('/voucher/check-reference', [PaymentVoucherController::class, 'checkReference'])->name('voucher.checkReference');



    Route::post('getLedgerVouchers', [VoucherController::class, 'getLedgerVouchers'])->name('getLedgerVouchers');
    Route::get('/voucher', [VoucherController::class, 'index']);
    Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::get('vouchersAmendment/{id}', [VoucherController::class, 'amendment'])->name('vouchers.amendment');
    // Route::get('/', [CustomerController::class, 'index'])->name('/');
    Route::get('getVoucherNo/{book_id}', [VoucherController::class, 'get_voucher_no'])->name('get_voucher_no');
    Route::get('getVoucherSeries/{id}', [VoucherController::class, 'get_series'])->name('get_voucher_series');
    Route::post('ledgersSearch', [VoucherController::class, 'ledgers_search'])->name('ledgers.search');
    Route::resource('vouchers', VoucherController::class)->except(['show', 'destroy']);
    Route::get('vouchers/getLedgerGroups', [VoucherController::class, 'getLedgerGroups'])->name('voucher.getLedgerGroups');
    Route::get('report/creditors', [CrDrReportController::class, 'credit'])->name('voucher.credit.report');
    Route::get('report/debitors', [CrDrReportController::class, 'debit'])->name('voucher.debit.report');
    Route::post('report/debitors-creditor-export', [CrDrReportController::class, 'exportDebitorCreditor'])->name('credit.debit.report.export');
    Route::get('/crdr/report/ledger/{group}', [CrDrReportController::class, 'getLedgersByGroup'])->name('crdr.report.ledger');
    Route::get('/report/getLedgerDetails/{type}/{ledger}/{group}', [CrDrReportController::class, 'getLedgerDetails'])->name('crdr.report.ledger.details');
    Route::get('report/creditors/getDetails', [CrDrReportController::class, 'credit_debit_details'])->name('voucher.credit_details.report');
    Route::get('/report/getLedgerPrint/{type}/{ledger}/{group}/{bill_type?}', [CrDrReportController::class, 'getLedgerDetailsPrint'])->name('crdr.report.ledger.print');
    Route::post('/crdr/report/add-scheduler', [CrDrReportController::class, 'addScheduler'])->name('crdr.add.scheduler');
    Route::get('/report/pending-payments', [CrDrReportController::class, 'creditorsPendingPayment'])->name('creditor.pending.receipts');
    Route::get('/report/pending-receipts', [CrDrReportController::class, 'debitorsPendingPayment'])->name('debitor.pending.payments');
    Route::post('getInvocies', [CrDrReportController::class, 'getInvocies'])->name('getInvocies');
    Route::get('/report/import/{type}', [CrDrReportController::class, 'showImportForm'])->name('pending.payment.show.import');
    Route::get('/report/process-import/{type}', [CrDrReportController::class, 'importingProgress'])->name('pending.payment.progress.import');
    Route::post('/report/import', [CrDrReportController::class, 'import'])->name('pending.payment.import');
    Route::post('/report/update-cache', [CrDrReportController::class, 'updateCacheData'])->name('pending.payment.update.cache');
    Route::post('report/store-cr-dr-row', [CrDrReportController::class, 'storeCrDrRowData'])->name('report.row.data');
    Route::get('/report/export-successful', [CrDrReportController::class, 'exportSuccessfulItems'])->name('pending.payment.export.successful');
    Route::get('/report/export-failed', [CrDrReportController::class, 'exportFailedItems'])->name('pending.payment.export.failed');
    Route::get('vouchers/revoke', [VoucherController::class, 'revokeDocument'])->name('voucher.revoke.document');
    Route::get('vouchers/cancel', [VoucherController::class, 'cancelDocument'])->name('voucher.cancel.document');
    Route::resource('ledger-groups', GroupController::class)->except(['show']);
    Route::get('/search/group', [GroupController::class, 'getLedgerGroup'])->name('groups.search');
    Route::post('/group/generate-prefix', [GroupController::class, 'generate_prefix'])->name('generate-group-prefix');
    Route::post('/group/check-prefix', [GroupController::class, 'checkPrefix'])->name('groups-check-prefix');
    Route::resource('ledgers', LedgerController::class)->except(['show']);
    Route::get('/ledgers/{ledgerId}/groups', [LedgerController::class, 'getLedgerGroups'])->name('ledgers.groups');
    ;
    Route::get('/search/ledger', [LedgerController::class, 'getLedger'])->name('ledger.search');
    Route::get('/ledger/import', [LedgerController::class, 'showImportForm'])->name('ledger.show.import');
    Route::post('/ledger/import', [LedgerController::class, 'import'])->name('ledger.import');
    Route::get('/ledger/export-successful', [LedgerController::class, 'exportSuccessfulItems'])->name('ledgers.export.successful');
    Route::get('/ledger/export-failed', [LedgerController::class, 'exportFailedItems'])->name('ledgers.export.failed');
    Route::post('/ledger/generate-code', [LedgerController::class, 'generateLedgerCode'])->name('generate-ledger-code');
    Route::post('/approveLedger', [LedgerController::class, 'approveVoucher'])->name('approveLedger');
    Route::get('ledgerAmendment/{id}', [LedgerController::class, 'amendment'])->name('ledgers.amendment');
    Route::get('ledger/update_null_data', [LedgerController::class, 'updateNull'])->name('ledgers.update-null-data');
    Route::get('create-party-ledger', [LedgerController::class, 'createPartyLedger'])->name('ledgers.create.party-ledger');

    // closefy
    Route::get('/close-fy', [CloseFyController::class, 'index'])->name('close-fy');
    Route::post('/close-fy', [CloseFyController::class, 'closeFy'])->name('post-closefy');
    Route::post('/close-fy/update-authuser', [CloseFyController::class, 'updateFyAuthorizedUser'])->name('close-fy.update-authuser');
    Route::post('/close-fy/delete-authuser', [CloseFyController::class, 'deleteFyAuthorizedUser'])->name('close-fy.delete-authuser');
    Route::post('/close-fy/lock', [CloseFyController::class, 'lockUnlockFy'])->name('close-fy.lock');
    Route::post('/getFyInitialGroups', [CloseFyController::class, 'getFyInitialGroups'])->name('getFyInitialGroups');
    Route::post('/store-fy-session', [CloseFyController::class, 'storeFySession'])->name('store.fy.session');
    // close month fy
    Route::get('/close-month-fy', [CloseFyController::class, 'monthFyIndex'])->name('close-month-fy');



    Route::resource('cost-group', CostGroupController::class)->except(['show']);
    Route::resource('cost-center', CostCenterController::class)->except(['show']);
    Route::post('getLocations', [CostCenterController::class, 'getLocation'])->name('cost-center.getLocations');
    Route::get('get-cost-center/{id}', [CostCenterController::class, 'getCostCenter'])->name('cost-center.get-cost-center');

    Route::get('get-cost-centers', [CostCenterController::class, 'getCostCenterLocationBasis'])->name('locations.getCostCenter');

    Route::get('/city', [CityController::class, 'index']);

    Route::get('/vendor', [VendorController::class, 'index']);

    Route::get('/vendors/users', [VendorController::class, 'users']);

    // //Erp Stores Route
    // Route::get('/stocks', [ErpStoreController::class, 'index'])->name('stock');
    // Route::get('/stock-create', [ErpStoreController::class, 'create'])->name('stock_create');
    // Route::post('/stocks/store', [ErpStoreController::class, 'store'])->name('stocks.store');
    // Route::get('/edit-stock/{id}', [ErpStoreController::class, 'edit'])->name('stockEdit');
    // Route::post('/update-stock/{id}', [ErpStoreController::class, 'update'])->name('stock.update');
    // Route::get('/delete-stock/{id}', [ErpStoreController::class, 'delete'])->name('stock.delete');

    // //Erp Rack Route
    // Route::get('/racks', [ErpRackController::class, 'index'])->name('racks');
    // Route::get('/rack-create', [ErpRackController::class, 'create'])->name('rack_create');
    // Route::post('/racks/store', [ErpRackController::class, 'store'])->name('racks.store');
    // Route::get('/edit-rack/{id}', [ErpRackController::class, 'edit'])->name('rackEdit');
    // Route::post('/update-rack/{id}', [ErpRackController::class, 'update'])->name('rack.update');
    // Route::get('/delete-rack/{id}', [ErpRackController::class, 'delete'])->name('rack.delete');

    // //Erp Shelf Routeshelves
    // Route::get('/shelves', [ErpShelfController::class, 'index'])->name('shelves');
    // Route::get('/shelf-create', [ErpShelfController::class, 'create'])->name('shelf_create');
    // Route::post('/shelves/store', [ErpShelfController::class, 'store'])->name('shelves.store');
    // Route::get('/edit-shelf/{id}', [ErpShelfController::class, 'edit'])->name('shelfEdit');
    // Route::post('/update-shelf/{id}', [ErpShelfController::class, 'update'])->name('shelf.update');
    // Route::get('/delete-shelf/{id}', [ErpShelfController::class, 'delete'])->name('shelf.delete');
    // Route::get('/racks-data', [ErpShelfController::class, 'getRacksData'])->name('racks.data');
    // Route::get('/shelf-data', [ErpShelfController::class, 'getShelvesData'])->name('shelfs.data');

    // //Erp Bin Route
    // Route::get('/bins', [ErpBinController::class, 'index'])->name('bins');
    // Route::get('/bin-create', [ErpBinController::class, 'create'])->name('bin_create');
    // Route::post('/bins/store', [ErpBinController::class, 'store'])->name('bins.store');
    // Route::get('/edit-bin/{id}', [ErpBinController::class, 'edit'])->name('binEdit');
    // Route::post('/update-bin/{id}', [ErpBinController::class, 'update'])->name('bin.update');
    // Route::get('/delete-bin/{id}', [ErpBinController::class, 'delete'])->name('bin.delete');


    Route::prefix('vendors')->controller(VendorController::class)->group(function () {
        Route::get('/', 'index')->name('vendor.index');
        Route::get('/create', 'create')->name('vendor.create');
        Route::post('/', 'store')->name('vendor.store');
        Route::post('/revoke', 'revoke')->name('vendor.revoke');
        Route::post('/generate-item-code', 'generateItemCode')->name('generate-vendor-code');
        Route::get('/check-gst', 'checkGst')->name('check-gst');
        Route::get('/search', 'getVendor')->name('vendors.search');
        Route::get('/ledger/{ledgerId}/groups', 'getLedgerGroupsByType')->name('ledgers.group.by.ledger');
        Route::get('/ledgers/group-by-type', 'getLedgerGroupsByType')->name('ledgers.group.default');
        Route::get('/import', 'showImportForm')->name('vendors.import');
        Route::post('/import', 'import')->name('vendors.import.post');
        Route::get('export-successful-vendors', 'exportSuccessfulVendors')->name('vendors.export.successful');
        Route::get('export-failed-vendors', 'exportFailedVendors')->name('vendors.export.failed');
        ;
        Route::get('/{id}', 'show')->name('vendor.show');
        Route::get('/{id}/edit', 'edit')->name('vendor.edit');
        Route::put('/{id}', 'update')->name('vendor.update');
        Route::delete('/{id}', 'destroy')->name('vendor.destroy');
        Route::delete('/vendor-items/{id}', 'deleteVendorItem')->name('vendor.vendor-item.destroy');
        Route::delete('/bank-info/{id}', 'deleteBankInfo')->name('vendor.bank-info.destroy');
        Route::delete('/contacts/{id}', 'deleteContact')->name('vendor.contacts.delete');
        Route::delete('/address/{id}', 'deleteAddress')->name('vendor.address.delete');
        Route::get('/{vendorId}/compliance-by-country/{countryId}', 'getComplianceByCountry');
        Route::get('/compliance/{id}', 'getComplianceById');
        Route::post('/get-uoms', 'getUOM')->name('send.uom');
        Route::get('/states/{country_id}', 'getStates')->name('vendor.get.states');
    });

    // Route::prefix('vendor-new')->controller(VendorController::class)->group(function () {
    //     Route::get('/', 'index')->name('vendor.index');
    // });

    // Route::prefix('vendors')->controller(VendorController::class)->group(function () {
    //     Route::get('/', 'index')->name('vendor.index');
    //     Route::get('/create', 'create')->name('vendor.create');
    //     Route::post('/', 'store')->name('vendor.store');
    //     Route::get('/search', 'getVendor')->name('vendors.search');
    //     Route::get('/{id}', 'show')->name('vendor.show');
    //     Route::get('/{id}/edit', 'edit')->name('vendor.edit');
    //     Route::put('/{id}', 'update')->name('vendor.update');
    //     Route::delete('/{id}', 'destroy')->name('vendor.destroy');
    //     Route::get('/states/{country_id}', 'getStates')->name('vendor.get.states');
    //     Route::get('/cities/{state_id}', 'getCities')->name('vendor.get.cities');
    //     Route::get('/{vendorId}/compliance-by-country/{countryId}', 'getComplianceByCountry');
    //     Route::get('/compliance/{id}', 'getComplianceById');

    // });

    Route::prefix('customers')->controller(CustomerController::class)->group(function () {
        Route::get('/', 'index')->name('customer.index');
        Route::get('/create', 'create')->name('customer.create');
        Route::post('/', 'store')->name('customer.store');
        Route::get('/import', 'showImportForm')->name('customers.import');
        Route::post('/import', 'import')->name('customers.import.post');
        Route::post('/revoke', 'revoke')->name('customer.revoke');
        Route::get('export-successful-customers', 'exportSuccessfulCustomers')->name('customers.export.successful');
        ;
        Route::get('export-failed-customers', 'exportFailedCustomers')->name('customers.export.failed');
        ;
        Route::post('/generate-item-code', 'generateCustomerCode')->name('generate-customer-code');
        Route::get('/search', 'getCustomer')->name('customers.search');
        Route::get('/{id}', 'show')->name('customer.show');
        Route::get('/{id}/edit', 'edit')->name('customer.edit');
        Route::put('/{id}', 'update')->name('customer.update');
        Route::delete('/{id}', 'destroy')->name('customer.destroy');
        Route::delete('/customer-items/{id}', 'deleteCustomerItem')->name('customer-item.destroy');
        Route::delete('/bank-info/{id}', 'deleteBankInfo')->name('customer.bank-info.destroy');
        Route::delete('/contacts/{id}', 'deleteContact')->name('customer.contacts.delete');
        Route::delete('/address/{id}', 'deleteAddress')->name('customer.address.delete');
        Route::get('/states/{country_id}', 'getStates')->name('customer.get.states');
        Route::get('/cities/{state_id}', 'getCities')->name('customer.get.cities');
        Route::get('/states/{country_id}', 'getStates');
        Route::get('/cities/{state_id}', 'getCities');
        Route::get('/{customerId}/compliance-by-country/{countryId}', 'getComplianceByCountry');
        Route::get('/compliance/{id}', 'getComplianceById');
    });

    // Route::prefix('pos')->controller(PurchaseOrderController::class)->group(function () {
    //     // Route::get('/', 'index')->name('po.index');
    //     Route::get('/create', 'create')->name('po.create');
    //     Route::get('/dropdown-data', 'getDropdownData');
    //     Route::post('/', 'store')->name('po.store');
    //     Route::get('/{id}', 'show')->name('po.show');
    //     Route::get('/{id}/edit', 'edit')->name('po.edit');
    //     Route::get('/get_purchase_order_no/{book_id}', 'get_purchase_order_no')->name('get_purchase_order_no');
    //     Route::put('/{id}', 'update')->name('po.update');
    //     Route::delete('/{id}', 'destroy')->name('po.destroy');
    // });


    // Route::prefix('pos')->controller(PurchaseOrderReportController::class)->group(function () {
    //     Route::get('/report', 'index')->name('po.report');
    // });


    // // po report po.report
    // Route::get('/get-attribute-values/{attributeId}', [PurchaseOrderReportController::class, 'getAttributeValues'])->name('po.report.getattributevalues');
    // Route::get('/pos/report/filter', [PurchaseOrderReportController::class, 'getPurchaseOrdersFilter'])->name('po.report.filter');
    // Route::post('/pos/add-scheduler', [PurchaseOrderReportController::class, 'addScheduler'])->name('po.add.scheduler');
    // Route::get('/pos/report-send/mail', [PurchaseOrderReportController::class, 'sendReportMail'])->name('po.send.report');

    Route::prefix('{type}')
        ->where(['type' => 'purchase-order|supplier-invoice'])
        ->name('po.')
        ->controller(PoController::class)
        ->group(function () {
            Route::get('revoke-document', 'revokeDocument')->name('revoke.document');
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('add-item-row', 'addItemRow')->name('item.row');
            Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
            Route::get('add-discount-row', 'addDiscountRow')->name('item.discount.row');
            Route::get('/tax-calculation', 'taxCalculation')->name('tax.calculation');
            Route::get('/get-address', 'getAddress')->name('get.address');
            Route::get('/edit-address', 'editAddress')->name('edit.address');
            Route::get('/get-itemdetail', 'getItemDetail')->name('get.itemdetail');
            Route::post('/address-save', 'addressSave')->name('address.save');
            Route::delete('component-delete', 'componentDelete')->name('comp.delete');
            Route::get('/{id}/pdf', 'generatePdf')->name('generate-pdf');
            Route::get('amendment-submit/{id}', 'amendmentSubmit')->name('amendment.submit');
            Route::get('get-purchase-indent', 'getPi')->name('get.pi');
            Route::get('get-purchase-indent-bulk', 'getPiBulk')->name('get.pi.bulk');
            Route::get('process-pi-item', 'processPiItem')->name('process.pi-item');
            Route::delete('remove-dis-item-level', 'removeDisItemLevel')->name('remove.item.dis');
            Route::delete('remove-dis-header-level', 'removeDisHeaderLevel')->name('remove.header.dis');
            Route::delete('remove-exp-header-level', 'removeExpHeaderLevel')->name('remove.header.exp');
            Route::post('short-close-submit', 'shortCloseSubmit')->name('short.close.submit');
            Route::get('bulk-create', 'bulkCreate')->name('bulk.create');
            Route::post('bulk-store', 'bulkStore')->name('bulk.store');
            Route::post('send-mail', 'poMail')->name('poMail');
            Route::get('report', 'poReport')->name('report');
        });

    Route::prefix('job-order')
        ->name('jo.')
        ->controller(JoController::class)
        ->group(function () {
            Route::get('revoke-document', 'revokeDocument')->name('revoke.document');
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('add-item-row', 'addItemRow')->name('item.row');
            Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
            Route::get('add-discount-row', 'addDiscountRow')->name('item.discount.row');
            Route::get('/tax-calculation', 'taxCalculation')->name('tax.calculation');
            Route::get('/get-address', 'getAddress')->name('get.address');
            Route::get('/edit-address', 'editAddress')->name('edit.address');
            Route::get('/get-itemdetail', 'getItemDetail')->name('get.itemdetail');
            Route::get('/get-itemdetail2', 'getItemDetail2')->name('get.itemdetail2');
            Route::post('/address-save', 'addressSave')->name('address.save');
            Route::delete('component-delete', 'componentDelete')->name('comp.delete');
            Route::get('/{id}/pdf', 'generatePdf')->name('generate-pdf');
            Route::get('amendment-submit/{id}', 'amendmentSubmit')->name('amendment.submit');
            Route::get('get-purchase-indent', 'getPi')->name('get.pi');
            Route::get('process-pi-item', 'processPiItem')->name('process.pi-item');
            Route::delete('remove-dis-item-level', 'removeDisItemLevel')->name('remove.item.dis');
            Route::delete('remove-dis-header-level', 'removeDisHeaderLevel')->name('remove.header.dis');
            Route::delete('remove-exp-header-level', 'removeExpHeaderLevel')->name('remove.header.exp');
            Route::post('short-close-submit', 'shortCloseSubmit')->name('short.close.submit');
            Route::post('send-mail', 'poMail')->name('poMail');
            Route::get('report', 'poReport')->name('report');
            Route::get('check-bom-job', 'checkBomJob')->name('check.bom.job');
        });

    # Manufacturing Order
    Route::prefix('manufacturing-order')
        ->name('mo.')
        ->controller(MoController::class)
        ->group(function () {
            Route::get('revoke-document', 'revokeDocument')->name('revoke.document');
            Route::post('close-document', 'closeDocument')->name('close.document');
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('store', 'store')->name('store');
            Route::get('change-item-code', 'changeItemCode')->name('item.code');
            Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
            Route::get('add-item-row', 'addItemRow')->name('item.row');
            Route::get('get-item-detail', 'getItemDetail')->name('get.itemdetail');
            Route::get('get-item-detail2', 'getItemDetail2')->name('get.itemdetail2');
            Route::get('get-doc-no', 'getDocNumber')->name('doc.no');
            Route::get('edit/{id}', 'edit')->name('edit');
            Route::post('update/{id}', 'update')->name('update');
            # get bom item cost child item
            Route::get('/{id}/pdf/{pattern}', 'generatePdf')->name('generate-pdf');
            Route::get('get-posting', 'getPostingDetails')->name('posting.get');
            Route::get('post-mo', 'postMo')->name('posting.post');
            Route::get('get-pwo', 'getPwo')->name('get.pwo');
            Route::get('get-pwo-create', 'getPwoCreate')->name('get.pwo.create');
            Route::get('process-pwo-item', 'processPwoItem')->name('process.pwo-item');
            Route::get('get-sub-store', 'getSubStore')->name('get.sub.store');
            Route::get('report', 'moReport')->name('report');
            Route::get('get-machine-detail', 'getMachineDetail')->name('get.machine.detail');
            Route::get('check-bom-inhouse', 'checkBomInhouse')->name('check.bom.inhouse');
        });




    Route::prefix('transporter-requests')
        ->name('transporter.')
        ->controller(ErpTransporterRequestController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::get('/view/{id}', 'view')->name('view');
            Route::post('/store', 'store')->name('store');
            Route::post('/shortlist', 'shortlist')->name('shortlist');
            Route::get('/address', 'get_address')->name('get-address');
            Route::get('/state', 'get_state')->name('get-state');
            Route::get('/city', 'get_city')->name('get-city');
            Route::post('/get-locations', 'get_locations')->name('get-locations');
            Route::post('/closeBid', 'closeBid')->name('closeBid');
            Route::post('/reOpenBid', 'reOpenBid')->name('reOpenBid');
            Route::post('/generate_pdf', 'generate_pdf')->name('generate-pdf');
        });


    Route::prefix('purchase-indent')
        ->name('pi.')
        ->controller(PiController::class)
        ->group(function () {
            Route::get('revoke-document', 'revokeDocument')->name('revoke.document');
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::post('/update-approve/{id}', 'updateApprove')->name('update.approve');
            Route::get('add-item-row', 'addItemRow')->name('item.row');
            Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
            Route::get('/get-itemdetail', 'getItemDetail')->name('get.itemdetail');
            Route::get('/{id}/pdf', 'generatePdf')->name('generate-pdf');

            Route::get('get-so', 'getSo')->name('get.so');
            Route::get('analyze-so-item', 'analyzeSoItem')->name('analyze.so-item');
            Route::get('process-so-item', 'processSoItem')->name('process.so-item');
            Route::post('process-so-item-submit', 'processSoItemSubmit')->name('process.so-item.submit');
            Route::get('get-selected-department', 'getSelectedDepartment')->name('get.selected.department');
            Route::get('report', 'piReport')->name('report');
            Route::get('amend', 'piAmend')->name('amend');
        });

    Route::prefix('scrap')
        ->name('scrap.')
        ->controller(ScrapController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index')->middleware('check.service.access');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit')->middleware('check.service.access');
            Route::get('/create', 'create')->name('create')->middleware('check.service.access');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/{id}/pdf', 'generatePdf')->name('generate-pdf')->middleware('check.service.access');
            Route::get('/get-itemdetail', 'getItemDetail')->name('get.itemdetail');
            Route::post('/update-approve/{id}', 'updateApprove')->name('update.approve');
            Route::delete('/{id}/{isAmedment}', 'destroy')->name('destroy');

            Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
            Route::get('revoke-document', 'revokeDocument')->name('revoke.document');
            Route::get('add-item-row', 'addItemRow')->name('item.row');
            Route::get('get-ps', 'getps')->name('get.ps');
            Route::get('process-item', 'processItem')->name('process.item');
            Route::post('process-pi-item-submit', 'processPiItemSubmit')->name('process.pi-item.submit');
        });


    // Route::prefix('pos')->controller(PurchaseOrderReportController::class)->group(function () {
    //     Route::get('/report', 'index')->name('po.report');
    // });
    //Route::get('/pos/report', [PurchaseOrderReportController::class, 'index'])->name('po.report');
    // Route::get('/test', function () {
    //     return 'Test route works!';
    // })->name('po.report');


    Route::prefix('machine')->controller(ErpMachineController::class)->group(function () {
        Route::get('/', 'index')->name('machine.index');
        Route::get('/create', 'create')->name('machine.create');
        Route::get('/edit/{id}', 'edit')->name('machine.edit');
        Route::post('/store', 'store')->name('machine.store');
        Route::put('/{id}', 'update')->name('machine.update');
        Route::get('/attribute/values', 'attributeValues')->name('machine.attribute.values');
    });

    Route::prefix('items')->controller(ItemController::class)->group(function () {
        Route::get('get-cost', 'getItemCost')->name('items.get.cost');
        Route::get('/', 'index')->name('item.index');
        Route::get('/export', 'export')->name('item.export');
        Route::get('/create', 'create')->name('item.create');
        Route::post('/', 'store')->name('item.store');
        Route::post('/revoke', 'revoke')->name('item.revoke');
        Route::get('/import', 'showImportForm')->name('items.show.import');
        Route::get('export-successful-items', 'exportSuccessfulItems')->name('items.export.successful');
        ;
        Route::get('export-failed-items', 'exportFailedItems')->name('items.export.failed');
        Route::post('/import', 'import')->name('items.import');
        Route::post('/generate-item-code', 'generateItemCode')->name('generate-item-code');
        Route::get('/search', 'getItem')->name('items.search');
        Route::get('/get-asset-data/{categoryId}', 'getAssetDataForCategory');
        Route::get('/{id}', 'show')->name('item.show');
        Route::get('/{id}/edit', 'edit')->name('item.edit');
        Route::put('/{id}', 'update')->name('item.update');
        Route::delete('/{id}', 'destroy')->name('item.destroy');
        Route::delete('/alternate-uom/delete/{id}', 'deleteAlternateUOM')->name('items.alternate-uom.delete');
        Route::delete('/approved-customer/delete/{id}', 'deleteApprovedCustomer')->name('items.approved-customer.delete');
        Route::delete('/approved-vendor/delete/{id}', 'deleteApprovedVendor')->name('items.approved-vendor.delete');
        Route::delete('/attribute/delete/{id}', 'deleteAttribute')->name('items.attribute.delete');
        Route::delete('/alternate-item/delete/{id}', 'deleteAlternateItem')->name('items.alternate-item.delete');
        Route::post('/get-uom', 'getUOM')->name('send.uom');
    });

    Route::prefix('hsn')->controller(HsnController::class)->group(function () {
        Route::get('/', 'index')->name('hsn.index');
        Route::get('/create', 'create')->name('hsn.create');
        Route::post('/', 'store')->name('hsn.store');
        Route::get('/{id}/edit', 'edit')->name('hsn.edit');
        Route::put('/{id}', 'update')->name('hsn.update');
        Route::delete('/{id}', 'destroy')->name('hsn.destroy');
        Route::delete('/hsn-detail/{id}', 'deleteHsnDetail')->name('hsn-detail.delete');
    });

    Route::prefix('categories')->controller(CategoryController::class)->group(function () {
        Route::get('/', 'index')->name('categories.index');
        Route::get('/create', 'create')->name('categories.create');
        Route::post('/', 'store')->name('categories.store');
        Route::get('/by-type', 'getCategoriesByType')->name('categories.byType');
        Route::get('/get-hsn', 'getHsnByParent')->name('categories.getHsnByParent');
        Route::get('/{id}/edit', 'edit')->name('categories.edit');
        Route::put('/{id}', 'update')->name('categories.update');
        Route::delete('/{id}', 'destroy')->name('categories.destroy');
        Route::delete('/subcategory/{id}', 'deleteSubcategory')->name('subcategory.delete');
        Route::get('/subcategories/{categoryId}', 'getSubcategories')->name('categories.subcategory');
    });

    Route::prefix('equipment-categories')->controller(CategoryController::class)->group(function () {
        Route::get('/', 'index')->name('equipment-categories.index');
        Route::get('/create', 'create')->name('equipment-categories.create');
        Route::get('/{id}/edit', 'edit')->name('equipment-categories.edit');
    });

    Route::prefix('payment-terms')->controller(PaymentTermController::class)->group(function () {
        Route::get('/', 'index')->name('payment-terms.index');
        Route::post('/', 'store')->name('payment-terms.store');
        Route::get('/create', 'create')->name('payment-terms.create');
        Route::get('/{id}/edit', 'edit')->name('payment-terms.edit');
        Route::get('/{id}', 'show')->name('payment-terms.show');
        Route::put('/{id}', 'update')->name('payment-terms.update');
        Route::delete('/payment-term-detail/{id}', 'deletePaymentTermDetail')->name('payment-term-detail.delete');
        Route::delete('/{id}', 'destroy')->name('payment-terms.destroy');
        Route::get('/{categoryId}/sub-payment-terms', 'getSubPaymentTerms')->name('payment-terms.sub-payment-terms');
    });

    Route::prefix('units')->controller(UnitController::class)->group(function () {
        Route::get('/', 'index')->name('units.index');
        Route::get('/create', 'create')->name('units.create');
        Route::post('/', 'store')->name('units.store');
        Route::get('/{id}', 'show')->name('units.show');
        Route::get('/{id}/edit', 'edit')->name('units.edit');
        Route::put('/{id}', 'update')->name('units.update');
        Route::delete('/{id}', 'destroy')->name('units.destroy');
    });

    Route::prefix('item-bundles')->controller(ErpItemBundleController::class)->group(function () {
        Route::get('/', 'index')->name('item-bundles.index');
        Route::get('/create', 'create')->name('item-bundles.create');
        Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
        Route::post('/', 'store')->name('item-bundles.store');
        Route::get('/{item_bundle}/edit', 'edit')->name('item-bundles.edit');
        Route::put('/{item_bundle}', 'update')->name('item-bundles.update');
        Route::delete('/{item_bundle}', 'destroy')->name('item-bundles.destroy');
    });

    Route::prefix('rgr')->controller(RgrController::class)->group(function () {
        Route::get('/', 'index')->name('rgr.index');
        Route::get('/create', 'create')->name('rgr.create');
        Route::post('/', 'store')->name('rgr.store');
        Route::get('/{id}/edit', 'edit')->name('rgr.edit');
        Route::put('/{id}', 'update')->name('rgr.update');
        Route::delete('/{id}', 'destroy')->name('rgr.destroy');

        // Extra custom routes
        Route::get('get-doc-no', 'getDocNumber')->name('rgr.doc.no');
        Route::get('get-posting', 'getPostingDetails')->name('rgr.posting');
        Route::get('get-pickup-item', 'getPickupScheduleItems')->name('rgr.get.pickup.item');
        Route::post('process-rgr-item', 'processPickupItem')->name('rgr.process.pickup-schdule-list');
        Route::get('revoke-document', 'revokeDocument')->name('rgr.revoke');
    });

    Route::prefix('erp-document')->controller(DocumentController::class)->group(function () {
        Route::get('/', 'index')->name('documents.index');
        Route::get('/create', 'create')->name('documents.create');
        Route::post('/', 'store')->name('documents.store');
        Route::get('/{id}', 'show')->name('documents.show');
        Route::get('/{id}/edit', 'edit')->name('documents.edit');
        Route::put('/{id}', 'update')->name('documents.update');
        Route::delete('/{id}', 'destroy')->name('documents.destroy');
    });


    Route::prefix('stakeholder')->controller(StakeholderController::class)->group(function () {
        Route::get('/', 'index')->name('stakeholder.index');
        Route::get('/create', 'create')->name('stakeholder.create');
        Route::post('/', 'store')->name('stakeholder.store');
        Route::get('/{id}', 'show')->name('stakeholder.show');
        Route::get('/{id}/edit', 'edit')->name('stakeholder.edit');
        Route::put('/{id}', 'update')->name('stakeholder.update');
        Route::delete('/{id}', 'destroy')->name('stakeholder.destroy');
    });
    Route::prefix('complaint')->controller(ComplaintManagementController::class)->group(function () {
        Route::get('/', 'index')->name('complaint.index');
        Route::get('/create', 'create')->name('complaint.create');
        Route::post('/', 'store')->name('complaint.store');
        Route::get('/{id}', 'show')->name('complaint.show');
        Route::get('/{id}/edit', 'edit')->name('complaint.edit');
        Route::put('/{id}', 'update')->name('complaint.update');
        Route::delete('/{id}', 'destroy')->name('complaint.destroy');
    });
    Route::prefix('feedback-process')->controller(FeedbackProcessController::class)->group(function () {
        Route::get('/', 'index')->name('feedback-process.index');
        Route::get('/create', 'create')->name('feedback-process.create');
        Route::post('/', 'store')->name('feedback-process.store');
        Route::get('/{id}', 'show')->name('feedback-process.show');
        Route::get('/{id}/edit', 'edit')->name('feedback-process.edit');
        Route::put('/{id}', 'update')->name('feedback-process.update');
        Route::delete('/{id}', 'destroy')->name('feedback-process.destroy');
    });

    Route::prefix('engagement-tracking')->controller(\App\Http\Controllers\ErpEngagementTrackingController::class)->group(function () {
        Route::get('/', 'index')->name('engagement-tracking.index');
        Route::get('/create', 'create')->name('engagement-tracking.create');
        Route::post('/', 'store')->name('engagement-tracking.store');
        Route::get('/{id}', 'show')->name('engagement-tracking.show');
        Route::get('/{id}/edit', 'edit')->name('engagement-tracking.edit');
        Route::put('/{id}', 'update')->name('engagement-tracking.update');
        Route::delete('/{id}', 'destroy')->name('engagement-tracking.destroy');
    });
    Route::prefix('relation-management')->controller(\App\Http\Controllers\ErpInvestorRelationManagementController::class)->group(function () {
        Route::get('/', 'index')->name('relation-management.index');
        Route::get('/create', 'create')->name('relation-management.create');
        Route::post('/', 'store')->name('relation-management.store');
        Route::get('/{id}', 'show')->name('relation-management.show');
        Route::get('/{id}/edit', 'edit')->name('relation-management.edit');
        Route::put('/{id}', 'update')->name('relation-management.update');
        Route::delete('/{id}', 'destroy')->name('relation-management.destroy');
    });
    Route::prefix('gov-relation-management')->controller(\App\Http\Controllers\ErpGovRelationManagementController::class)->group(function () {
        Route::get('/', 'index')->name('gov-relation-management.index');
        Route::get('/create', 'create')->name('gov-relation-management.create');
        Route::post('/', 'store')->name('gov-relation-management.store');
        Route::get('/{id}', 'show')->name('gov-relation-management.show');
        Route::get('/{id}/edit', 'edit')->name('gov-relation-management.edit');
        Route::put('/{id}', 'update')->name('gov-relation-management.update');
        Route::delete('/{id}', 'destroy')->name('gov-relation-management.destroy');
    });

    Route::prefix('public-outreach')->controller(ErpPublicOutreachAndCommunicationController::class)->group(function () {
        Route::get('/', 'index')->name('public-outreach.index');
        Route::get('/create', 'create')->name('public-outreach.create');
        Route::post('/', 'store')->name('public-outreach.store');
        Route::get('/{id}', 'show')->name('public-outreach.show');
        Route::get('/{id}/edit', 'edit')->name('public-outreach.edit');
        Route::put('/{id}', 'update')->name('public-outreach.update');
        Route::delete('/{id}', 'destroy')->name('public-outreach.destroy');
    });


    Route::prefix('attributes')->controller(AttributeController::class)->group(function () {
        Route::get('/', 'index')->name('attributes.index');
        Route::get('/create', 'create')->name('attributes.create');
        Route::post('/store', 'store')->name('attributes.store');
        Route::get('/{group_id}', 'getAttributesByGroup')->name('attributes.byGroup');
        Route::get('/{id}', 'show')->name('attributes.show');
        Route::get('/{id}/edit', 'edit')->name('attributes.edit');
        Route::put('/{id}', 'update')->name('attributes.update');
        Route::delete('/attributes-detail/{id}', 'deleteAttributeDetail')->name('attribute-detail.delete');
        Route::delete('/{id}', 'destroy')->name('attributes.destroy');
    });

    // ErpDprTemplateMaster
    Route::prefix('dpr-templates')->name('dpr-template.')->controller(DPRTemplateController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}/update', 'update')->name('update');
    });

    // ErpDprMaster
    Route::prefix('dpr-master')->name('dpr-master.')->controller(ErpDprMasterController::class)->group(function () {
        Route::get('/index', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}/update', 'update')->name('update');
        // Route::get('/{group_id}', 'getAttributesByGroup')->name('attributes.byGroup');
        // Route::get('/{id}', 'show')->name('attributes.show');
        // Route::get('/{id}/edit', 'edit')->name('attributes.edit');
        // Route::put('/{id}', 'update')->name('attributes.update');
        // Route::delete('/attributes-detail/{id}', 'deleteAttributeDetail')->name('attribute-detail.delete');
        // Route::delete('/{id}', 'destroy')->name('attributes.destroy');
    });

    Route::prefix('stock-accounts')->controller(StockAccountController::class)->group(function () {
        Route::get('/', 'index')->name('stock-accounts.index');
        Route::post('/', 'store')->name('stock-accounts.store');
        Route::get('test-ledger', 'testLedgerGroupAndLedgerId');
        Route::delete('/{id}', 'destroy')->name('stock-accounts.destroy');
        Route::get('organizations/{companyId}', 'getOrganizationsByCompany');
        Route::get('data-by-organization/{organizationId}', 'getDataByOrganization');
        Route::get('items-and-subcategories-by-category', 'getItemsAndSubCategoriesByCategory');
        Route::get('items-by-subcategory', 'getItemsBySubCategory');
        Route::get('ledgers-by-organization/{organizationId}', 'getLedgersByOrganization');
        Route::get('categories-by-organization/{organizationId}', 'getCategoriesByOrganization');
        Route::get('sub-categories-by-category/{categoryId}', 'getSubcategoriesByCategory');
        Route::get('ledgers-by-group', 'getLedgerGroupByLedger');
    });

    Route::prefix('cogs-accounts')->controller(CogsAccountController::class)->group(function () {
        Route::get('/', 'index')->name('cogs-accounts.index');
        Route::post('/', 'store')->name('cogs-accounts.store');
        Route::delete('/{id}', 'destroy')->name('cogs-accounts.destroy');
        Route::get('/test-ledger', 'testLedgerGroupAndLedgerId')->name('cogs-accounts.test-ledger');
    });

    Route::prefix('gr-accounts')->controller(GrAccountController::class)->group(function () {
        Route::get('/', 'index')->name('gr-accounts.index');
        Route::post('/', 'store')->name('gr-accounts.store');
        Route::delete('/{id}', 'destroy')->name('gr-accounts.destroy');
        Route::get('/test-ledger', 'testLedgerGroupAndLedgerId')->name('gr-accounts.test-ledger');
    });


    Route::prefix('sales-accounts')->controller(SalesAccountController::class)->group(function () {
        Route::get('/', 'index')->name('sales-accounts.index');
        Route::post('/', 'store')->name('sales-accounts.store');
        Route::delete('/{id}', 'destroy')->name('sales-accounts.destroy');
        Route::get('test-ledger', 'testLedgerGroupAndLedgerId');
        Route::get('organizations/{companyId}', 'getOrganizationsByCompany');
        Route::get('data-by-organization/{organizationId}', 'getDataByOrganization');
        Route::get('customer-subcategories-by-category', 'getCustomerAndSubCategoriesByCategory');
        Route::get('customer-by-subcategory', 'getCustomerBySubCategory');
        Route::get('item-subcategories-by-category', 'getItemsAndSubCategoriesByCategory');
        Route::get('items-by-subcategory', 'getItemsBySubCategory');
        Route::get('ledgers-by-organization/{organizationId}', 'getLedgersByOrganization');
        Route::get('categories-by-organization/{organizationId}', 'getCategoriesByOrganization');
        Route::get('subcategories-by-category/{categoryId}', 'getSubcategoriesByCategory');
        Route::get('ledgers-by-group', 'getLedgerGroupByLedger');
    });

    Route::prefix('wip-accounts')->controller(WipAccountController::class)->group(function () {
        Route::get('/', 'index')->name('wip-accounts.index');
        Route::post('/', 'store')->name('wip-accounts.store');
        Route::delete('/{id}', 'destroy')->name('wip-accounts.destroy');
        Route::get('test-ledger', 'testLedgerGroupAndLedgerId')->name('wip-accounts.test-ledger');
    });

    Route::prefix('price-variance-accounts')->controller(PriceVarianceAccountController::class)->group(function () {
        Route::get('/', 'index')->name('price-variance-accounts.index');
        Route::post('/', 'store')->name('price-variance-accounts.store');
        Route::delete('/{id}', 'destroy')->name('price-variance-accounts.destroy');
        Route::get('/test-ledger', 'testLedgerGroupAndLedgerId')->name('price-variance-accounts.test-ledger');
    });

    Route::prefix('purchase-return-accounts')->controller(PurchaseReturnAccountController::class)->group(function () {
        Route::get('/', 'index')->name('purchase-return-accounts.index');
        Route::post('/', 'store')->name('purchase-return-accounts.store');
        Route::delete('/{id}', 'destroy')->name('purchase-return-accounts.destroy');
        Route::get('/test-ledger', 'testLedgerGroupAndLedgerId')->name('purchase-return-accounts.test-ledger');
    });

    Route::prefix('service-accounts')->controller(ServiceAccountController::class)->group(function () {
        Route::get('/', 'index')->name('service-accounts.index');
        Route::post('/', 'store')->name('service-accounts.store');
        Route::delete('/{id}', 'destroy')->name('service-accounts.destroy');
        Route::get('/test-ledger', 'testLedgerGroupAndLedgerId')->name('service-accounts.test-service');
        Route::get('organizations/{companyId}', 'getOrganizationsByCompany')->name('service-accounts.organizations.by-company');
        Route::get('data-by-organization/{organizationId}', 'getDataByOrganization')->name('service-accounts.data.by-organization');
        Route::get('items-and-subcategories-by-category', 'getItemsAndSubCategoriesByCategory')->name('service-accounts.items-and-subcategories.by-category');
        Route::get('items-by-subcategory', 'getItemsBySubCategory')->name('service-accounts.items.by-subcategory');
        Route::get('ledgers-by-organization/{organizationId}', 'getLedgersByOrganization')->name('service-accounts.ledgers.by-organization');
        Route::get('categories-by-organization/{organizationId}', 'getCategoriesByOrganization')->name('service-accounts.categories.by-organization');
        Route::get('sub-categories-by-category/{categoryId}', 'getSubcategoriesByCategory')->name('service-accounts.subcategories.by-category');
        Route::get('ledgers-by-group', 'getLedgerGroupByLedger')->name('service-accounts.ledgers.by-group');
    });

    Route::prefix('physical-stock-accounts')->controller(PhysicalStockAccountController::class)->group(function () {
        Route::get('/', 'index')->name('physical-stock-account.index');
        Route::post('/', 'store')->name('physical-stock-account.store');
        Route::delete('/{id}', 'destroy')->name('physical-stock-account.destroy');
        Route::get('/test-ledger', 'testLedgerGroupAndLedgerId')->name('physical-stock-account.test-stock');
        Route::get('organizations/{companyId}', 'getOrganizationsByCompany')->name('physical-stock-account.organizations.by-company');
        Route::get('data-by-organization/{organizationId}', 'getDataByOrganization')->name('physical-stock-account.data.by-organization');
        Route::get('items-and-subcategories-by-category', 'getItemsAndSubCategoriesByCategory')->name('physical-stock-account.items-and-subcategories.by-category');
        Route::get('items-by-subcategory', 'getItemsBySubCategory')->name('physical-stock-account.items.by-subcategory');
        Route::get('ledgers-by-organization/{organizationId}', 'getLedgersByOrganization')->name('physical-stock-account.ledgers.by-organization');
        Route::get('categories-by-organization/{organizationId}', 'getCategoriesByOrganization')->name('physical-stock-account.categories.by-organization');
        Route::get('sub-categories-by-category/{categoryId}', 'getSubcategoriesByCategory')->name('physical-stock-account.subcategories.by-category');
        Route::get('ledgers-by-group', 'getLedgerGroupByLedger')->name('physical-stock-account.ledgers.by-group');
    });

    Route::get('/loan', [LoanController::class, 'index']);
    Route::get('/bookType', [BookTypeController::class, 'index'])->name('book-type.index');
    Route::get('/bookType/create', [BookTypeController::class, 'create_bookType'])->name('bookType.create');
    Route::post('/bookType/store', [BookTypeController::class, 'store'])->name('bookTypeStore');
    Route::get('/bookType/edit/{id}', [BookTypeController::class, 'edit_bookType'])->name('bookTypeEdit');
    Route::post('/bookType/update/{id}', [BookTypeController::class, 'update_bookType'])->name('book-type.update');
    Route::get('/bookType/delete/{id}', [BookTypeController::class, 'destroy_bookType'])->name('book-type.delete');


    Route::get('/org-services', [OrganizationServiceController::class, 'index'])->name('org-services.index');
    Route::get('/org-services/edit/{id}', [OrganizationServiceController::class, 'edit'])->name('org-service.edit');
    Route::post('/org-services/update/{id}', [OrganizationServiceController::class, 'update'])->name('org-service.update');

    Route::get('/issue-type', [IssueTypeController::class, 'index'])->name('issue-type.index');
    Route::get('/issue-type/create', [IssueTypeController::class, 'create_issueType'])->name('issueType.create');
    Route::post('/issue-type/store', [IssueTypeController::class, 'store'])->name('issueTypeStore');
    Route::get('/issue-type/edit/{id}', [IssueTypeController::class, 'edit_issueType'])->name('issueTypeEdit');
    Route::post('/issue-type/update/{id}', [IssueTypeController::class, 'update_issueType'])->name('issue-type.update');
    Route::get('/issue-type/delete/{id}', [IssueTypeController::class, 'destroy_issueType'])->name('issue-type.delete');

    Route::get('/book', [BookController::class, 'index'])->name('book');
    Route::get('/bookCreate', [BookController::class, 'book_create'])->name('book_create');
    Route::post('/books/store', [BookController::class, 'store'])->name('books.store');
    Route::get('/editBook/{id}', [BookController::class, 'edit_book'])->name('bookEdit');
    Route::post('/updateBook/{id}', [BookController::class, 'update_book'])->name('book.update');
    Route::get('/deleteBook/{id}', [BookController::class, 'destroy_book'])->name('book.delete');
    Route::post('get_codes', [BookController::class, 'get_codes'])->name('get_codes');
    Route::get('book/get/doc-no-and-parameters', [BookController::class, 'getBookDocNoAndParameters'])->name('book.get.doc_no_and_parameters');
    Route::get('book/doc-no', [BookController::class, 'firstOrNewBookDocNoAndParameters'])->name('book.doc_no');
    Route::get('get/service-params/{serviceId}', [BookController::class, 'getServiceParamForBookCreation'])->name('book.get.service_params');
    Route::get('check/approval-level', [BookController::class, 'checkLevelForChange'])->name('book.approval-level.check');
    Route::get('get/approval-employees', [BookController::class, 'getEmployeesForApprovalOrgWise'])->name('book.approval-employees.get');
    Route::get('get/reference-series', [BookController::class, 'getReferenceSeriesFromReferenceService'])->name('book.reference-series.get');
    Route::get('get/service-series', [BookController::class, 'getSeriesOfService'])->name('book.service-series.get');


    Route::get('/legal', [LegalController::class, 'index'])->name('legal');
    Route::get('/legal/add', [LegalController::class, 'legal_create'])->name('legal.legal_add');
    Route::get('/legal/view/{id}', [LegalController::class, 'legal_view'])->name('legal.legal_view');
    Route::get('/legal/edit/{id}', [LegalController::class, 'edit'])->name('legal.edit');
    Route::post('/legal/update/{id}', [LegalController::class, 'update'])->name('legal.update');
    Route::post('/legal/store', [LegalController::class, 'legal_store'])->name('legal.store');
    Route::post('/legal/assignsubmit', [LegalController::class, 'legal_assignsubmit'])->name('legal.assignsubmit');
    Route::post('/legal/send-message', [LegalController::class, 'sendEmail'])->name('legal.send_message');
    Route::post('/legal/close', [LegalController::class, 'close'])->name('legal.close');
    Route::post('/legal/appr-rej', [LegalController::class, 'ApprReject'])->name('legal.appr_rej');
    Route::get('/get-series/{issue_id}', [LegalController::class, 'getSeries'])->name('get.series');
    Route::get('/get-request/{book_id}', [LegalController::class, 'getRequests'])->name('get.requests');
    Route::get('/search-messages', [LegalController::class, 'searchMessages'])->name('search.messages');
    Route::get('/legal/filter', [LegalController::class, 'filter'])->name('legal.filter');
    Route::get('/search-docs', [LegalController::class, 'searchDocs'])->name('legal.search.docs');
    Route::get('/mailer', [LegalController::class, 'mailer'])->name('legal.mailer');

    // ajax url legal for land or lease
    Route::get('/lease/onLeaseAddFilter', [LegalController::class, 'onLeaseAddFilter'])->name('legal.onLeaseAddFilter');




    //profit loss
    Route::get('plGroup', [ProfitLossController::class, 'plGroup'])->name('finance.plGroup');
    Route::post('plGroup', [ProfitLossController::class, 'plGroupStore'])->name('finance.plgroups.store');
    Route::get('profit-loss-report', [ProfitLossController::class, 'profitLoss'])->name('finance.profitLoss');
    Route::post('getPLInitialGroups', [ProfitLossController::class, 'getPLInitialGroups'])->name('finance.getPLInitialGroups');
    Route::post('getPLGroupLedgers', [ProfitLossController::class, 'getPLGroupLedgers'])->name('finance.getPLGroupLedgers');
    Route::post('getPLGroupLedgersMultiple', [ProfitLossController::class, 'getPLGroupLedgersMultiple'])->name('finance.getPLGroupLedgersMultiple');
    Route::post('exportPLLevel', [ProfitLossController::class, 'exportPLLevel'])->name('finance.exportPLLevel');

    //balance_sheet

    Route::get('balance-sheet-report', [BalanceSheetController::class, 'balanceSheet'])->name('finance.balanceSheet');
    Route::post('balanceSheetInitialGroups', [BalanceSheetController::class, 'balanceSheetInitialGroups'])->name('finance.balanceSheetInitialGroups');
    Route::post('getBalanceSheetLedgers', [BalanceSheetController::class, 'getBalanceSheetLedgers'])->name('finance.getBalanceSheetLedgers');
    Route::post('getBalanceSheetLedgersMultiple', [BalanceSheetController::class, 'getBalanceSheetLedgersMultiple'])->name('finance.getBalanceSheetLedgersMultiple');
    Route::post('exportBalanceSheet', [BalanceSheetController::class, 'exportBalanceSheet'])->name('finance.exportBalanceSheet');

    //landplot
    Route::get('/land-plot', [LandPlotController::class, 'index'])->name('land-plot.index');
    Route::get('/land-plot/filter', [LandPlotController::class, 'filter'])->name('land-plot.filter');
    Route::get('/land-plot/add', [LandPlotController::class, 'create'])->name('land-plot.create');
    Route::get('/land-plot/view/{id}', [LandPlotController::class, 'view'])->name('land-plot.view');
    Route::post('/land-plot/save', [LandPlotController::class, 'save'])->name('land-plot.save');
    Route::get('/land-plot/edit/{id}', [LandPlotController::class, 'edit'])->name('land-plot.edit');
    Route::post('/land-plot/update', [LandPlotController::class, 'update'])->name('land-plot.update');
    Route::get('/findland', [LandPlotController::class, 'search'])->name('land.search');
    Route::post('/plot-appr-rej', [LandPlotController::class, 'ApprReject'])->name('landplot.appr_rej');
    Route::get('land-plot/amendment/{id}', [LandPlotController::class, 'amendment'])->name('land-plot.amendment');


    //landparcel
    Route::get('/land-parcel', [LandParcelController::class, 'index'])->name('land-parcel.index');
    Route::get('/land-parcel/filter', [LandParcelController::class, 'filter'])->name('land-parcel.filter');
    Route::get('/land-parcel/add', [LandParcelController::class, 'create'])->name('land-parcel.create');
    Route::post('/land-parcel/save', [LandParcelController::class, 'save'])->name('land-parcel.save');
    Route::get('/land-parcel/edit/{id}', [LandParcelController::class, 'edit'])->name('land-parcel.edit');
    Route::get('/land-parcel/view/{id}', [LandParcelController::class, 'view'])->name('land-parcel.view');
    Route::post('/land-parcel/update', [LandParcelController::class, 'update'])->name('land-parcel.update');
    Route::post('/appr-rej', [LandParcelController::class, 'ApprReject'])->name('land.appr_rej');
    Route::get('land-parcel/amendment/{id}', [LandParcelController::class, 'amendment'])->name('land-parcel.amendment');


    //land
    Route::get('/land', [LandController::class, 'index'])->name('land');
    Route::get('/land/add', [LandController::class, 'create'])->name('land.add');
    Route::post('/save-land', [LandController::class, 'saveLand'])->name('save.land');
    Route::get('/land/edit/{id}', [LandController::class, 'edit'])->name('land.edit');
    Route::post('/update-land', [LandController::class, 'updateLand'])->name('update.land');
    Route::get('/get-land-request/{book_id}', [LandController::class, 'getRequests'])->name('get.landrequests');

    Route::get('/land/recovery', [LandController::class, 'recovery'])->name('land.recovery');
    Route::get('/land/recovery/add', [LandController::class, 'recoveryadd'])->name('land.recoveryadd');
    Route::get('/land/recovery/edit/{id}', [LandController::class, 'recoveryedit'])->name('land.recoveryedit');
    Route::post('/save-recovery', [LandController::class, 'saveRecovery'])->name('save.recovery');
    Route::get('/get-land-by-series/{id}', [LandController::class, 'getLandBySeries'])->name('land.getLandBySeries');
    Route::get('/get-land-details/{id}', [LandController::class, 'getLandDetails'])->name('land.getLandDetails');
    Route::get('/get-lease-details/{id}', [LandController::class, 'getLeaseDetails'])->name('land.getLeaseDetails');

    Route::post('/land/approve-recovery', [LandController::class, 'approveRecovery']);
    Route::post('/land/reject-recovery', [LandController::class, 'rejectRecovery']);
    Route::get('/recovery/filter', [LandController::class, 'recoveryfilter'])->name('recovery.filter');
    Route::get('/lease/filter', [LandController::class, 'leasefilter'])->name('lease.filter');
    Route::get('/land/filter', [LandController::class, 'landfilter'])->name('land.filter');

    Route::prefix('land')->group(function () {
        // Land Dashboard
        Route::get('/dashboard', [LandController::class, 'dashboard'])->name('land.dashboard');
        Route::get('/dashboard/revenue-report', [LandController::class, 'getDashboardRevenueReport'])->name('land.getDashboardRevenueReport');
        // Land Report
        Route::get('/report', [LandReportController::class, 'index'])->name('land.report');
        Route::get('/report/filter', [LandReportController::class, 'getLandReport'])->name('land.getReportFilter');
        Route::get('/report-send/mail', [LandReportController::class, 'sendReportMail'])->name('land.send.report');
        Route::post('/add-scheduler', [LandReportController::class, 'addScheduler'])->name('land.add.scheduler');
        Route::get('/recovery-scheduler', [LandReportController::class, 'recoverySchedulerReport'])->name('land.recovery.scheduler');
        // End Land Report


    });

    Route::prefix('land-lease')->group(function () {
        //land parcel data
        Route::get('get-land-parcel-data/{land_id}', [LeaseController::class, 'getLandParcelData'])->name('lease.landparceldata');

        //lease
        Route::get('/', [LeaseController::class, 'index'])->name('lease.index');
        Route::get('/create', [LeaseController::class, 'create'])->name('lease.create');
        Route::post('/store', [LeaseController::class, 'store'])->name('lease.store');
        Route::get('/edit/{id}', [LeaseController::class, 'edit'])->name('lease.edit');
        Route::post('/update', [LeaseController::class, 'update'])->name('lease.update');
        Route::get('/show/{id}', [LeaseController::class, 'show'])->name('lease.show');
        Route::delete('/destroy/{id}', [LeaseController::class, 'destroy'])->name('lease.delete');
        Route::get('/add/filter-land/{page?}', [LeaseController::class, 'leaseFilterLand'])->name('land.onleaseadd.filter-land');
        Route::post('/tax-calculation', [LeaseController::class, 'taxCalculation'])->name('land.onleaseadd.tax');
        Route::post('/lease-appr-rej', [LeaseController::class, 'ApprReject'])->name('lease.appr_rej');
        Route::get('/amendment/{id}', [LeaseController::class, 'amendment'])->name('lease.amendment');
        Route::post('/action', [LeaseController::class, 'action'])->name('lease.action');
        Route::get('/show/{id}', [LeaseController::class, 'show'])->name('lease.show');
        Route::get('/report', [LeaseController::class, 'report'])->name('lease.report');

        // End lease

        // Extra Route

        Route::get('/get-exchange-rate/{currency_id}', [LeaseController::class, 'getExchangeRate'])->name('get.lease.exchange.rate');
        Route::post('/customer/address/store', [LeaseController::class, 'customerAddressStore'])->name('lease.customer.address.store');
        // End Extra Route

        Route::get('/land/on-lease', [LandController::class, 'onlease'])->name('land.onlease');
        Route::get('/land/on-lease/add', [LandController::class, 'onleaseadd'])->name('land.onleaseadd');
        Route::post('/save-lease', [LandController::class, 'savelease'])->name('save.lease');
        Route::get('/land/on-lease/edit/{id}', [LandController::class, 'onleaseedit'])->name('land.onleaseedit');
        Route::post('/update-lease', [LandController::class, 'updatelease'])->name('update.lease');
    });

    Route::get('/finance-ledger-report', [TrialBalanceController::class, 'getLedgerReport'])->name('getLedgerReport');
    Route::get('/getOrgLedgers/{id}', [TrialBalanceController::class, 'get_org_ledgers'])->name('get_org_ledgers');
    Route::post('/filterLedgerReport', [TrialBalanceController::class, 'filterLedgerReport'])->name('filterLedgerReport');
    Route::post('exportLedgerReport', [TrialBalanceController::class, 'exportLedgerReport'])->name('exportLedgerReport');
    Route::post('exportTrialBalanceReport', [TrialBalanceController::class, 'exportTrialBalanceReport'])->name('exportTrialBalanceReport');

    Route::get('/updateLedgerOpening', [TrialBalanceController::class, 'updateLedgerOpening'])->name('updateLedgerOpening'); ///// temp method to reset opening

    Route::get('/trial-balance-report/{id?}', [TrialBalanceController::class, 'index'])->name('trial_balance');
    Route::get('/trailLedger/{id}/{group}', [TrialBalanceController::class, 'trailLedger'])->name('trailLedger');
    Route::post('getInitialGroups', [TrialBalanceController::class, 'getInitialGroups'])->name('getInitialGroups');
    Route::post('getSubGroups', [TrialBalanceController::class, 'getSubGroups'])->name('getSubGroups');
    Route::post('getSubGroupsMultiple', [TrialBalanceController::class, 'getSubGroupsMultiple'])->name('getSubGroupsMultiple');

    // Loan Management
    Route::prefix('loan')->group(function () {
        Route::get('/my-application', [LoanManagementController::class, 'index'])->name('loan.index');
        Route::get('/home-loan/view/{id}', [LoanManagementController::class, 'viewAllDetail'])->name('loan.view_all_detail');
        Route::get('/home-loan', [LoanManagementController::class, 'home_loan'])->name('loan.home-loan');
        Route::get('/vehicle-loan', [LoanManagementController::class, 'vehicle_loan'])->name('loan.vehicle-loan');
        Route::get('/term-loan', [LoanManagementController::class, 'term_loan'])->name('loan.term-loan');
        Route::get('/loan-get-customer', [LoanManagementController::class, 'loanGetCustomer'])->name('loan.get.customer');

        //recovery
        Route::get('/recovery', [LoanRecoveryController::class, 'recovery'])->name('loan.recovery');
        Route::get('/recovery/view/{id}', [LoanRecoveryController::class, 'viewRecovery'])->name('loan.recovery_view');
        Route::get('/recovery/add', [LoanRecoveryController::class, 'addRecovery'])->name('loan.add-recovery');
        Route::post('/recovery-add-update', [LoanRecoveryController::class, 'recoveryAddUpdate'])->name('loan.recovery.add-update');
        Route::post('/recovery-appr-rej', [LoanRecoveryController::class, 'RecoveryApprReject'])->name('loan.recovery_appr_rej');
        Route::get('/fetch-recovery-approve', [LoanRecoveryController::class, 'fetchRecoveryApprove'])->name('loan.fetch-recovery-approve');
        Route::get('/loan-recovery-customer', [LoanRecoveryController::class, 'loanGetCustomer'])->name('loan.get.recovery.customer');
        Route::get('/get-recovery-interest', [LoanRecoveryController::class, 'getPrincipalInterest'])->name('loan.get.RecoveryInterest');
        Route::get('/loan-recovery-invoice/voucher/get', [LoanRecoveryController::class, 'getPostingDetails'])->name('loan.recovery.getPostingDetails');
        Route::post('/loan-recovery-invoice/voucher/post', [LoanRecoveryController::class, 'postPostingDetails'])->name('loan.recovery.post');

        // Route::get('/disbursement', [LoanManagementController::class, 'disbursement'])->name('loan.disbursement');
        Route::get('/settlement', [LoanManagementController::class, 'settlement'])->name('loan.settlement');


        // Loan Dashboard
        Route::get('/dashboard', [LoanDashboardController::class, 'dashboard'])->name('loan.dashboard');
        Route::get('/dashboard/loan-analytics', [LoanDashboardController::class, 'getDashboardLoanAnalytics'])->name('loan.analytics');
        Route::get('/dashboard/loan-kpi', [LoanDashboardController::class, 'getDashboardLoanKpi'])->name('loan.kpi');
        Route::get('/dashboard/loan-summary', [LoanDashboardController::class, 'getDashboardLoanSummary'])->name('loan.summary');


        // Loan Report
        Route::get('/report', [LoanReportController::class, 'index'])->name('loan.report');
        Route::get('/report/filter', [LoanReportController::class, 'getLoanFilter'])->name('loan.report.filter');
        Route::post('/add-scheduler', [LoanReportController::class, 'addScheduler'])->name('loan.add.scheduler');
        Route::get('/report-send/mail', [LoanReportController::class, 'sendReportMail'])->name('loan.send.report');

        Route::get('/disbursement-report', [LoanDisbursementReportController::class, 'index'])->name('loandisbursement.report');
        Route::get('/disbursementreport/filter', [LoanDisbursementReportController::class, 'getFilter'])->name('loandisbursement.report.filter');
        Route::post('/disbursement-add-scheduler', [LoanDisbursementReportController::class, 'addScheduler'])->name('loandisbursement.add.scheduler');
        Route::get('/disbursement-report-send/mail', [LoanDisbursementReportController::class, 'sendReportMail'])->name('loandisbursement.send.report');

        Route::get('/repayment-report', [LoanRepaymentReportController::class, 'index'])->name('loanrepayment.report');
        Route::get('/repaymentreport/filter', [LoanRepaymentReportController::class, 'getFilter'])->name('loanrepayment.report.filter');
        Route::post('/repayment-add-scheduler', [LoanRepaymentReportController::class, 'addScheduler'])->name('loanrepayment.add.scheduler');
        Route::get('/repayment-report-send/mail', [LoanRepaymentReportController::class, 'sendReportMail'])->name('loanrepayment.send.report');

        // Interest rate
        Route::get('/interest-rate', [LoanManagementController::class, 'interest_rate'])->name('loan.interest-rate');
        Route::get('/interest-add', [LoanInterestRateController::class, 'add'])->name('loan.interest-add');
        Route::post('/interest-create', [LoanInterestRateController::class, 'create'])->name('loan.interest-create');
        Route::get('/interest-edit/{id}', [LoanInterestRateController::class, 'edit'])->name('loan.interest-edit');
        Route::post('/interest-update/{id}', [LoanInterestRateController::class, 'update'])->name('loan.interest-update');
        Route::get('/interest-delete/{id}', [LoanInterestRateController::class, 'delete'])->name('loan.interest-delete');

        // Financial setup
        Route::get('/financial-setup', [LoanFinancialSetupController::class, 'index'])->name('loan.financial-setup');
        Route::get('/financial-setup-add', [LoanFinancialSetupController::class, 'add'])->name('loan.financial-setup-add');
        Route::post('/financial-setup-create', [LoanFinancialSetupController::class, 'create'])->name('loan.financial-setup-create');
        Route::get('/financial-setup-edit/{id}', [LoanFinancialSetupController::class, 'edit'])->name('loan.financial-setup-edit');
        Route::post('/financial-setup-update/{id}', [LoanFinancialSetupController::class, 'update'])->name('loan.financial-setup-update');
        Route::get('/financial-setup-delete/{id}', [LoanFinancialSetupController::class, 'delete'])->name('loan.financial-setup-delete');

        //Home Loan
        Route::get('/home-loan/add', [HomeLoanController::class, 'add'])->name('loan.home-loan-add');
        Route::post('/home-loan-create-update', [HomeLoanController::class, 'create'])->name('loan.home-loan-createUpdate');
        Route::get('/home-loan/edit/{id}', [HomeLoanController::class, 'edit'])->name('loan.home-loan-edit');
        Route::get('/home-loan/delete/{id}', [HomeLoanController::class, 'destroy'])->name('loan.home-loan-delete');

        // Vehicle Loan
        Route::post('/vehicle-loan-create-update', [VehicleLoanController::class, 'create'])->name('loan.vehicle.loan-createUpdate');
        Route::get('/vehicle-loan/view/{id}', [VehicleLoanController::class, 'viewVehicleDetail'])->name('loan.view_vehicle_detail');
        Route::get('/vehicle-loan/edit/{id}', [VehicleLoanController::class, 'editVehicleDetail'])->name('loan.edit_vehicle_detail');
        Route::get('/vehicle-loan/delete/{id}', [VehicleLoanController::class, 'destroy'])->name('loan.delete_vehicle_detail');

        // Application Delete
        Route::post('application/delete', [LoanManagementController::class, 'destroy'])->name('loan.delete');
        Route::delete('destroy/{id}', [LoanManagementController::class, 'destroy'])->name('loan.destroy');

        // Term Loan
        Route::post('/term-loan-create-update', [TermLoanController::class, 'create'])->name('loan.term-loan-createUpdate');
        Route::get('/term-loan/view/{id}', [TermLoanController::class, 'viewTermDetail'])->name('loan.view_term_detail');
        Route::get('/term-loan/edit/{id}', [TermLoanController::class, 'editTermDetail'])->name('loan.term-loan-edit');
        Route::get('/term-loan/delete/{id}', [TermLoanController::class, 'destroy'])->name('loan.term-loan-delete');

        Route::get('/get-cities', [LoanManagementController::class, 'getCities'])->name('loan.getCities');
        Route::get('/get-city-by-id', [LoanManagementController::class, 'getCityByID'])->name('loan.getCityByID');

        Route::get('/get-state', [LoanManagementController::class, 'getStates'])->name('loan.getStates');
        Route::get('/get-state-by-id', [LoanManagementController::class, 'getStateByID'])->name('loan.getStateByID');

        // Filter
        Route::post('/appr-rej', [LoanManagementController::class, 'ApprReject'])->name('loan.appr_rej');

        // Assessment
        Route::post('/loan-assess', [LoanManagementController::class, 'loanAssessment'])->name('loan.assess');
        Route::get('/get-assess', [LoanManagementController::class, 'getAssessment'])->name('get.loan.assess');

        // Disbursal schedule
        Route::post('/loan-disbursemnt', [LoanManagementController::class, 'loanDisbursemnt'])->name('loan.disbursemnt');
        Route::get('/get-disbursemnt', [LoanManagementController::class, 'getDisbursemnt'])->name('get.loan.disbursemnt');

        // Recovery Schedule
        Route::post('/loan-recovery-schedule', [LoanManagementController::class, 'loanRecoverySchedule'])->name('loan.recovery-schedule');
        Route::get('/get-recovery-schedule', [LoanManagementController::class, 'getRecoverySchedule'])->name('get.loan.recovery.schedule');

        // Documents
        Route::get('/get-doc', [LoanManagementController::class, 'getDoc'])->name('get.loan.docc');

        // Disbursement
        Route::get('/disbursement', [LoanDisbursementController::class, 'disbursement'])->name('loan.disbursement');
        Route::get('/disbursement/add', [LoanDisbursementController::class, 'addDisbursement'])->name('loan.add-disbursement');
        Route::get('/disbursement/view/{id}', [LoanDisbursementController::class, 'viewDisbursement'])->name('loan.view-disbursement');
        Route::get('/disbursement/assesment/view/{id}', [LoanDisbursementController::class, 'viewDisbursementAssesment'])->name('loan.view-disbursement-assesment');
        Route::get('/disbursement/assesment', [LoanDisbursementController::class, 'disbursement_assesment'])->name('loan.disbursement.assesment');
        Route::get('/disbursement/approval/view/{id}', [LoanDisbursementController::class, 'viewDisbursementApproval'])->name('loan.view-disbursement-approval');
        Route::get('/disbursement/approval', [LoanDisbursementController::class, 'disbursement_approval'])->name('loan.disbursement.approval');
        Route::get('/disbursement/submission/view/{id}', [LoanDisbursementController::class, 'viewDisbursementSubmssion'])->name('loan.view-disbursement-submission');
        Route::get('/disbursement/submission', [LoanDisbursementController::class, 'disbursement_submission'])->name('loan.disbursement.submission');
        Route::get('/disbursement-invoice/posting/get', [LoanDisbursementController::class, 'getPostingDetails'])->name('loan.disbursement.getPostingDetails');
        Route::post('/disbursement-invoice/post', [LoanDisbursementController::class, 'postInvoice'])->name('loan.disbursement.post');
        Route::post('/disbursement-payment', [LoanDisbursementController::class, 'disbursement_payment'])->name('disbursement.payment');
        Route::get('/get-bank', [LoanDisbursementController::class, 'get_bank_details'])->name('get.bank.details');
        Route::post('/disbursement-add-update', [LoanDisbursementController::class, 'disbursementAddUpdate'])->name('loan.disbursement.add-update');
        Route::get('/loan-get-disburs-customer', [LoanDisbursementController::class, 'loanGetDisbursCustomer'])->name('loan.get.disburs.customer');
        Route::post('/dis-appr-rej', [LoanDisbursementController::class, 'DisApprReject'])->name('loan.dis_appr_rej');
        Route::post('/proceed-disbursement-assesment', [LoanDisbursementController::class, 'proceedDisbursementAssesment'])->name('loan.proceed.disbursement');
        Route::post('/reject-disbursement-assesment', [LoanDisbursementController::class, 'rejectDisbursementAssesment'])->name('loan.reject.disbursement');
        Route::get('/loan-get-appraisal-customer', [LoanDisbursementController::class, 'loanGetCustomer'])->name('loan.get.customer.appraisal');
        //Route::get('/add-disbursement', [LoanManagementController::class, 'addDisbursement'])->name('loan.add-disbursement');
        //Route::post('/disbursement-add-update', [LoanManagementController::class, 'disbursementAddUpdate'])->name('loan.disbursement.add-update');
        // Route::get('/loan-get-disburs-customer', [LoanManagementController::class, 'loanGetDisbursCustomer'])->name('loan.get.disburs.customer');
        //Route::get('/loan-get-customer', [LoanManagementController::class, 'loanGetCustomer'])->name('loan.get.customer');
        //Route::post('/dis-appr-rej', [LoanManagementController::class, 'DisApprReject'])->name('loan.dis_appr_rej');


        // Settlement
        Route::get('/settlement', [LoanSettlementController::class, 'index'])->name('loan.settlement');
        Route::get('/settlement/add', [LoanSettlementController::class, 'add'])->name('loan.settlement.add');
        Route::get('/settlement/view/{id}', [LoanSettlementController::class, 'view'])->name('loan.settlement.view');
        Route::post('/settlement-add-update', [LoanSettlementController::class, 'save'])->name('loan.settlement.save');
        Route::post('/settle-appr-rej', [LoanSettlementController::class, 'ApprReject'])->name('loan.settlement.appr_rej');
        Route::get('/loan-settled-customer', [LoanSettlementController::class, 'loanGetCustomer'])->name('loan.settlement.customer');
        Route::get('/settlement-invoice/voucher/get', [LoanSettlementController::class, 'getPostingDetails'])->name('loan.settlement.getPostingDetails');
        Route::post('/settlement-invoice/voucher/post', [LoanSettlementController::class, 'postPostingDetails'])->name('loan.settlement.post');


        // Route::get('/add-settlement', [LoanManagementController::class, 'addSettlement'])->name('loan.add-settlement');
        // Route::post('/settlement-add-update', [LoanManagementController::class, 'settlementAddUpdate'])->name('loan.settlement.add-update');
        // Route::post('/settle-appr-rej', [LoanManagementController::class, 'SettleApprReject'])->name('loan.settle_appr_rej');

        // get pending disbursals
        Route::get('/get-pending-disbursal', [LoanManagementController::class, 'getPendingDisbursal'])->name('loan.get-pending-disbursal');
        Route::get('/set-pending-status', [LoanManagementController::class, 'setPendingStatus'])->name('loan.set_pending_status');

        //get series
        Route::get('/get-series', [LoanManagementController::class, 'getSeries'])->name('loan.get_series');

        Route::get('/fetch-disbursement-approve', [LoanManagementController::class, 'fetchDisbursementApprove'])->name('loan.fetch-disbursement-approve');
        Route::get('/fetch-settle-approve', [LoanManagementController::class, 'fetchSettleApprove'])->name('loan.fetch-settle-approve');


        Route::get('/get-loan-cibil', [LoanManagementController::class, 'getLoanCibil'])->name('get.loan.cibil');
        Route::get('/get-principal-interest', [LoanManagementController::class, 'getPrincipalInterest'])->name('loan.get.PrincipalInterest');

        Route::get('/get-loan-request/{book_id}', [LoanManagementController::class, 'getLoanRequests'])->name('get_loan_request');
    });

    # Bill of material
    Route::prefix('bill-of-material')
        ->name('bill.of.material.')
        ->controller(BomController::class)
        ->group(function () {
            Route::get('revoke-document', 'revokeDocument')->name('revoke.document');
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('store', 'store')->name('store');
            Route::get('change-item-code', 'changeItemCode')->name('item.code');
            Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
            Route::post('add-item-row', 'addItemRow')->name('item.row');
            Route::get('add-instruction-row', 'addInstructionRow')->name('instruction.row');
            Route::get('get-item-detail', 'getItemDetail')->name('get.itemdetail');
            Route::get('get-doc-no', 'getDocNumber')->name('doc.no');
            Route::get('edit/{id}', 'edit')->name('edit');
            Route::get('copy/{id}', 'copy')->name('copy');
            Route::post('update/{id}', 'update')->name('update');
            # get bom item cost child item
            Route::get('get-item-cost', 'getItemCost')->name('get.item.cost');
            Route::get('/{id}/pdf', 'generatePdf')->name('generate-pdf');

            Route::get('get-quote-bom', 'getQuoteBom')->name('get.quote.bom');
            Route::get('process-bom-item', 'processBomItem')->name('process.bom-item');
            # Overhead route
            Route::get('add-overhead-level', 'addOverheadLevel')->name('add.overhead.level');
            Route::get('add-overhead-row', 'addOverheadRow')->name('add.overhead.row');
            Route::get('add-overhead-item-row', 'addOverheadItemRow')->name('add.overhead.item.row');
            # Only for the production Bom
            Route::get('check-bom-exist', 'checkBomExist')->name('check.bom.exist');
            Route::get('report', 'bomReport')->name('report');
            Route::get('export/{id}', 'export')->name('export');
            Route::delete('/{id}/{isAmedment}', 'destroy')->name('destroy');
        });

    # Bom Import
    Route::prefix('bill-of-material')
        ->name('bill.of.material.')
        ->controller(BomImportController::class)
        ->group(function () {
            Route::get('import', 'import')->name('import');
            Route::post('import-save', 'importSave')->name('import.save');
            Route::get('import-error', 'importError')->name('import.error');
            Route::get('download-sample', 'downloadSample')->name('download.sample');
        });

    Route::prefix('quotation-bom')
        ->name('quotation.bom.')
        ->controller(BomImportController::class)
        ->group(function () {
            Route::get('import', 'import')->name('import');
            Route::post('import-save', 'importSave')->name('import.save');
            Route::get('import-error', 'importError')->name('import.error');
            Route::get('download-sample', 'downloadSample')->name('download.sample');
        });

    Route::get('/test-zip', function () {
        return class_exists('ZipArchive') ? '✅ ZipArchive is enabled' : '❌ ZipArchive NOT found';
    });
    Route::get('/test-zip', function () {
        return class_exists('ZipArchive') ? '✅ ZipArchive is enabled' : '❌ ZipArchive NOT found';
    });
    # All type documents approval
    Route::prefix('document-approval')
        ->name('document.approval.')
        ->controller(DocumentApprovalController::class)
        ->group(function () {
            Route::post('bom', 'bom')->name('bom');
            Route::post('saleOrder', 'saleOrder')->name('so');
            Route::post('po', 'po')->name('po');
            Route::post('jo', 'jo')->name('jo');
            Route::post('pi', 'pi')->name('pi');
            Route::post('saleInvoice', 'saleInvoice')->name('saleInvoice');
            Route::post('transportInvoice', 'transportInvoice')->name('transportInvoice');
            Route::post('saleReturn', 'saleReturn')->name('saleReturn');
            Route::post('transporter', 'transporter')->name('transporter');
            Route::post('materialIssue', 'materialIssue')->name('materialIssue');
            Route::post('materialReturn', 'materialReturn')->name('materialReturn');
            Route::post('production-slip', 'productionSlip')->name('productionSlip');
            Route::post('rate-contract', 'rateContract')->name('rateContract');
            Route::post('rfq', 'rfq')->name('rfq');
            Route::post('pqc', 'pqc')->name('pqc');
            Route::post('pq', 'pq')->name('pq');
            Route::post('pds', 'pds')->name('pds');
            Route::post('packing-list', 'packingList')->name('packingList');
            Route::post('pick-list', 'pickList')->name('pickList');
            Route::post('item', 'item')->name('item');
            Route::post('vendor', 'vendor')->name('vendor');
            Route::post('customer', 'customer')->name('customer');
            Route::post('rgr', 'rgr')->name('rgr');
            Route::post('lorryReceipt', 'lorryReceipt')->name('lorryReceipt');
        });

    // Material Receipt routes
    Route::prefix('material-receipts')
        ->name('material-receipt.')
        ->controller(MaterialReceiptController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/{id}/view', 'show')->name('show');
            Route::get('add-item-row', 'addItemRow')->name('item.row');
            Route::get('po-item-row', 'poItemRows')->name('po-item.row');
            Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
            Route::get('add-discount-row', 'addDiscountRow')->name('item.discount.row');
            Route::get('/tax-calculation', 'taxCalculation')->name('tax.calculation');
            Route::get('/get-address', 'getAddress')->name('get.address');
            Route::get('/edit-address', 'editAddress')->name('edit.address');
            Route::post('/address-save', 'addressSave')->name('address.save');
            Route::get('/get-itemdetail', 'getItemDetail')->name('get.itemdetail');
            Route::post('/get-store-racks', 'getStoreRacks')->name('get.store-racks');
            Route::post('/get-rack-shelfs', 'getStoreShelfs')->name('get.rack-shelfs');
            Route::post('/get-shelf-bins', 'getStoreBins')->name('get.shelf-bins');
            Route::get('/validate-quantity', 'validateQuantity')->name('get.validate-quantity');
            Route::get('/{id}/logs', 'logs')->name('logs');
            Route::get('/{id}/pdf', 'generatePdf')->name('generate-pdf');
            Route::delete('component-delete', 'componentDelete')->name('comp.delete');
            Route::get('/get-stock-detail', 'getStockDetail')->name('get.stock-detail');
            Route::get('amendment-submit/{id}', 'amendmentSubmit')->name('amendment.submit');
            Route::get('get-purchase-orders', 'getPo')->name('get.po');
            Route::get('get-job-orders', 'getJo')->name('get.jo');
            Route::get('get-sale-orders', 'getSo')->name('get.so');
            Route::get('process-po-item', 'processPoItem')->name('process.po-item');
            Route::get('process-jo-item', 'processJoItem')->name('process.jo-item');
            Route::get('process-so-item', 'processSoItem')->name('process.so-item');
            Route::get('/posting/get', 'getPostingDetails')->name('posting.get');
            Route::post('/post', 'postMrn')->name('post');
            Route::get('revoke-document', 'revokeDocument')->name('revoke.document');
            Route::post('/import-item', 'itemsImport')->name('items.import');
            Route::get('export-successful-items', 'exportSuccessfulItems');
            Route::get('export-failed-items', 'exportFailedItems');
            Route::get('process-import-item', 'processImportItem')->name('process.import-item');
            Route::get('/report', 'Report')->name('report');
            Route::get('/report/filter', 'getReportFilter')->name('report.filter');
            Route::post('/add-scheduler', 'addScheduler')->name('add.scheduler');
            Route::get('import-items-data/update', 'updateImportItem')->name('update.import-item');
            Route::get('/order/report', 'materialReceiptReport')->name('order.report');
            Route::get('/warehouse/check-setup', 'checkWarehouseSetup')->name('warehouse-setup');
            Route::get('/warehouse/item-uom-info', 'warehouseItemUomInfo')->name('warehouse-item-uom-info');
            Route::get('/{id}/print-labels', 'printLabels')->name('print-labels');
            Route::get('/{id}/print-barcodes', 'printBarcodes')->name('print-barcodes');
            Route::post('/get-selected-item-amount', 'getSelectedItemAmount')->name('get-selected-item-amount');
            Route::post('/validate-asn', 'processAsn')->name('validate-asn');

            /*Remove data*/
            Route::delete('remove-dis-item-level', 'removeDisItemLevel')->name('remove.item.dis');
            Route::delete('remove-dis-header-level', 'removeDisHeaderLevel')->name('remove.header.dis');
            Route::delete('remove-exp-header-level', 'removeExpHeaderLevel')->name('remove.header.exp');
        });


    Route::prefix('gate-entries')
        ->name('gate-entry.')
        ->controller(GateEntryController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/{id}/view', 'show')->name('show');
            Route::get('add-item-row', 'addItemRow')->name('item.row');
            Route::get('po-item-row', 'poItemRows')->name('po-item.row');
            Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
            Route::get('add-discount-row', 'addDiscountRow')->name('item.discount.row');
            Route::get('/tax-calculation', 'taxCalculation')->name('tax.calculation');
            Route::get('/get-address', 'getAddress')->name('get.address');
            Route::get('/edit-address', 'editAddress')->name('edit.address');
            Route::post('/address-save', 'addressSave')->name('address.save');
            Route::get('/get-itemdetail', 'getItemDetail')->name('get.itemdetail');
            Route::post('/get-store-racks', 'getStoreRacks')->name('get.store-racks');
            Route::post('/get-rack-shelfs', 'getStoreShelfs')->name('get.rack-shelfs');
            Route::post('/get-shelf-bins', 'getStoreBins')->name('get.shelf-bins');
            Route::get('/validate-quantity', 'validateQuantity')->name('get.validate-quantity');
            Route::get('/{id}/logs', 'logs')->name('logs');
            Route::get('/{id}/pdf', 'generatePdf')->name('generate-pdf');
            Route::delete('component-delete', 'componentDelete')->name('comp.delete');
            Route::get('/get-stock-detail', 'getStockDetail')->name('get.stock-detail');
            Route::get('amendment-submit/{id}', 'amendmentSubmit')->name('amendment.submit');
            Route::get('get-purchase-orders', 'getPo')->name('get.po');
            Route::get('get-job-orders', 'getJo')->name('get.jo');
            Route::get('process-po-item', 'processPoItem')->name('process.po-item');
            Route::get('process-jo-item', 'processJoItem')->name('process.jo-item');
            Route::get('/posting/get', 'getPostingDetails')->name('posting.get');
            Route::post('/post', 'postMrn')->name('post');
            Route::get('revoke-document', 'revokeDocument')->name('revoke.document');
            Route::get('/report', 'Report')->name('report');
            Route::get('/report/filter', 'getReportFilter')->name('report.filter');
            Route::post('/add-scheduler', 'addScheduler')->name('add.scheduler');
            Route::get('/order/report', 'gateEntryReport')->name('order.report');
            Route::post('/get-selected-item-amount', 'getSelectedItemAmount')->name('get-selected-item-amount');
            Route::post('/validate-asn', 'processAsn')->name('validate-asn');

            /*Remove data*/
            Route::delete('remove-dis-item-level', 'removeDisItemLevel')->name('remove.item.dis');
            Route::delete('remove-dis-header-level', 'removeDisHeaderLevel')->name('remove.header.dis');
            Route::delete('remove-exp-header-level', 'removeExpHeaderLevel')->name('remove.header.exp');
        });

    # All type documents approval
    Route::prefix('document-approval')
        ->name('document.approval.')
        ->controller(DocumentApprovalController::class)
        ->group(function () {
            Route::post('material-receipt', 'mrn')->name('material-receipt');
        });

    Route::prefix('document-approval')
        ->name('document.approval.')
        ->controller(DocumentApprovalController::class)
        ->group(function () {
            Route::post('gate-entry', 'gateEntry')->name('gate-entry');
        });

    # WHM Barcode Routes
    Route::name('barcodes.')
        ->controller(PrintBarcodeController::class)
        ->group(function () {
            Route::get('barcodes/{id}', 'page')->name('page');
            Route::get('barcodes/{id}/data', 'data')->name('data');
            Route::get('barcodes/print/{module_type}/{id}', 'print')->name('print');
        });

    // Inventory Report routes
    Route::prefix('inventory-reports')
        ->name('inventory-report.')
        ->controller(InventoryReportController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/get-attribute-values', 'getAttributeValues')->name('get.attribute-values');
            Route::get('/report/filter', 'getReportFilter')->name('report.filter');
            Route::get('get-item-attributes', 'getItemAttributes')->name('item.attr');
            Route::get('/get-stock-ledger-reports', 'detailedReports');
            Route::get('/get-stock-ledger-filter', 'detailedReportFilter')->name('detail.filter');
            Route::get('/get-stock-ledger-summary-reports', 'summaryReport');
            Route::get('/get-stock-ledger-summary-filter', 'summaryReportFilter')->name('summary.filter');
            Route::post('add-scheduler', 'addScheduler')->name('add.scheduler');
            Route::get('/single-item', 'getSingleItemData')->name('single.item');
        });

    // Expense routes
    Route::prefix('expense-advice')
        ->name('expense-adv.')
        ->controller(ExpenseAdviseController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/{id}/view', 'show')->name('show');
            Route::get('add-item-row', 'addItemRow')->name('item.row');
            Route::get('po-item-row', 'poItemRows')->name('po-item.row');
            Route::get('so-item-row', 'soItemRows')->name('so-item.row');

            Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
            Route::get('add-discount-row', 'addDiscountRow')->name('item.discount.row');
            Route::get('/tax-calculation', 'taxCalculation')->name('tax.calculation');
            Route::get('/get-address', 'getAddress')->name('get.address');
            Route::get('/edit-address', 'editAddress')->name('edit.address');
            Route::post('/address-save', 'addressSave')->name('address.save');
            Route::get('/get-itemdetail', 'getItemDetail')->name('get.itemdetail');
            Route::post('/get-items-by-vendor', 'getPoItemsByVendorId');
            Route::post('/get-po-items-by-po-id', 'getPoItemsByPoId')->name('get.po-items-by-po-id');
            Route::post('/get-items-by-customer', 'getSoItemsByCustomerId');
            Route::post('/get-so-items-by-so-id', 'getSoItemsBySoId')->name('get.so-items-by-so-id');
            Route::get('/{id}/logs', 'logs')->name('logs');
            Route::get('/{id}/pdf', 'generatePdf')->name('generate-pdf');
            Route::delete('component-delete', 'componentDelete')->name('comp.delete');
            Route::get('/amendment-submit/{id}', 'amendmentSubmit')->name('amendment.submit');
            Route::get('/get-purchase-orders', 'getPo')->name('get.po');
            Route::get('/process-po-item', 'processPoItem')->name('process.po-item');
            Route::get('/get-job-orders', 'getJo')->name('get.jo');
            Route::get('/process-jo-item', 'processJoItem')->name('process.jo-item');
            Route::get('/get-sales-orders', 'getSo')->name('get.so');
            Route::get('/process-so-item', 'processSoItem')->name('process.so-item');
            Route::get('/posting/get', 'getPostingDetails')->name('posting.get');
            Route::post('/post', 'postExpenseAdvise')->name('post');
            Route::get('revoke-document', 'revokeDocument')->name('revoke.document');
            Route::get('/report', 'Report')->name('report');
            Route::get('/report/filter', 'getReportFilter')->name('report.filter');
            Route::post('/add-scheduler', 'addScheduler')->name('add.scheduler');
            Route::get('/order/report', 'expenseAdviseReport')->name('order.report');
            Route::get('/validate-quantity', 'validateQuantity')->name('get.validate-quantity');

            /*Remove data*/
            Route::delete('remove-dis-item-level', 'removeDisItemLevel')->name('remove.item.dis');
            Route::delete('remove-dis-header-level', 'removeDisHeaderLevel')->name('remove.header.dis');
            Route::delete('remove-exp-header-level', 'removeExpHeaderLevel')->name('remove.header.exp');
        });

    # All type documents approval
    Route::prefix('document-approval')
        ->name('document.approval.')
        ->controller(DocumentApprovalController::class)
        ->group(function () {
            Route::post('expense-adv', 'expense')->name('expense-adv');
        });

    # All type documents Amendements
    Route::prefix('amendement')
        ->name('document.amendement.')
        ->controller(AmendementController::class)
        ->group(function () {
            Route::post('expense-adv', 'expense')->name('expense');
        });

    // Purchase Bill Routes
    Route::prefix('purchase-bills')
        ->name('purchase-bill.')
        ->controller(PurchaseBillController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/{id}/view', 'show')->name('show');
            Route::get('add-item-row', 'addItemRow')->name('item.row');
            Route::get('mrn-item-row', 'mrnItemRows')->name('mrn-item.row');
            Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
            Route::get('add-discount-row', 'addDiscountRow')->name('item.discount.row');
            Route::get('/tax-calculation', 'taxCalculation')->name('tax.calculation');
            Route::get('/get-address', 'getAddress')->name('get.address');
            Route::get('/edit-address', 'editAddress')->name('edit.address');
            Route::post('/address-save', 'addressSave')->name('address.save');
            Route::get('/get-itemdetail', 'getItemDetail')->name('get.itemdetail');
            Route::post('/get-items-by-vendor', 'getMrnItemsByVendorId');
            Route::post('/get-mrn-items-by-mrn-id', 'getMrnItemsByMrnId')->name('get.mrn-items-by-mrn-id');
            Route::get('/{id}/logs', 'logs')->name('logs');
            Route::get('/{id}/pdf', 'generatePdf')->name('generate-pdf');
            Route::delete('component-delete', 'componentDelete')->name('comp.delete');
            Route::get('amendment-submit/{id}', 'amendmentSubmit')->name('amendment.submit');
            Route::get('get-mrn', 'getMrn')->name('get.mrn');
            Route::get('process-mrn-item', 'processMrnItem')->name('process.mrn-item');
            Route::get('/posting/get', 'getPostingDetails')->name('posting.get');
            Route::post('/post', 'postPb')->name('post');
            Route::get('revoke-document', 'revokeDocument')->name('revoke.document');
            Route::get('/report', 'Report')->name('report');
            Route::get('/report/filter', 'getReportFilter')->name('report.filter');
            Route::post('/add-scheduler', 'addScheduler')->name('add.scheduler');
            Route::get('/order/report', 'purchaseBillReport')->name('order.report');
            Route::get('/validate-quantity', 'validateQuantity')->name('get.validate-quantity');

            /*Remove data*/
            Route::delete('remove-dis-item-level', 'removeDisItemLevel')->name('remove.item.dis');
            Route::delete('remove-dis-header-level', 'removeDisHeaderLevel')->name('remove.header.dis');
            Route::delete('remove-exp-header-level', 'removeExpHeaderLevel')->name('remove.header.exp');
        });

    # All type documents approval
    Route::prefix('document-approval')
        ->name('document.approval.')
        ->controller(DocumentApprovalController::class)
        ->group(function () {
            Route::post('purchase-bill', 'purchaseBill')->name('purchase-bill');
        });

    # All type documents Amendements
    Route::prefix('amendement')
        ->name('document.amendement.')
        ->controller(AmendementController::class)
        ->group(function () {
            Route::post('purchase-bill', 'purchaseBill')->name('purchase-bill');
        });

    // Purchase Return routes
    Route::prefix('purchase-return')
        ->name('purchase-return.')
        ->controller(PurchaseReturnController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/{id}/view', 'show')->name('show');
            Route::get('add-item-row', 'addItemRow')->name('item.row');
            Route::get('mrn-item-row', 'mrnItemRows')->name('mrn-item.row');
            Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
            Route::get('add-discount-row', 'addDiscountRow')->name('item.discount.row');
            Route::get('/tax-calculation', 'taxCalculation')->name('tax.calculation');
            Route::get('/get-address', 'getAddress')->name('get.address');
            Route::get('/edit-address', 'editAddress')->name('edit.address');
            Route::post('/address-save', 'addressSave')->name('address.save');
            Route::get('/get-itemdetail', 'getItemDetail')->name('get.itemdetail');
            Route::get('/validate-quantity', 'validateQuantity')->name('get.validate-quantity');
            Route::get('/{id}/logs', 'logs')->name('logs');
            Route::get('/{id}/pdf', 'generatePdf')->name('generate-pdf');
            Route::delete('component-delete', 'componentDelete')->name('comp.delete');
            Route::get('amendment-submit/{id}', 'amendmentSubmit')->name('amendment.submit');
            Route::get('get-mrn', 'getMrn')->name('get.mrn');
            Route::get('process-mrn-item', 'processMrnItem')->name('process.mrn-item');
            Route::get('/posting/get', 'getPostingDetails')->name('posting.get');
            Route::post('/post', 'postPR')->name('post');
            Route::get('revoke-document', 'revokeDocument')->name('revoke.document');
            Route::get('/generate-einvoice', 'generateEInvoice')->name('generate-einvoice');
            Route::post('/cancel-einvoice', 'cancelEInvoice')->name('cancel-einvoice');
            Route::get('/generate-ewaybill', 'generateEwayBill')->name('generate-ewaybill');
            Route::get('/report', 'Report')->name('report');
            Route::get('/report/filter', 'getReportFilter')->name('report.filter');
            Route::post('/add-scheduler', 'addScheduler')->name('add.scheduler');
            Route::get('/order/report', 'purchaseReturnReport')->name('order.report');

            /*Remove data*/
            Route::delete('remove-dis-item-level', 'removeDisItemLevel')->name('remove.item.dis');
            Route::delete('remove-dis-header-level', 'removeDisHeaderLevel')->name('remove.header.dis');
            Route::delete('remove-exp-header-level', 'removeExpHeaderLevel')->name('remove.header.exp');

            Route::post('send-mail', 'prMail')->name('prMail');
        });

    Route::prefix('document-approval')
        ->name('document.approval.')
        ->controller(DocumentApprovalController::class)
        ->group(function () {
            Route::post('purchase-return', 'purchaseReturn')->name('purchase-return');
        });

    // Inspection routes
    Route::prefix('inspection')
        ->name('inspection.')
        ->controller(InspectionController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/{id}/view', 'show')->name('show');
            Route::get('add-item-row', 'addItemRow')->name('item.row');
            Route::get('mrn-item-row', 'mrnItemRows')->name('mrn-item.row');
            Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
            Route::get('add-discount-row', 'addDiscountRow')->name('item.discount.row');
            Route::get('/tax-calculation', 'taxCalculation')->name('tax.calculation');
            Route::get('/get-address', 'getAddress')->name('get.address');
            Route::get('/edit-address', 'editAddress')->name('edit.address');
            Route::post('/address-save', 'addressSave')->name('address.save');
            Route::get('/get-itemdetail', 'getItemDetail')->name('get.itemdetail');
            Route::get('/validate-quantity', 'validateQuantity')->name('get.validate-quantity');
            Route::get('/{id}/logs', 'logs')->name('logs');
            Route::get('/{id}/pdf', 'generatePdf')->name('generate-pdf');
            Route::delete('component-delete', 'componentDelete')->name('comp.delete');
            Route::get('amendment-submit/{id}', 'amendmentSubmit')->name('amendment.submit');
            Route::get('get-mrn', 'getMrn')->name('get.mrn');
            Route::get('process-mrn-item', 'processMrnItem')->name('process.mrn-item');
            Route::get('revoke-document', 'revokeDocument')->name('revoke.document');
            Route::get('/report', 'Report')->name('report');
            Route::get('/report/filter', 'getReportFilter')->name('report.filter');
            Route::post('/add-scheduler', 'addScheduler')->name('add.scheduler');
            Route::get('/order/report', 'purchaseReturnReport')->name('order.report');

            /*Remove data*/
            Route::delete('remove-dis-item-level', 'removeDisItemLevel')->name('remove.item.dis');
            Route::delete('remove-dis-header-level', 'removeDisHeaderLevel')->name('remove.header.dis');
            Route::delete('remove-exp-header-level', 'removeExpHeaderLevel')->name('remove.header.exp');

            Route::post('send-mail', 'prMail')->name('prMail');
        });

    Route::prefix('document-approval')
        ->name('document.approval.')
        ->controller(DocumentApprovalController::class)
        ->group(function () {
            Route::post('inspection', 'inspection')->name('inspection');
        });

    // Inspection routes
    Route::prefix('put-away')
        ->name('put-away.')
        ->controller(PutAwayController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/{id}/view', 'show')->name('show');
            Route::get('add-item-row', 'addItemRow')->name('item.row');
            Route::get('mrn-item-row', 'mrnItemRows')->name('mrn-item.row');
            Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
            Route::get('add-discount-row', 'addDiscountRow')->name('item.discount.row');
            Route::get('/tax-calculation', 'taxCalculation')->name('tax.calculation');
            Route::get('/get-address', 'getAddress')->name('get.address');
            Route::get('/edit-address', 'editAddress')->name('edit.address');
            Route::post('/address-save', 'addressSave')->name('address.save');
            Route::get('/get-itemdetail', 'getItemDetail')->name('get.itemdetail');
            Route::get('/validate-quantity', 'validateQuantity')->name('get.validate-quantity');
            Route::get('/{id}/logs', 'logs')->name('logs');
            Route::get('/{id}/pdf', 'generatePdf')->name('generate-pdf');
            Route::delete('component-delete', 'componentDelete')->name('comp.delete');
            Route::get('amendment-submit/{id}', 'amendmentSubmit')->name('amendment.submit');
            Route::get('get-mrn', 'getMrn')->name('get.mrn');
            Route::get('process-mrn-item', 'processMrnItem')->name('process.mrn-item');
            Route::get('/posting/get', 'getPostingDetails')->name('posting.get');
            Route::post('/post', 'postPR')->name('post');
            Route::get('revoke-document', 'revokeDocument')->name('revoke.document');
            Route::get('/report', 'Report')->name('report');
            Route::get('/report/filter', 'getReportFilter')->name('report.filter');
            Route::post('/add-scheduler', 'addScheduler')->name('add.scheduler');
            Route::get('/order/report', 'purchaseReturnReport')->name('order.report');
            Route::get('/warehouse/check-setup', 'checkWarehouseSetup')->name('warehouse-setup');
            Route::get('/warehouse/item-uom-info', 'warehouseItemUomInfo')->name('warehouse-item-uom-info');
            Route::get('/{id}/print-labels', 'printLabels')->name('print-labels');
            Route::get('/{id}/print-barcodes', 'printBarcodes')->name('print-barcodes');
            // Route::post('location-listing', 'locationListing')->name('get.locations');
            // Route::post('sub-location-listing', 'subLocationListing')->name('get.sub-locations');
            // Route::post('mrn-listing', 'mrnListing')->name('get.mrn-listing');

            /*Remove data*/
            Route::delete('remove-dis-item-level', 'removeDisItemLevel')->name('remove.item.dis');
            Route::delete('remove-dis-header-level', 'removeDisHeaderLevel')->name('remove.header.dis');
            Route::delete('remove-exp-header-level', 'removeExpHeaderLevel')->name('remove.header.exp');

            Route::post('send-mail', 'prMail')->name('prMail');
        });

    Route::prefix('document-approval')
        ->name('document.approval.')
        ->controller(DocumentApprovalController::class)
        ->group(function () {
            Route::post('put-away', 'putAway')->name('put-away');
        });

    // Material Request routes
    Route::prefix('material-request')
        ->name('material-request.')
        ->controller(PiController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::get('/edit/{id}', 'edit')->name('edit');
        });

    // Stock Adjustment routes
    Route::prefix('stock-adjustment')
        ->name('stock-adjustment.')
        ->controller(MaterialReceiptController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::get('/edit/{id}', 'edit')->name('edit');
        });

    // Physical Stock Take routes
    Route::prefix('physical-stock-take')
        ->name('physical-stock-take.')
        ->controller(MaterialReceiptController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::get('/edit/{id}', 'edit')->name('edit');
        });

    // Production Slip routes
    // Route::prefix('production-slip')
    //     ->name('production-slip.')
    //     ->controller(MaterialReceiptController::class)
    //     ->group(function () {
    //         Route::get('/', 'index')->name('index');
    //         Route::get('/create', 'create')->name('create');
    //         Route::get('/edit/{id}', 'edit')->name('edit');
    //     });

    // Commercial BOM routes
    Route::prefix('quotation-bom')
        ->name('quotation-bom.')
        ->controller(BomController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::get('copy/{id}', 'copy')->name('copy');

            Route::get('export/{id}', 'export')->name('export');
        });

    // Production Work Order routes
    Route::prefix('production-work-order')
        ->name('production-work-order.')
        ->controller(PoController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::get('/edit/{id}', 'edit')->name('edit');
        });

    Route::prefix('taxes')->controller(TaxController::class)->group(function () {
        Route::get('/test-tax-calculation', 'testCalculateTax')->name('tax.test.calculate');
        Route::get('/tax-calculation', 'calculateItemTax')->name('tax.calculate');
        Route::get('/tax-group-calculation', 'calculateTaxGroups')->name('tax.group.calculate');
        Route::get('/tax-calculation', 'calculateItemTax');
        Route::get('/', 'index')->name('tax.index');
        Route::get('/create', 'create')->name('tax.create');
        Route::post('/', 'store')->name('tax.store');
        Route::get('/search/ledger', 'getLedger')->name('tax.ledger.search');
        Route::get('/getTaxPercentage', 'getTaxPercentage');
        Route::get('/{id}', 'show')->name('tax.show');
        Route::get('/{id}/edit', 'edit')->name('tax.edit');
        Route::put('/{id}', 'update')->name('tax.update');
        Route::delete('/{id}', 'destroy')->name('tax.destroy');
        Route::get('/calculate-tax/sales/{alias}', 'calculateTaxForSalesModule')->name('tax.calculate.sales');
        Route::delete('/tax-detail/{id}', 'deleteTaxDetail')->name('tax-detail.delete');
    });

    // Production Route Routes
    Route::prefix('production-routes')
        ->name('production-route.')
        ->controller(ProductionRouteController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/delete/{id}', 'delete')->name('delete');
            Route::get('/station', 'getStationData')->name('get-station');
            Route::get('/get-items', 'getItemAttribute')->name('get-items');
            Route::get('/get-items-edit', 'getItemAttributeEdit')->name('get-items-edit');
        });

    // Warehouse Structure Routes
    Route::prefix('warehouse-structures')
        ->name('warehouse-structure.')
        ->controller(WarehouseStructureController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/delete/{id}', 'delete')->name('delete');
            Route::post('/delete-level', 'deleteLevel')->name('delete-level');
        });

    // Warehouse Mapping Routes
    Route::prefix('warehouse-mappings')
        ->name('warehouse-mapping.')
        ->controller(WarehouseMappingController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/delete/{id}', 'delete')->name('delete');
            Route::get('/sub-stores', 'getSubStores')->name('get.sub-stores');
            Route::get('/levels', 'getLevels')->name('get.levels');
            Route::get('/level-parents', 'getLevelParents')->name('get.level-parents');
            Route::get('/get-parents', 'getParents')->name('get.parents');
            Route::get('/{id}/print-labels', 'getBarcodes')->name('print-labels');
            Route::get('/{id}/print-barcodes', 'printBarcodes')->name('print-barcodes');
            Route::post('/delete-details', 'deleteDetails')->name('delete-details');
        });

    // Warehouse Multiple Mapping Routes
    Route::prefix('warehouse-multiple-mappings')
        ->name('warehouse-multiple-mapping.')
        ->controller(WarehouseMultiMappingController::class)
        ->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/levels', 'getLevels')->name('get.levels');
            Route::get('/level-parents', 'getLevelParents')->name('get.level-parents');
            Route::get('/get-parents', 'getParents')->name('get.parents');
        });

    // Warehouse Item Mapping Routes
    Route::prefix('warehouse-item-mappings')
        ->name('warehouse-item-mapping.')
        ->controller(WarehouseItemMappingController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/delete/{id}', 'delete')->name('delete');
            Route::get('/sub-stores', 'getSubStores')->name('get.sub-stores');
            Route::get('/details', 'getDetails')->name('get.details');
            Route::get('/existing-details', 'getMappingData')->name('get.existing-details');
            Route::get('/get-sub-categories', 'getSubCategories')->name('get.sub-categories');
            Route::get('/get-items', 'getItems')->name('get.items');
            Route::get('/get-structure-details', 'getStructureDetails')->name('get.structure-details');
            Route::get('/get-childs', 'getChilds')->name('get.childs');
            Route::post('/delete-details', 'deleteDetails')->name('delete-details');
        });

    Route::prefix('product-sections')->controller(ProductSectionController::class)->group(function () {
        Route::get('/', 'index')->name('product-sections.index');
        Route::get('/create', 'create')->name('product-sections.create');
        Route::post('/', 'store')->name('product-sections.store');
        Route::get('/{id}/edit', 'edit')->name('product-sections.edit');
        Route::put('/{id}', 'update')->name('product-sections.update');
        Route::delete('/{id}', 'destroy')->name('product-sections.destroy');
        Route::delete('/section-detail/{id}', 'deleteSectionDetail')->name('section-detail.delete');
        Route::get('/subproduct-sections/{productSectionId}', 'getSubProductSection')->name('product-sections.subproduct-sections');
    });

    Route::prefix('product-specifications')->controller(ProductSpecificationController::class)->group(function () {
        Route::get('/', 'index')->name('product-specifications.index');
        Route::post('/', 'store')->name('product-specifications.store');
        Route::get('/create', 'create')->name('product-specifications.create');
        Route::get('/specifications/{id}', 'getSpecificationDetails');
        Route::get('/{id}/edit', 'edit')->name('product-specifications.edit');
        Route::get('/{id}', 'show')->name('product-specifications.show');
        Route::put('/{id}', 'update')->name('product-specifications.update');
        Route::delete('/{id}', 'destroy')->name('product-specifications.destroy');
        Route::delete('/specification-detail/{id}', 'deleteSpecificationDetail')->name('specification-detail.delete');
    });

    Route::prefix('dynamic-fields')->controller(DynamicFieldController::class)->group(function () {
        Route::get('/', 'index')->name('dynamic-fields.index');
        Route::post('/', 'store')->name('dynamic-fields.store');
        Route::get('/create', 'create')->name('dynamic-fields.create');
        Route::get('/field-details/{id}', 'getFieldDetails');
        Route::get('/{id}/edit', 'edit')->name('dynamic-fields.edit');
        Route::put('/{id}', 'update')->name('dynamic-fields.update');
        Route::delete('/detail-value/{id}', 'dynamicValueDestroy')->name('dynamic-fields-value.destroy');
        Route::delete('/{id}', 'destroy')->name('dynamic-fields.destroy');
        Route::delete('/field-detail/{id}', 'deleteFieldDetail')->name('field-detail.delete');
        Route::get('/detail', 'getDynamicFieldDetails')->name('dynamic-fields.detail');
    });

    Route::prefix('inspection-checklists')->controller(InspectionChecklistController::class)->group(function () {
        Route::get('/', 'index')->name('inspection-checklists.index');
        Route::post('/', 'store')->name('inspection-checklists.store');
        Route::get('/create', 'create')->name('inspection-checklists.create');
        // Import / Export
        Route::get('/import', 'showImportForm')->name('inspection-checklists.show.import');
        Route::post('/import', 'import')->name('inspection-checklists.import');
        Route::get('/export-successful', 'exportSuccessful')->name('inspection-checklists.export.successful');
        Route::get('/export-failed', 'exportFailed')->name('inspection-checklists.export.failed');

        Route::get('/checklist-details/{id}', 'getChecklistDetails');
        Route::get('/{id}/edit', 'edit')->name('inspection-checklists.edit');
        Route::put('/{id}', 'update')->name('inspection-checklists.update');
        Route::delete('/detail-value/{id}', 'deleteChecklistValue')->name('inspection-checklists-value.destroy');
        Route::delete('/checklist-detail/{id}', 'deleteChecklistDetail')->name('checklist-detail.delete');
        Route::delete('/{id}', 'destroy')->name('inspection-checklists.destroy');
        Route::get('/detail', 'getChecklistDetails')->name('inspection-checklists.detail');
    });

    Route::prefix('maintenance-inspection-checklists')->controller(InspectionChecklistController::class)->group(function () {
        Route::get('/', 'index')->name('maintenance-inspection-checklists.index');
        Route::get('/create', 'create')->name('maintenance-inspection-checklists.create');
        Route::get('/{id}/edit', 'edit')->name('maintenance-inspection-checklists.edit');
    });

    Route::prefix('stations')->controller(StationController::class)->group(function () {
        Route::get('/', 'index')->name('stations.index');
        Route::get('/create', 'create')->name('stations.create');
        Route::post('/', 'store')->name('stations.store');
        Route::get('/{id}', 'show')->name('stations.show');
        Route::get('/{id}/edit', 'edit')->name('stations.edit');
        Route::put('/{id}', 'update')->name('stations.update');
        Route::delete('/{id}', 'destroy')->name('stations.destroy');
        Route::delete('/substation/{id}', 'deleteSubstation')->name('substation.delete');
        Route::get('/stocking/get/by-sub-store', 'getStockingStationsOfSubStore')->name('stations.stocking.get.subStore');
    });

    Route::prefix('station-groups')->controller(StationGroupController::class)->group(function () {
        Route::get('/', 'index')->name('station-groups.index');
        Route::get('/create', 'create')->name('station-groups.create');
        Route::post('/', 'store')->name('station-groups.store');
        Route::get('/{id}', 'show')->name('station-groups.show');
        Route::get('/{id}/edit', 'edit')->name('station-groups.edit');
        Route::put('/{id}', 'update')->name('station-groups.update');
        Route::delete('/{id}', 'destroy')->name('station-groups.destroy');
    });

    Route::prefix('terms-conditions')->controller(TermsAndConditionController::class)->group(function () {
        Route::get('/', 'index')->name('terms.index');
        Route::get('/create', 'create')->name('terms.create');
        Route::post('/', 'store')->name('terms.store');
        Route::get('/{id}', 'show')->name('terms.show');
        Route::get('/{id}/edit', 'edit')->name('terms.edit');
        Route::put('/{id}', 'update')->name('terms.update');
        Route::delete('/{id}', 'destroy')->name('terms.destroy');
    });

    Route::prefix('exchange-rates')->controller(ExchangeRateController::class)->group(function () {
        Route::get('/', 'index')->name('exchange-rates.index');
        Route::get('/create', 'create')->name('exchange-rates.create');
        Route::post('/get-currency-exchange-rate', 'getExchangeRate')->name('get.currency.exchange.rate');
        Route::post('/', 'store')->name('exchange-rates.store');
        Route::get('/{id}/edit', 'edit')->name('exchange-rates.edit');
        Route::put('/{id}', 'update')->name('exchange-rates.update');
        Route::delete('/{id}', 'destroy')->name('exchange-rates.destroy');
    });

    Route::prefix('discount-masters')->controller(DiscountMasterController::class)->group(function () {
        Route::get('/', 'index')->name('discount-masters.index');
        Route::post('/', 'store')->name('discount-masters.store');
        Route::put('/{id}', 'update')->name('discount-masters.update');
        Route::delete('/{id}', 'destroy')->name('discount-masters.destroy');
    });

    Route::prefix('overhead-masters')->controller(OverheadMasterController::class)->group(function () {
        Route::get('/', 'index')->name('overhead-masters.index');
        Route::post('/', 'store')->name('overhead-masters.store');
        Route::put('/{id}', 'update')->name('overhead-masters.update');
        Route::delete('/{id}', 'destroy')->name('overhead-masters.destroy');
    });

    Route::prefix('expense-masters')->controller(ExpenseMasterController::class)->group(function () {
        Route::get('/', 'index')->name('expense-masters.index');
        Route::post('/', 'store')->name('expense-masters.store');
        Route::put('/{id}', 'update')->name('expense-masters.update');
        Route::delete('/{id}', 'destroy')->name('expense-masters.destroy');
    });

    Route::prefix('maintenance-types')->controller(ErpMaintenanceTypeController::class)->group(function () {
        Route::get('/', 'index')->name('maintenance-types.index');
        Route::post('/', 'store')->name('maintenance-types.store');
        Route::delete('/', 'delete')->name('maintenance-types.delete');
    });

    Route::prefix('defect-types')->controller(ErpDefectTypeController::class)->group(function () {
        Route::get('/', 'index')->name('defect-types.index');
        Route::post('/', 'store')->name('defect-types.store');
        Route::delete('/', 'delete')->name('defect-types.delete');
    });

    Route::get('/search', [AutocompleteController::class, 'search'])->name('search');

    Route::get('/countries', [CountryController::class, 'countries'])->name('countries.get');
    Route::get('/states/{countryId}', [CountryController::class, 'states'])->name('states.get');
    Route::get('/cities/{stateId}', [CountryController::class, 'cities'])->name('cities.get');
    Route::get('/pincodes/{stateId}', [CountryController::class, 'pincodes'])->name('pincodes.get');
    Route::get('/get-state-id-by-code/{stateCode}', [CountryController::class, 'getStateIdByCode']);
    Route::get('/get-country-id-by-state/{stateId}', [CountryController::class, 'getCountryIdByState']);
    Route::get('/get-city-id-by-name/{stateId}/{cityName}', [CountryController::class, 'getCityIdByName']);
    Route::get('/get-pincode-id-by-code/{stateId}/{pincode}', [CountryController::class, 'getPincodeIdByCode']);

    //Sale Invoice
    Route::get('/sale-invoices', [ErpSaleInvoiceController::class, 'index'])->name('sale.invoice.index');
    Route::get('/lease-invoices', [ErpSaleInvoiceController::class, 'index'])->name('sale.leaseInvoice.index');
    Route::get('/delivery-note', [ErpSaleInvoiceController::class, 'index'])->name('sale.deliveryNote.index');
    Route::get('/delivery-note-cum-invoice', [ErpSaleInvoiceController::class, 'index'])->name('sale.deliveryNoteCumInvoice.index');
    Route::get('/service-invoices', [ErpSaleInvoiceController::class, 'index'])->name('sale.serviceInvoice.index');
    Route::get('/service-invoices/create', [ErpSaleInvoiceController::class, 'create'])->name('sale.serviceInvoice.create');
    Route::get('/service-invoices/edit/{id}', [ErpSaleInvoiceController::class, 'edit'])->name('sale.serviceInvoice.edit');



    Route::get('/sale-invoices/create', [ErpSaleInvoiceController::class, 'create'])->name('sale.invoice.create');
    Route::get('/lease-invoices/create', [ErpSaleInvoiceController::class, 'create'])->name('sale.leaseInvoice.create');
    Route::get('/delivery-note/create', [ErpSaleInvoiceController::class, 'create'])->name('sale.deliveryNote.create');
    Route::get('/delivery-note-cum-invoice/create', [ErpSaleInvoiceController::class, 'create'])->name('sale.deliveryNoteCumInvoice.create');
    Route::get('/transporter-invoices/create', [TransporterInvoiceController::class, 'create'])->name('sale.transporterInvoice.create');

    Route::post('/sale-invoices/store', [ErpSaleInvoiceController::class, 'store'])->name('sale.invoice.store');

    Route::get('/sale-invoices/edit/{id}', [ErpSaleInvoiceController::class, 'edit'])->name('sale.invoice.edit');
    Route::get('/lease-invoices/edit/{id}', [ErpSaleInvoiceController::class, 'edit'])->name('sale.leaseInvoice.edit');
    Route::get('/delivery-note/edit/{id}', [ErpSaleInvoiceController::class, 'edit'])->name('sale.deliveryNote.edit');
    Route::get('/transporter-invoices/edit/{id}', [TransporterInvoiceController::class, 'edit'])->name('sale.transporterInvoice.edit');
    Route::get('/delivery-note-cum-invoice/edit/{id}', [ErpSaleInvoiceController::class, 'edit'])->name('sale.deliveryNoteCumInvoice.edit');

    Route::get('/sale-invoices/orders/get', [ErpSaleInvoiceController::class, 'getOrders'])->name('sale.invoice.orders.get');
    Route::get('/sale-invoices/challans/get', [ErpSaleInvoiceController::class, 'getDeliveryChallans'])->name('sale.invoice.challans.get');
    Route::get('/sale-invoices/order', [ErpSaleInvoiceController::class, 'processOrder'])->name('sale.invoice.order.get');
    Route::get('/sale-invoices/challan', [ErpSaleInvoiceController::class, 'processDeliveryChallan'])->name('sale.invoice.challan.get');
    Route::get('/sale-invoices/generate-pdf/{id}/{pattern}', [ErpSaleInvoiceController::class, 'generatePdf'])->name('sale.invoice.generate-pdf');
    Route::post('/sale-invoices/EInvoiceMail', [ErpSaleInvoiceController::class, 'EInvoiceMail'])->name('sale.invoice.eInvoiceMail');
    Route::get('/sale-invoices/pull/items', [ErpSaleInvoiceController::class, 'getSalesItemsForPulling'])->name('sale.invoice.pull.items');
    Route::get('/sale-invoices/pull/land', [ErpSaleInvoiceController::class, 'getSalesLandForPulling'])->name('sale.invoice.pull.land');
    Route::get('/sale-invoices/process/items', [ErpSaleInvoiceController::class, 'processPulledItems'])->name('sale.invoice.process.items');
    Route::post('/sale-invoices/revoke', [ErpSaleInvoiceController::class, 'revokeSalesInvoice'])->name('sale.invoice.revoke');
    Route::get('/sale-invoices/get/pslip-bundles/so', [ErpSaleInvoiceController::class, 'getBundlesForPulledSo'])->name('sale.invoice.get.pslip.bundles.so');
    Route::get('/sale-invoices/get/free-pslips', [ErpSaleInvoiceController::class, 'getFreePslipsForDirectDeliveryNote'])->name('sale.invoice.get.free.pslips');
    Route::post('/sale-invoices/generate/e-invoice', [ErpSaleInvoiceController::class, 'generateEInvoice'])->name('sale.invoice.generate.einvoice');
    Route::post('/sale-invoices/pod', [ErpSaleInvoiceController::class, 'invoicePod'])->name('sale.invoice.pod');
    Route::get('/sales-invoices/report', [ErpSaleInvoiceController::class, 'salesInvoiceReport'])->name('sale.invoice.report');

    Route::post('/sale-invoices/generate/e-way-bill', [ErpSaleInvoiceController::class, 'generateEwayBill'])->name('sale.invoice.generate.ewayBill');

    //Sale Return
    Route::get('/sale-returns', [ErpSaleReturnController::class, 'index'])->name('sale.return.index');
    Route::get('/sale-returns/create', [ErpSaleReturnController::class, 'create'])->name('sale.return.create');
    Route::post('/sale-returns/store', [ErpSaleReturnController::class, 'store'])->name('sale.return.store');
    Route::get('/sale-returns/edit/{id}', [ErpSaleReturnController::class, 'edit'])->name('sale.return.edit');
    Route::get('/sale-returns/orders/get', [ErpSaleReturnController::class, 'getOrders'])->name('sale.return.orders.get');
    Route::get('/sale-returns/challans/get', [ErpSaleReturnController::class, 'getDeliveryChallans'])->name('sale.return.challans.get');
    Route::get('/sale-returns/order', [ErpSaleReturnController::class, 'processOrder'])->name('sale.return.order.get');
    Route::get('/sale-returns/challan', [ErpSaleReturnController::class, 'processDeliveryChallan'])->name('sale.return.challan.get');
    Route::get('/sale-returns/generate-pdf/{id}/{pattern}', [ErpSaleReturnController::class, 'generatePdf'])->name('sale.return.generate-pdf');
    Route::get('/sale-returns/pull/items', [ErpSaleReturnController::class, 'getInvoiceItemsForPulling'])->name('sale.return.pull.items');
    Route::get('/sale-returns/process/items', [ErpSaleReturnController::class, 'processPulledItems'])->name('sale.return.process.items');
    Route::post('/sale-returns/revoke', [ErpSaleReturnController::class, 'revokeSalesReturn'])->name('sale.return.revoke');
    Route::post('/sale-returns/CreditNoteMail', [ErpSaleReturnController::class, 'CreditNoteMail'])->name('sale.return.creditNoteMail');

    Route::get('/sales-return/amend/{id}', [ErpSaleReturnController::class, 'amendmentSubmit'])->name('sale.return.amend');
    Route::get('/sales-return/posting/get', [ErpSaleReturnController::class, 'getPostingDetails'])->name('sale.return.posting.get');
    Route::post('/sales-return/post', [ErpSaleReturnController::class, 'postReturn'])->name('sale.return.post');
    Route::get('/item/stores/details', [ErpSaleReturnController::class, 'getRacksAndBins'])->name('get_store_data');
    Route::get('/item/shelf/details', [ErpSaleReturnController::class, 'getShelfs'])->name('get_shelfs');
    Route::post('/sale-returns/generate/e-invoice', [ErpSaleReturnController::class, 'generateEInvoice'])->name('sale.return.generate.einvoice');
    Route::post('/sale-returns/generate/e-way-bill', [ErpSaleReturnController::class, 'generateEwayBill'])->name('sale.return.generate.ewayBill');
    Route::get('/sales-returns/report', [ErpSaleReturnController::class, 'salesReturnReport'])->name('sale.return.report');



    #filtered document view
    Route::get('/pending-requests', [IndexController::class, 'requests'])->name('riv.requests');
    Route::get('/pending-approvals', [IndexController::class, 'approvals'])->name('riv.approvals');
    Route::get('/pending-postings', [IndexController::class, 'postings'])->name('riv.postings');
    Route::post('/bulk-approvals', [IndexController::class, 'bulkApprovals'])->name('bulk.approvals');
    Route::post('/bulk-postings', [IndexController::class, 'bulkPostings'])->name('bulk.postings');
    Route::post('/bulk-requests', [IndexController::class, 'bulkRequests'])->name('bulk.requests');



    #Erp Rate Contract
    Route::get('/rate-contract', [ErpRCController::class, 'index'])->name('rate.contract.index');
    Route::get('/rate-contract/create', [ErpRCController::class, 'create'])->name('rate.contract.create');
    Route::get('/rate-contract/edit/{id}', [ErpRCController::class, 'edit'])->name('rate.contract.edit');
    Route::get('/rate-contract/amend/{id}', [ErpRCController::class, 'amend'])->name('rate.contract.amend');
    Route::post('/rate-contract/store', [ErpRCController::class, 'store'])->name('rate.contract.store');
    Route::post('/rate-contract/revoke', [ErpRCController::class, 'revoke'])->name('rate.contract.revoke');
    Route::get('/rate-contract/check', [ErpRCController::class, 'checkExistingRateContract'])->name('rate.contract.check');

    //Transport invoice
    Route::get('/transporter-invoices', [TransporterInvoiceController::class, 'index'])->name('sale.transporterInvoice.index');
    Route::get('/transporter-invoices/revoke', [TransporterInvoiceController::class, 'index'])->name('sale.transporterInvoice.revoke');
    Route::get('/transporter-invoices/pull/items', [TransporterInvoiceController::class, 'getSalesItemsForPulling'])->name('sale.transporterInvoice.pull.items');
    Route::get('/transporter-invoices/process/items', [TransporterInvoiceController::class, 'processPulledItems'])->name('sale.transporterInvoice.process.items');
    Route::get('ti/details', [TransporterInvoiceController::class, 'getItemDetails'])->name('sale.transporterInvoice.details');
    Route::post('/transporter-invoices/store', [TransporterInvoiceController::class, 'store'])->name('sale.transporterInvoice.store');

    Route::post('/transporter-invoices/e-invoice-mail', [TransporterInvoiceController::class, 'InvoiceMail'])
        ->name('tranport.invoice.eInvoiceMail');
    Route::post('/transporter-invoices/confirm', [TransporterInvoiceController::class, 'confirm'])
        ->name('sale.transporterInvoice.confirm');
    Route::get('/transporter-invoices/print/{id}', [TransporterInvoiceController::class, 'print'])
        ->name('sale.transporterInvoice.print');
    Route::get('/transporter-invoice/posting/get', [TransporterInvoiceController::class, 'getPostingDetails'])->name('transport.invoice.posting.get');
    Route::post('/transporter-invoice/post', [TransporterInvoiceController::class, 'postInvoice'])->name('transport.invoice.post');



    # Production Work Order Route
    Route::prefix('production-work-orders')
        ->name('pwo.')
        ->controller(PWOController::class)
        ->group(function () {
            Route::get('revoke-document', 'revokeDocument')->name('revoke.document');
            Route::post('close-document', 'closeDocument')->name('close.document');
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('store', 'store')->name('store');
            Route::get('change-item-code', 'changeItemCode')->name('item.code');
            Route::get('change-item-attr', 'changeItemAttr')->name('item.attr.change');
            Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
            Route::get('add-item-row', 'addItemRow')->name('item.row');
            Route::get('get-item-detail', 'getItemDetail')->name('get.itemdetail');
            Route::get('get-item-detail2', 'getItemDetail2')->name('get.itemdetail2');
            Route::get('get-doc-no', 'getDocNumber')->name('doc.no');
            Route::get('edit/{id}', 'edit')->name('edit');
            Route::post('update/{id}', 'update')->name('update');
            # get bom item cost child item
            Route::get('/{id}/pdf', 'generatePdf')->name('generate-pdf');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::get('get-posting', 'getPostingDetails')->name('posting.get');
            Route::get('post-mo', 'postMo')->name('posting.post');
            Route::get('get-so-item', 'getSoItem')->name('get.so.item');
            Route::get('analyze-so-item', 'analyzeSoItem')->name('analyze.so-item');
            Route::post('process-so-item', 'processSoItem')->name('process.so-item');
            Route::get('get-stock', 'getStock')->name('get.stock');
        });

    //Material Issue
    Route::get('/material-issue', [ErpMaterialIssueController::class, 'index'])->name('material.issue.index');
    Route::get('/material-issue/create', [ErpMaterialIssueController::class, 'create'])->name('material.issue.create');
    Route::get('/material-issue/report', [ErpMaterialIssueController::class, 'report'])->name('material.issue.report');
    Route::get('/material-issue/filter', [ErpMaterialIssueController::class, 'filter'])->name('material.issue.filter');
    Route::post('/material-issue/store', [ErpMaterialIssueController::class, 'store'])->name('material.issue.store');
    Route::get('/material-issue/edit/{id}', [ErpMaterialIssueController::class, 'edit'])->name('material.issue.edit');
    Route::post('/material-issue/revoke', [ErpMaterialIssueController::class, 'revokeMaterialIssue'])->name('material.issue.revoke');
    Route::get('/material-issue/vendor/stores', [ErpMaterialIssueController::class, 'getVendorStores'])->name('material.issue.vendor.stores');
    Route::get('/material-issue/mo/process/mo', [ErpMaterialIssueController::class, 'processPulledItems'])->name('material.issue.process.items');
    Route::post('/material-issue/mo/get/items', [ErpMaterialIssueController::class, 'getMoItemsForPulling'])->name('material.issue.pull.items');
    Route::get('/material-issue/{id}/pdf/{pattern}', [ErpMaterialIssueController::class, 'generatePdf'])->name('material.issue.generate-pdf');
    Route::get('/material-issue/multi-stores-location', [ErpMaterialIssueController::class, 'getLocationsWithMultipleStores'])->name('material.issue.multi-store-location');
    Route::get('/mireport', [ErpMaterialIssueController::class, 'materialIssueReport'])->name('mi.report');


    Route::get('/material-return', [ErpMaterialReturnController::class, 'index'])->name('material.return.index');
    Route::get('/material-return/create', [ErpMaterialReturnController::class, 'create'])->name('material.return.create');
    Route::post('/material-return/store', [ErpMaterialReturnController::class, 'store'])->name('material.return.store');
    Route::get('/material-return/edit/{id}', [ErpMaterialReturnController::class, 'edit'])->name('material.return.edit');
    Route::post('/material-return/revoke', [ErpMaterialReturnController::class, 'revokeMaterialreturn'])->name('material.return.revoke');
    Route::get('/material-return/vendor/shipping-addresses', [ErpMaterialReturnController::class, 'getVendorAddresses'])->name('material.return.vendor.addresses');
    Route::get('/material-return/mi/process/mi', [ErpMaterialReturnController::class, 'processPulledItems'])->name('material.return.process.items');
    Route::get('/material-return/mi/get/items', [ErpMaterialReturnController::class, 'getMiItemsForPulling'])->name('material.return.pull.items');
    Route::get('/material-return/{id}/pdf/{pattern}', [ErpMaterialReturnController::class, 'generatePdf'])->name('material.return.generate-pdf');
    Route::get('/material-return/report', [ErpMaterialReturnController::class, 'materialReturnReport'])->name('material.return.report');


    //PSV
    Route::get('/psv', [ErpPSVController::class, 'index'])->name('psv.index');
    Route::get('/psv/create', [ErpPSVController::class, 'create'])->name('psv.create');
    Route::get('/psv/report', [ErpPSVController::class, 'psvReport'])->name('psv.report');
    Route::get('/psv/filter', [ErpPSVController::class, 'filter'])->name('psv.filter');
    Route::post('/psv/store', [ErpPSVController::class, 'store'])->name('psv.store');
    Route::get('/psv/edit/{id}', [ErpPSVController::class, 'edit'])->name('psv.edit');
    Route::post('/psv/revoke', [ErpPSVController::class, 'revokePSV'])->name('psv.revoke');
    Route::get('/psv/vendor/stores', [ErpPSVController::class, 'getVendorStores'])->name('psv.vendor.stores');
    Route::get('/psv/mo/process/mo', [ErpPSVController::class, 'processPulledItems'])->name('psv.process.items');
    Route::get('/psv/mo/get/items', [ErpPSVController::class, 'getMoItemsForPulling'])->name('psv.pull.items');
    Route::get('/psv/search/items', [ErpPSVController::class, 'searchItems'])->name('psv.search.items');
    Route::get('/psv/{id}/pdf/{pattern}', [ErpPSVController::class, 'generatePdf'])->name('psv.generate-pdf');
    Route::get('/psv/multi-stores-location', [ErpPSVController::class, 'getLocationsWithMultipleStores'])->name('psv.multi-store-location');
    Route::get('/psv/posting/get', [ErpPSVController::class, 'getPostingDetails'])->name('psv.posting.get');
    Route::post('/psv/post', [ErpPSVController::class, 'postPsv'])->name('psv.post');
    Route::post('/psv/import', [ErpPSVController::class, 'import'])->name('psv.import');
    Route::get('/psv/itemList', [ErpPSVController::class, 'itemList'])->name('psv.itemlist');
    Route::get('/psv/getAllItems', [ErpPSVController::class, 'getAllItems'])->name('psv.getAllItems');
    Route::get('/psv/{id}/pdf', [ErpPSVController::class, 'generatePdf'])->name('psv.generate-pdf');

    //PL
    Route::get('/pick-list', [ErpPlController::class, 'index'])->name('PL.index');
    Route::get('/pick-list/create', [ErpPlController::class, 'create'])->name('PL.create');
    Route::get('/pick-list/filter', [ErpPlController::class, 'filter'])->name('PL.filter');
    Route::post('/pick-list/store', [ErpPlController::class, 'store'])->name('PL.store');
    Route::get('/pick-list/edit/{id}', [ErpPlController::class, 'edit'])->name('PL.edit');
    Route::post('/pick-list/revoke', [ErpPlController::class, 'revokePL'])->name('PL.revoke');
    Route::get('/pick-list/vendor/stores', [ErpPlController::class, 'getVendorStores'])->name('PL.vendor.stores');
    Route::get('/pick-list/mo/process/mo', [ErpPlController::class, 'processPulledItems'])->name('PL.process.items');
    Route::get('/pick-list/so/get/items', [ErpPlController::class, 'getSoItemsForPulling'])->name('PL.pull.items');
    Route::get('/pick-list/{id}/pdf/{pattern}', [ErpPlController::class, 'generatePdf'])->name('PL.generate-pdf');
    Route::get('/pick-list/multi-stores-location', [ErpPlController::class, 'getLocationsWithMultipleStores'])->name('PL.multi-store-location');
    Route::get('/pick-list/report', [ErpPlController::class, 'PLReport'])->name('PL.report');
    Route::get('/pick-list/posting/get', [ErpPlController::class, 'getPostingDetails'])->name('PL.posting.get');
    Route::post('/pick-list/post', [ErpPlController::class, 'postPL'])->name('PL.post');
    Route::post('/pick-list/import', [ErpPlController::class, 'import'])->name('PL.import');

    //Trip Plan
    Route::get('/trip-plan', [ErpTripPlanController::class, 'index'])->name('trip-plan.index');
    Route::get('/trip-plan/create', [ErpTripPlanController::class, 'create'])->name('trip-plan.create');
    Route::get('/trip-plan/filter', [ErpTripPlanController::class, 'filter'])->name('trip-plan.filter');
    Route::post('/trip-plan/store', [ErpTripPlanController::class, 'store'])->name('trip-plan.store');
    Route::get('/trip-plan/edit/{id}', [ErpTripPlanController::class, 'edit'])->name('trip-plan.edit');
    Route::post('/trip-plan/revoke', [ErpTripPlanController::class, 'revokePL'])->name('trip-plan.revoke');
    Route::get('/trip-plan/vendor/stores', [ErpTripPlanController::class, 'getVendorStores'])->name('trip-plan.vendor.stores');
    Route::get('/trip-plan/mo/process/mo', [ErpTripPlanController::class, 'processPulledItems'])->name('trip-plan.process.items');
    Route::get('/trip-plan/so/get/items', [ErpTripPlanController::class, 'getSoItemsForPulling'])->name('trip-plan.pull.items');
    Route::get('/trip-plan/{id}/pdf/{pattern}', [ErpTripPlanController::class, 'generatePdf'])->name('trip-plan.generate-pdf');
    Route::get('/trip-plan/multi-stores-location', [ErpTripPlanController::class, 'getLocationsWithMultipleStores'])->name('trip-plan.multi-store-location');
    Route::get('/trip-plan/report', [ErpTripPlanController::class, 'PLReport'])->name('trip-plan.report');
    Route::get('/trip-plan/posting/get', [ErpTripPlanController::class, 'getPostingDetails'])->name('trip-plan.posting.get');
    Route::post('/trip-plan/post', [ErpTripPlanController::class, 'postPL'])->name('trip-plan.post');
    Route::post('/trip-plan/import', [ErpTripPlanController::class, 'import'])->name('trip-plan.import');


    //Driver
    Route::get('/logistics/driver', [ErpDriverController::class, 'index'])->name('logistics.driver.index');
    Route::get('/logistics/driver/create', [ErpDriverController::class, 'create'])->name('logistics.driver.create');
    Route::post('/logistics/driver/store', [ErpDriverController::class, 'store'])->name('logistics.driver.store');
    Route::put('/logistics/driver/update/{id}', [ErpDriverController::class, 'update'])->name('logistics.driver.update');
    Route::get('/logistics/driver/edit/{id}', [ErpDriverController::class, 'edit'])->name('logistics.driver.edit');
    Route::delete('/logistics/driver/{id}', [ErpDriverController::class, 'destroy'])->name('logistics.driver.destroy');

    //Vehicle
    Route::get('/logistics/vehicle', [ErpVehicleController::class, 'index'])->name('logistics.vehicle.index');
    Route::get('/logistics/vehicle/create', [ErpVehicleController::class, 'create'])->name('logistics.vehicle.create');
    Route::post('/logistics/vehicle/store', [ErpVehicleController::class, 'store'])->name('logistics.vehicle.store');
    Route::post('/logistics/vehicle/update/{id}', [ErpVehicleController::class, 'update'])->name('logistics.vehicle.update');
    Route::get('/logistics/vehicle/edit/{id}', [ErpVehicleController::class, 'edit'])->name('logistics.vehicle.edit');
    Route::delete('/logistics/vehicle/{id}', [ErpVehicleController::class, 'destroy'])->name('logistics.vehicle.destroy');


    //Vehicle-Types
    Route::get('/logistics/vehicle-type', [ErpVehicleTypeController::class, 'index'])->name('logistics.vehicle-type.index');
    Route::post('/logistics/vehicle-type/store', [ErpVehicleTypeController::class, 'store'])->name('logistics.vehicle-type.store');
    Route::delete('/logistics/vehicle-type/delete-multiple', [ErpVehicleTypeController::class, 'deleteMultiple'])->name('logistics.vehicle-type.delete-multiple');



    //Freight-Charges
    Route::get('/logistics/freight-charges', [ErpFreightChargesController::class, 'index'])->name('logistics.freight-charges.index');
    Route::post('/logistics/freight-charges/store', [ErpFreightChargesController::class, 'store'])->name('logistics.freight-charges.store');
    Route::delete('/logistics/freight-charges/delete-multiple', [ErpFreightChargesController::class, 'deleteMultiple'])->name('logistics.freight-charges.delete-multiple');
    Route::get('/logistics/freight-charges/get-cities-by-state', [ErpFreightChargesController::class, 'getCityByState'])->name('logistics.freight-charges.get-cities-by-state');
    Route::get('/freight-charge-details', [ErpFreightChargesController::class, 'getFreightChargeDetails']);

    //Multi-Point-Pricing
    Route::get('/logistics/multi-point-pricing', [ErpMultiPointPricingController::class, 'index'])->name('logistics.multi-point-pricing.index');
    Route::post('/logistics/multi-point/store', [ErpMultiPointPricingController::class, 'store'])->name('logistics.multi-point.store');
    Route::delete('/logistics/multi-point/delete-multiple', [ErpMultiPointPricingController::class, 'deleteMultiple'])->name('logistics.multi-point.delete-multiple');


    //Multi-Point Fixed
    Route::get('/logistics/multi-point-fixed/create', [ErpMultiPointFixedController::class, 'create'])->name('logistics.multi-point-fixed.create');
    Route::post('/logistics/multi-point-fixed/store', [ErpMultiPointFixedController::class, 'store'])->name('logistics.multi-point-fixed.store');
    Route::put('/logistics/multi-point-fixed/update/{id}', [ErpMultiPointFixedController::class, 'update'])->name('logistics.multi-point-fixed.update');
    Route::get('/logistics/multi-point-fixed/edit/{id}', [ErpMultiPointFixedController::class, 'edit'])->name('logistics.multi-point-fixed.edit');
    Route::delete('/logistics/multi-point-fixed/{id}', [ErpMultiPointFixedController::class, 'destroy'])->name('logistics.multi-point-fixed.destroy');
    Route::get('/logistics/multi-point-fixed/get-cities-by-state', [ErpMultiPointFixedController::class, 'getCityByState'])->name('logistics.multi-point-fixed.get-cities-by-state');


    //Route-Master
    Route::get('/logistics/route-master', [ErpRouteMasterController::class, 'index'])->name('logistics.route-master.index');
    Route::post('/logistics/route-master/store', [ErpRouteMasterController::class, 'store'])->name('logistics.route-master.store');
    Route::delete('/logistics/route-master/delete-multiple', [ErpRouteMasterController::class, 'deleteMultiple'])->name('logistics.route-master.delete-multiple');
    Route::get('/logistics/route-master/get-states-by-country', [ErpRouteMasterController::class, 'getStatesByCountry'])->name('logistics.route-master.get-states-by-country');
    Route::get('/logistics/route-master/get-cities-by-state', [ErpRouteMasterController::class, 'getCitiesByState'])->name('logistics.route-master.get-cities-by-state');

    //Lorry Receipt
    Route::get('/logistics/lorry-receipt', [ErpLorryReceiptController::class, 'index'])->name('logistics.lorry-receipt.index');
    Route::get('/logistics/lorry-receipt/create', [ErpLorryReceiptController::class, 'create'])->name('logistics.lorry-receipt.create');
    Route::post('/logistics/lorry-receipt/store', [ErpLorryReceiptController::class, 'store'])->name('logistics.lorry-receipt.store');
    Route::put('/logistics/lorry-receipt/update/{id}', [ErpLorryReceiptController::class, 'update'])->name('logistics.lorry-receipt.update');
    Route::get('/logistics/lorry-receipt/edit/{id}', [ErpLorryReceiptController::class, 'edit'])->name('logistics.lorry-receipt.edit');
    Route::delete('/logistics/lorry-receipt/{id}', [ErpLorryReceiptController::class, 'destroy'])->name('logistics.lorry-receipt.destroy');
    Route::get('/get-cost-centers-by-location/{locationId}', [ErpLorryReceiptController::class, 'getCostCentersByLocation']);
    Route::post('/get-location-pricing', [ErpLorryReceiptController::class, 'getFreePointData']);
    Route::post('/logistics/lorry-receipt/send-mail', [ErpLorryReceiptController::class, 'lorryMail'])->name('logistics.lorry-receipt.lorryMail');
    Route::post('/logistics/lorry-receipt/revoke', [ErpLorryReceiptController::class, 'revoke'])->name('logistics.lorry-receipt.revoke');
    Route::get('/logistics/lorry-receipt/generate-pdf/{id}', [ErpLorryReceiptController::class, 'generatePdf'])->name('logistics.lorry-receipt.generate-pdf');


    //Production Slip
    Route::get('/production-slip', [ErpProductionSlipController::class, 'index'])->name('production.slip.index');
    Route::get('/production-slip/create', [ErpProductionSlipController::class, 'create'])->name('production.slip.create');
    Route::post('/production-slip/store', [ErpProductionSlipController::class, 'store'])->name('production.slip.store');
    Route::get('/production-slip/edit/{id}', [ErpProductionSlipController::class, 'edit'])->name('production.slip.edit');
    Route::post('/production-slip/revoke', [ErpProductionSlipController::class, 'revoke'])->name('production.slip.revoke');
    Route::get('/production-slip/pwo/process/pwo', [ErpProductionSlipController::class, 'processPulledItems'])->name('production.slip.process.items');
    Route::get('/production-slip/pwo/get/items', [ErpProductionSlipController::class, 'getPwoItemsForPulling'])->name('production.slip.pull.items');

    // Production Slip
    Route::get('/production-slip/posting/details', [ProductionSlipController::class, 'getPostingDetails'])->name('production.slip.get.posting.details');
    Route::post('/production-slip/post/voucher', [ProductionSlipController::class, 'postPslipVoucher'])->name('production.slip.post.voucher');
    Route::delete('/production-slip/{erpProductionSlip}/{amendment}', [ProductionSlipController::class, 'destroy'])->name('production.slip.destroy');

    #get item detail for the consumption
    Route::get('/production-slip/get-item-detail', [ErpProductionSlipController::class, 'getItemDetail'])->name('production.slip.item.detail');
    Route::get('/production-slip/{id}/pdf', [ErpProductionSlipController::class, 'generatepdf'])->name('production.slip.generate-pdf');
    Route::get('/production-slip/get-substore', [ErpProductionSlipController::class, 'getSubStore'])->name('production.slip.substore');
    Route::get('/production-slip/get-item-alternate', [ErpProductionSlipController::class, 'getAlterItems'])->name('production.slip.clone_alterItems');
    Route::get('/production-slip/get-item-attribute', [ErpProductionSlipController::class, 'getItemAttribute'])->name('production.slip.getattributes');
    Route::get('/production-slip/get-avl-stock', [ErpProductionSlipController::class, 'getAvlStock'])->name('production.slip.avlStock');
    Route::get('/bom-vs-actual', [ProductionSlipController::class, 'bomVsActualReport'])->name('bomVsActual.report');
    Route::get('/bom-vs-actual/download', [ProductionSlipController::class, 'downloadBomVsActualWithOutfile'])->name('bomVsActual.download');

    Route::prefix('stores')->controller(StoreController::class)->group(function () {
        # Get Store Address Ajax
        Route::get('get-location', 'getLocation')->name('store.get');
        Route::get('/', 'index')->name('store.index');
        Route::get('/create', 'create')->name('store.create');
        Route::post('/', 'store')->name('store.store');
        Route::post('/rack', 'rackStore')->name('rack.store');
        Route::post('/shelf', 'shelfStore')->name('shelf.store');
        Route::post('/bin', 'binStore')->name('bin.store');
        Route::get('/get-racks', 'getRacks')->name('store.getRacks');
        Route::get('/get-shelfs', 'getShelves')->name('store.getShelves');
        Route::get('/get-bins', 'getBins')->name('store.getBins');
        Route::get('/get-mapped-racks', 'getMappedRacks')->name('store.getMappedRacks');
        Route::get('/get-mapped-shelfs', 'getMappedShelves')->name('store.getMappedShelves');
        Route::get('/get-mapped-bins', 'getMappedBins')->name('store.getMappedBins');

        Route::get('/stores/searchRacks', 'searchRacks')->name('store.searchRacks');
        Route::get('/stores/searchShelves', 'searchShelves')->name('store.searchShelves');
        Route::get('/stores/searchBins', 'searchBins')->name('store.searchBins');

        Route::get('/{id}/edit', 'edit')->name('store.edit');
        Route::put('/{id}', 'update')->name('store.update');
        Route::delete('/{id}', 'destroy')->name('store.destroy');
        Route::delete('/racks/{id}', 'destroyRack')->name('rack.delete');
        Route::delete('/shelfs/{id}', 'destroyShelf')->name('shelf.delete');
        Route::delete('/bins/{id}', 'destroyBin')->name('bin.delete');

        Route::get('/store/racks-bins', 'getStoreRacksAndBins')->name('store.racksAndBins');
        Route::get('/rack/shelfs', 'getRackShelfs')->name('store.rack.shelfs');
        Route::get('get-sub-store', 'getSubStore')->name('get.sub.store');
    });

    Route::prefix('sub-stores')->name('subStore.')->controller(SubStoreController::class)->group(function () {
        # Get Store Address Ajax
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/store-wise', 'getSubStoresOfStore')->name('get.from.stores');
    });

   Route::prefix('rgr-store-mappings')->controller(ErpRgrStoreMappingController::class)->group(function () {
        Route::get('/', 'index')->name('store-mappings.index');
        Route::post('/', 'store')->name('store-mappings.store');
        Route::get('/create', 'create')->name('store-mappings.create');
        Route::get('/{id}/edit', 'edit')->name('store-mappings.edit');
        Route::put('/{id}', 'update')->name('store-mappings.update');
        Route::delete('/destroy', 'destroy')->name('store-mappings.destroy');
        Route::get('/autocomplete/categories', 'categories')->name('autocomplete.categories');
        Route::get('/autocomplete/stores', 'stores')->name('autocomplete.stores');
        Route::get('/autocomplete/substores', 'substores')->name('autocomplete.substores');
    });

    Route::prefix('budgets')->controller(BudgetController::class)->group(function () {
        Route::get('/', 'index')->name('budget.index');
        Route::get('/create', 'create')->name('budget.create');
        Route::post('/', 'store')->name('budget.store');
        Route::get('/{budget}', 'show')->name('budget.show');
        Route::get('/edit/{budget}', 'edit')->name('budget.edit');
        Route::post('/{budget}', 'update')->name('budget.update');
        Route::delete('/{budget}', 'destroy')->name('budget.destroy');
        Route::get('/get-request/{book_id}', 'getRequests')->name('budget.requests');
    });

    Route::prefix('banks')->controller(BankController::class)->group(function () {
        Route::get('/', 'index')->name('bank.index');
        Route::get('/create', 'create')->name('bank.create');
        Route::post('/', 'store')->name('bank.store');
        Route::get('/search', 'search')->name('bank.search');
        Route::get('/search/ledger', 'getLedger')->name('bank.ledger.search');
        Route::get('/{id}', 'show')->name('bank.show');
        Route::get('/{id}/edit', 'edit')->name('bank.edit');
        Route::put('/{id}', 'update')->name('bank.update');
        Route::delete('/bank-detail/{id}', 'deleteBankDetail')->name('bank-detail.delete');
        Route::delete('/{id}', 'destroy')->name('bank.destroy');
        Route::get('/get-request/{book_id}', 'getRequests')->name('bank.requests');
        Route::get('/ifsc/{id}', 'getIfscDetails')->name('bank.ifsc.details');
    });


    // Loan Progress Routes

    Route::prefix('loan/progress/appraisal')->controller(AppraisalController::class)
        ->name('loanAppraisal.')->group(function () {


            Route::get('/', 'index')->name('index');
            Route::get('/view', 'view')->name('view');
            Route::get('/home-loan/view/{id}', 'viewHomeLoan')->name('viewHomeLoan');
            Route::get('/vehicle-loan/view/{id}', 'viewVehicleLoan')->name('viewVehicleLoan');
            Route::get('/term-loan/view/{id}', 'viewTermLoan')->name('viewTermLoan');
            Route::get('/create/{id}', 'create')->name('create');
            Route::post('/save', 'save')->name('save');

            Route::post('/get-interest-rate', 'getInterestRate')->name('getInterestRate');
            Route::post('/get-dpr-fields', 'getDprFields')->name('getDprFields');
            Route::delete('/delete-document', 'deleteDocument')->name('deleteDocument');
            Route::post('/loan-return', 'loanReturn')->name('loan-return');
            Route::post('/loan-reject', 'loanReject')->name('loan-reject');
        });

    Route::prefix('loan/progress/approval')->controller(ApprovalController::class)
        ->name('loanApproval.')->group(function () {

            Route::get('/', 'index')->name('index');
            Route::get('/view', 'view')->name('view');
            Route::get('/home-loan/view/{id}', 'viewHomeLoan')->name('viewHomeLoan');
            Route::get('/vehicle-loan/view/{id}', 'viewVehicleLoan')->name('viewVehicleLoan');
            Route::get('/term-loan/view/{id}', 'viewTermLoan')->name('viewTermLoan');

            Route::post('/loan-approve', 'loanApprove')->name('loan-approve');
            Route::post('/loan-return', 'loanReturn')->name('loan-return');
            Route::post('/loan-reject', 'loanReject')->name('loan-reject');
            Route::get('/approval/{id}', 'approval')->name('approval');
            Route::post('/update-approval', 'updateApproval')->name('update-approval');
        });

    Route::prefix('loan/progress/assessment')->controller(AssessmentController::class)
        ->name('loanAssessment.')->group(function () {

            Route::get('/', 'index')->name('index');
            Route::get('/view', 'view')->name('view');
            Route::get('/home-loan/view/{id}', 'viewHomeLoan')->name('viewHomeLoan');
            Route::get('/vehicle-loan/view/{id}', 'viewVehicleLoan')->name('viewVehicleLoan');
            Route::get('/term-loan/view/{id}', 'viewTermLoan')->name('viewTermLoan');
            Route::post('/assessment-proceed', 'assessmentProceed')->name('assessment-proceed');

            Route::post('/loan-return', 'loanReturn')->name('loan-return');
            Route::post('/loan-reject', 'loanReject')->name('loan-reject');
        });

    Route::prefix('loan/progress/legal-documentation')->controller(LegalDocumentationController::class)
        ->name('loanLegalDocumentation.')->group(function () {

            Route::get('/', 'index')->name('index');
            Route::get('/view', 'view')->name('view');
            Route::get('/home-loan/view/{id}', 'viewHomeLoan')->name('viewHomeLoan');
            Route::get('/vehicle-loan/view/{id}', 'viewVehicleLoan')->name('viewVehicleLoan');
            Route::get('/term-loan/view/{id}', 'viewTermLoan')->name('viewTermLoan');

            Route::post('/loan-legal-document', 'loanLegalDocument')->name('loan-legal-document');
            Route::post('/loan-return', 'loanReturn')->name('loan-return');
            Route::post('/loan-reject', 'loanReject')->name('loan-reject');
        });

    Route::prefix('loan/progress/processing-fee')->controller(ProcessingFeeController::class)
        ->name('loanProcessingFee.')->group(function () {

            Route::get('/', 'index')->name('index');
            Route::get('/view', 'view')->name('view');
            Route::get('/home-loan/view/{id}', 'viewHomeLoan')->name('viewHomeLoan');
            Route::get('/vehicle-loan/view/{id}', 'viewVehicleLoan')->name('viewVehicleLoan');
            Route::get('/term-loan/view/{id}', 'viewTermLoan')->name('viewTermLoan');

            Route::post('/loan-process', 'loanProcess')->name('loan-process');
            Route::get('/loan-invoice/posting/get', 'getPostingDetails')->name('getPostingDetails');
            Route::post('/loan-invoice/post', 'postInvoice')->name('post');
            Route::post('/loan-return', 'loanReturn')->name('loan-return');
            Route::post('/loan-reject', 'loanReject')->name('loan-reject');
        });

    Route::prefix('loan/progress/sanction-letter')->controller(SanctionLetterController::class)
        ->name('loanSanctionLetter.')->group(function () {

            Route::get('/', 'index')->name('index');
            Route::get('/view', 'view')->name('view');
            Route::get('/home-loan/view/{id}', 'viewHomeLoan')->name('viewHomeLoan');
            Route::get('/vehicle-loan/view/{id}', 'viewVehicleLoan')->name('viewVehicleLoan');
            Route::get('/term-loan/view/{id}', 'viewTermLoan')->name('viewTermLoan');

            Route::post('/loan-accept', 'loanAccept')->name('loan-accept');
            Route::post('/loan-return', 'loanReturn')->name('loan-return');
            Route::post('/loan-reject', 'loanReject')->name('loan-reject');
            // Route::post('/assessment-proceed', 'assessmentProceed')->name('assessment-proceed');

        });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('/services/edit/{id}', [ServiceController::class, 'edit'])->name('services.edit');
        Route::post('/services/update', [ServiceController::class, 'update'])->name('services.update');
    });

    //Route for Document Drive
    Route::get('/my-drive', [DocumentDriveController::class, 'index'])->name('document-drive.index');
    Route::get('/my-drive/shared-with-me/{id?}', [DocumentDriveController::class, 'sharedWithMe'])->name('document-drive.shared-with-me');
    Route::get('/my-drive/shared-drive/{id?}', [DocumentDriveController::class, 'sharedDrive'])->name('document-drive.shared-drive');
    Route::get('/my-drive/folder/{id}', [DocumentDriveController::class, 'show'])->name('document-drive.folder.show');
    Route::get('/my-drive/files/download/{id}', [DocumentDriveController::class, 'download'])->name('document-drive.files.download');
    Route::get('/my-drive/folders/download/{id}', [DocumentDriveController::class, 'downloadFolder'])->name('document-drive.folders.download');
    Route::delete('/my-drive/delete-file/{id}', [DocumentDriveController::class, 'file_destroy'])->name('document-drive.file.delete');
    Route::delete('/my-drive/delete-folder/{id}', [DocumentDriveController::class, 'folder_destroy'])->name('document-drive.folder.delete');
    Route::post('/my-drive/delete', [DocumentDriveController::class, 'destroy'])->name('document-drive.delete');
    Route::post('/my-drive/folder/create/{parentId?}', [DocumentDriveController::class, 'create_folder'])->name('document-drive.folder.store');
    Route::post('/my-drive/file/upload/{parentId?}', [DocumentDriveController::class, 'upload'])->name('document-drive.file.upload');
    Route::post('/my-drive/folder/upload/{parentId?}', [DocumentDriveController::class, 'uploadFolder'])->name('document-drive.folder.upload');
    Route::get('/my-drive/file/{id}', [DocumentDriveController::class, 'showFile'])->name('document-drive.file.show');
    Route::post('/my-drive/rename/{parent?}', [DocumentDriveController::class, 'rename'])->name('document-drive.rename');
    Route::post('/my-drive/move-to-folder', [DocumentDriveController::class, 'moveFolder'])->name('document-drive.movetofolder');
    Route::post('/my-drive/move-to-folder-multiple', [DocumentDriveController::class, 'moveFolderMultiple'])->name('document-drive.movetofolder.multiple');
    Route::post('/my-drive/share', [DocumentDriveController::class, 'share'])->name('document-drive.share');
    Route::post('/my-drive/share-all', [DocumentDriveController::class, 'shareMultiple'])->name('document-drive.share.all');
    Route::post('/my-drive/download-zip', [DocumentDriveController::class, 'downloadZip'])->name('document-drive.download-zip');
    Route::post('/my-drive/tags', [DocumentDriveController::class, 'addTagsToItems'])->name('document-drive.tags');

    Route::resource('file-tracking', FileTrackingController::class);
    Route::get('/file-tracking/file/{id}', [FileTrackingController::class, 'showFile'])->name('file-tracking.showFile');
    Route::get('/file-tracking/sign-file/{id}', [FileTrackingController::class, 'showSignFile'])->name('file-tracking.showSignFile');
    Route::post('/file-tracking/sign/{id}', [FileTrackingController::class, 'sign'])->name('file-tracking.sign');

    Route::resource('user-signature', UserSignatureController::class);
    Route::get('/user-signature/sign/{id}', [UserSignatureController::class, 'showFile'])->name('user-signature.showFile');
    Route::resource('fixed-asset/registration', RegistrationController::class)->names([
        'index' => 'finance.fixed-asset.registration.index',
        'create' => 'finance.fixed-asset.registration.create',
        'store' => 'finance.fixed-asset.registration.store',
        'show' => 'finance.fixed-asset.registration.show',
        'edit' => 'finance.fixed-asset.registration.edit',
        'update' => 'finance.fixed-asset.registration.update',
        'destroy' => 'finance.fixed-asset.registration.destroy',
    ]);

    Route::get('fixed-asset/registration/amendment/{id}', [RegistrationController::class, 'amendment'])->name('finance.fixed-asset.registration.amendment');
    Route::post('fixed-asset/registration/approval', [RegistrationController::class, 'documentApproval'])->name('finance.fixed-asset.registration.approval');
    Route::get('fixed-asset/sub_asset', [RegistrationController::class, 'subAsset'])->name('finance.fixed-asset.sub_asset');
    Route::get('fixed-asset/sub_asset_details', [RegistrationController::class, 'subAssetDetails'])->name('finance.fixed-asset.sub_asset_details');
    Route::get('fixed-asset/getLedgerGroups', [RegistrationController::class, 'getLedgerGroups'])->name('finance.fixed-asset.getLedgerGroups');
    Route::get('fixed-asset/fetch-grn-data', [RegistrationController::class, 'fetchGrnData'])->name('finance.fixed-asset.fetch.grn.data');
    Route::post('fixed-asset/asset-search', [RegistrationController::class, 'assetSearch'])->name('finance.fixed-asset.asset-search');
    Route::post('fixed-asset/sub-asset-search', [RegistrationController::class, 'subAssetSearch'])->name('finance.fixed-asset.sub_asset_search');
    Route::get('fixed-asset/get-categories', [RegistrationController::class, 'getCategories'])->name('finance.fixed-asset.get-categories');
    Route::get('fixed-asset/get-locations', [RegistrationController::class, 'getLocations'])->name('finance.fixed-asset.get-locations');
    Route::get('fixed-asset/get-cost-centers', [RegistrationController::class, 'getCostCenters'])->name('finance.fixed-asset.get-cost-centers');
    Route::post('fixed-asset/check-code', [RegistrationController::class, 'checkCode'])->name('finance.fixed-asset.check-code');
    Route::get('fixed-asset/get-cost-centers', [RegistrationController::class, 'getCostCenters'])->name('finance.fixed-asset.get-cost-centers');
    Route::get('fixed-asset/export', [RegistrationController::class, 'export'])->name('finance.fixed-asset.export');
    Route::post('fixed-asset/category-search', [RegistrationController::class, 'categorySearch'])->name('finance.fixed-asset.category-search');
    Route::post('fixed-asset/registration/filter', [RegistrationController::class, 'index'])->name('finance.fixed-asset.registration.filter');
    Route::get('fixed-asset/import', [RegistrationController::class, 'showImportForm'])->name('finance.fixed-asset.show.import');
    Route::post('fixed-asset/import', [RegistrationController::class, 'import'])->name('finance.fixed-asset.import');
    Route::get('fixed-asset/export-successful', [RegistrationController::class, 'exportSuccessfulItems'])->name('finance.fixed-asset.export.successful');
    Route::get('fixed-asset/export-failed', [RegistrationController::class, 'exportFailedItems'])->name('finance.fixed-asset.export.failed');
    Route::post('fixed-asset/get-code', [RegistrationController::class, 'generateAssetCode'])->name('finance.fixed-asset.asset-code');



    Route::resource('fixed-asset/issue-transfer', IssueTransferController::class)->names([
        'index' => 'finance.fixed-asset.issue-transfer.index',
        'create' => 'finance.fixed-asset.issue-transfer.create',
        'store' => 'finance.fixed-asset.issue-transfer.store',
        'show' => 'finance.fixed-asset.issue-transfer.show',
        'edit' => 'finance.fixed-asset.issue-transfer.edit',
        'update' => 'finance.fixed-asset.issue-transfer.update',
    ]);

    Route::resource('fixed-asset/insurance', InsuranceController::class)->names([
        'index' => 'finance.fixed-asset.insurance.index',
        'create' => 'finance.fixed-asset.insurance.create',
        'store' => 'finance.fixed-asset.insurance.store',
        'show' => 'finance.fixed-asset.insurance.show',
        'edit' => 'finance.fixed-asset.insurance.edit',
        'update' => 'finance.fixed-asset.insurance.update',
    ]);
    Route::resource('fixed-asset/maintenance', MaintenanceController::class)->names([
        'index' => 'finance.fixed-asset.maintenance.index',
        'create' => 'finance.fixed-asset.maintenance.create',
        'store' => 'finance.fixed-asset.maintenance.store',
        'show' => 'finance.fixed-asset.maintenance.show',
        'edit' => 'finance.fixed-asset.maintenance.edit',
        'update' => 'finance.fixed-asset.maintenance.update',
    ]);
    Route::get('fixed-asset/setup/category', [SetupController::class, 'category'])->name('finance.fixed-asset.setup.category');

    Route::resource('fixed-asset/setup', SetupController::class)->names([
        'index' => 'finance.fixed-asset.setup.index',
        'create' => 'finance.fixed-asset.setup.create',
        'store' => 'finance.fixed-asset.setup.store',
        'show' => 'finance.fixed-asset.setup.show',
        'edit' => 'finance.fixed-asset.setup.edit',
        'update' => 'finance.fixed-asset.setup.update',
        'destroy' => 'finance.fixed-asset.setup.destroy',
    ]);
    Route::post('/setup/generate-prefix', [SetupController::class, 'generate_prefix'])->name('generate-setup-prefix');
    Route::post('/setup/check-prefix', [SetupController::class, 'checkPrefix'])->name('setup-check-prefix');

    Route::get('fixed-asset/depreciation/posting/get', [DepreciationController::class, 'getPostingDetails'])->name('finance.fixed-asset.depreciation.posting.get');
    Route::post('fixed-asset/depreciation/post', [DepreciationController::class, 'postInvoice'])->name('finance.fixed-asset.depreciation.post');
    Route::get('fixed-asset/depreciation/assets', [DepreciationController::class, 'getAssets'])->name('finance.fixed-asset.depreciation.assets');
    Route::post('fixed-asset/depreciation/approval', [DepreciationController::class, 'documentApproval'])->name('finance.fixed-asset.depreciation.approval');
    Route::get('fixed-asset/depreciation/amendment/{id}', [DepreciationController::class, 'amendment'])->name('finance.fixed-asset.depreciation.amendment');

    Route::resource('fixed-asset/depreciation', DepreciationController::class)->names([
        'index' => 'finance.fixed-asset.depreciation.index',
        'create' => 'finance.fixed-asset.depreciation.create',
        'store' => 'finance.fixed-asset.depreciation.store',
        'show' => 'finance.fixed-asset.depreciation.show',
        'edit' => 'finance.fixed-asset.depreciation.edit',
        'update' => 'finance.fixed-asset.depreciation.update',
        'destroy' => 'finance.fixed-asset.depreciation.destroy',
    ]);
    Route::get('fixed-asset/it-dep/assets', [ITDepreciationController::class, 'getAssets'])->name('finance.fixed-asset.it.dep.assets');
    Route::resource('fixed-asset/it-dep', ITDepreciationController::class)->names([
        'index' => 'finance.fixed-asset.it.dep.index',
    ]);
    Route::post('fixed-asset-rdv-val', [ITDepreciationController::class, 'getFixedAssetRDV'])
    ->name('fixed-asset-rdv');
    Route::post('get-fixed-asset-rdv-response', [ITDepreciationController::class, 'getFixedAssetRDVResponse'])
    ->name('get-fixed-asset-rdv-response');


    Route::resource('fixed-asset/split', SplitController::class)->names([
        'index' => 'finance.fixed-asset.split.index',
        'create' => 'finance.fixed-asset.split.create',
        'store' => 'finance.fixed-asset.split.store',
        'show' => 'finance.fixed-asset.split.show',
        'edit' => 'finance.fixed-asset.split.edit',
        'update' => 'finance.fixed-asset.split.update',
    ]);
    Route::post('fixed-asset/split/approval', [SplitController::class, 'documentApproval'])->name('finance.fixed-asset.split.approval');
    Route::post('fixed-asset/split/filter', [SplitController::class, 'index'])->name('finance.fixed-asset.split.filter');
    Route::get('fixed-asset/split/posting/get', [SplitController::class, 'getPostingDetails'])->name('finance.fixed-asset.split.posting.get');
    Route::post('fixed-asset/split/post', [SplitController::class, 'postInvoice'])->name('finance.fixed-asset.split.post');
    Route::get('fixed-asset/depreciation/amendment/{id}', [SplitController::class, 'amendment'])->name('finance.fixed-asset.split.amendment');


    Route::resource('fixed-asset/merger', MergerController::class)->names([
        'index' => 'finance.fixed-asset.merger.index',
        'create' => 'finance.fixed-asset.merger.create',
        'store' => 'finance.fixed-asset.merger.store',
        'show' => 'finance.fixed-asset.merger.show',
        'edit' => 'finance.fixed-asset.merger.edit',
        'update' => 'finance.fixed-asset.merger.update',
    ]);
    Route::post('fixed-asset/merger/approval', [MergerController::class, 'documentApproval'])->name('finance.fixed-asset.merger.approval');
    Route::post('fixed-asset/merger/filter', [MergerController::class, 'index'])->name('finance.fixed-asset.merger.filter');
    Route::get('fixed-asset/merger/posting/get', [MergerController::class, 'getPostingDetails'])->name('finance.fixed-asset.merger.posting.get');
    Route::post('fixed-asset/merger/post', [MergerController::class, 'postInvoice'])->name('finance.fixed-asset.merger.post');
    Route::get('fixed-asset/merger/amendment/{id}', [MergerController::class, 'amendment'])->name('finance.fixed-asset.merger.amendment');

    Route::resource('fixed-asset/rev', RevImpController::class)->names([
        'index' => 'finance.fixed-asset.revaluation-impairement.index',
        'create' => 'finance.fixed-asset.revaluation-impairement.create',
        'store' => 'finance.fixed-asset.revaluation-impairement.store',
        'show' => 'finance.fixed-asset.revaluation-impairement.show',
        'edit' => 'finance.fixed-asset.revaluation-impairement.edit',
        'update' => 'finance.fixed-asset.revaluation-impairement.update',
    ]);
    Route::post('fixed-asset/rev/approval', [RevImpController::class, 'documentApproval'])->name('finance.fixed-asset.revaluation-impairement.approval');
    Route::post('fixed-asset/rev/filter', [RevImpController::class, 'index'])->name('finance.fixed-asset.revaluation-impairement.filter');
    Route::get('fixed-asset/rev/posting/get', [RevImpController::class, 'getPostingDetails'])->name('finance.fixed-asset.revaluation-impairement.posting.get');
    Route::post('fixed-asset/rev/post', [RevImpController::class, 'postInvoice'])->name('finance.fixed-asset.revaluation-impairement.post');
    Route::get('fixed-asset/rev/amendment/{id}', [RevImpController::class, 'amendment'])->name('finance.fixed-asset.revaluation-impairement.amendment');


    Route::resource('asset-category', AssetCategoryController::class);
    Route::get('plant/bom/get-item-attribute', [MaintBomController::class, 'getItemAttribute'])->name('maint-bom.attr');
    Route::get('plant/search', [MaintBomController::class, 'search'])->name('plant.search');
    Route::post('plant/bom/approval', [MaintBomController::class, 'documentApproval'])->name('maint-bom.approval');


    Route::resource('plant/maint-bom', MaintBomController::class)->names([
        'index' => 'maint-bom.index',
        'create' => 'maint-bom.create',
        'store' => 'maint-bom.store',
        'update' => 'maint-bom.update',
        'show' => 'maint-bom.show',
        'edit' => 'maint-bom.edit',
    ]);

    Route::prefix('maintenance-types')->controller(ErpMaintenanceTypeController::class)->group(function () {
        Route::get('/', 'index')->name('maintenance-types.index');
        Route::post('/', 'store')->name('maintenance-types.store');
        Route::delete('/', 'delete')->name('maintenance-types.delete');
    });

    Route::prefix('defect-types')->controller(ErpDefectTypeController::class)->group(function () {
        Route::get('/', 'index')->name('defect-types.index');
        Route::post('/', 'store')->name('defect-types.store');
        Route::delete('/', 'delete')->name('defect-types.delete');
    });
    Route::post('plant/maint-wo/close-work-order', [MaintWoController::class, 'closeWorkOrder'])->name('maint-wo.close-work-order');
    Route::get('plant/maint-wo/get-ajax-data', [MaintWoController::class, 'ajaxData'])->name('maint-wo.ajax-data');
    Route::get('plant/maint-wo/get-equipment-spare-parts', [MaintWoController::class, 'getEquipmentSpareParts'])->name('maint-wo.get-equipment-spare-parts');
    Route::post('plant/maint-wo/approve', [MaintWoController::class, 'documentApproval'])->name('maint-wo.approval');
    Route::post('plant/maint-wo/filter', [MaintWoController::class, 'filter'])->name('maint-wo.filter');

    Route::resource('plant/maint-wo', MaintWoController::class)->names([
        'index' => 'maint-wo.index',
        'create' => 'maint-wo.create',
        'store' => 'maint-wo.store',
        'update' => 'maint-wo.update',
        'show' => 'maint-wo.show',
        'edit' => 'maint-wo.edit',
    ]);


    Route::get('plant/defect-noti/get-ajax-data', [DefectNotificationController::class, 'getDefectNotificationsData'])->name('defect-notification.ajax-data');
    Route::get('plant/populate-modal', [MaintWoController::class, 'populateModal'])->name('maint-wo.populateModal');

    Route::get('plant/defect-noti/filter', [DefectNotificationController::class, 'filter'])->name('defect-notification.filter');
    Route::get('plant/defect-noti/{id}/get', [DefectNotificationController::class, 'getDefectNotification'])->name('defect-notification.get');
    Route::post('plant/defect-noti/get-checklists', [DefectNotificationController::class, 'getChecklistsByMaintenanceType'])->name('defect-notification.get-checklists');

    Route::resource('plant/defect-noti', DefectNotificationController::class)
        ->names([
            'index' => 'defect-notification.index',
            'create' => 'defect-notification.create',
            'store' => 'defect-notification.store',
            'update' => 'defect-notification.update',
            'show' => 'defect-notification.show',
            'edit' => 'defect-notification.edit',
        ]);


    Route::get('cashflow-statement/{page?}', [CashflowReportController::class, 'index'])->name('finance.cashflow');
    Route::post('/cashflow/export', [CashflowReportController::class, 'export'])->name('cashflow.export');

    Route::post('/cashflow/add-scheduler', [CashflowReportController::class, 'addScheduler'])->name('finance.cashflow.add.scheduler');
    Route::get('tds-report', [TDSReportController::class, 'index'])->name('finance.tds');

    Route::controller(GstrController::class)->prefix('finance/gstr')->group(function () {
        Route::get('/', 'index')->name('finance.gstr.index');
        Route::get('/json', 'json')->name('finance.gstr.json');
        Route::get('/details/{id}', 'details')->name('finance.gstr.details');
        Route::get('/detail/csv/{id}', 'detailCsv')->name('finance.gstr.detail-csv');
    });



    // Route::get('/index', [LoanManagementController::class, 'index'])->name('loan.index');
    // Route::get('/view-all-detail/{id}', [LoanManagementController::class, 'viewAllDetail'])->name('loan.view_all_detail');
    // Route::get('/home-loan', [LoanManagementController::class, 'home_loan'])->name('loan.home-loan');
    // Route::get('/vehicle-loan', [LoanManagementController::class, 'vehicle_loan'])->name('loan.vehicle-loan');
    // Route::get('/term-loan', [LoanManagementController::class, 'term_loan'])->name('loan.term-loan');
    // Route::get('/disbursement', [LoanManagementController::class, 'disbursement'])->name('loan.disbursement');
    // Route::get('/recovery', [LoanManagementController::class, 'recovery'])->name('loan.recovery');
    // Route::get('/settlement', [LoanManagementController::class, 'settlement'])->name('loan.settlement');

    // // Loan Dashboard
    // Route::get('/dashboard', [LoanDashboardController::class, 'dashboard'])->name('loan.dashboard');
    // Route::get('/dashboard/loan-analytics', [LoanDashboardController::class, 'getDashboardLoanAnalytics'])->name('loan.analytics');
    // Route::get('/dashboard/loan-kpi', [LoanDashboardController::class, 'getDashboardLoanKpi'])->name('loan.kpi');
    // Route::get('/dashboard/loan-summary', [LoanDashboardController::class, 'getDashboardLoanSummary'])->name('loan.summary');


    // Route::get('/index', [LoanManagementController::class, 'index'])->name('loan.index');
    // Route::get('/view-all-detail/{id}', [LoanManagementController::class, 'viewAllDetail'])->name('loan.view_all_detail');
    // Route::get('/home-loan', [LoanManagementController::class, 'home_loan'])->name('loan.home-loan');
    // Route::get('/vehicle-loan', [LoanManagementController::class, 'vehicle_loan'])->name('loan.vehicle-loan');
    // Route::get('/term-loan', [LoanManagementController::class, 'term_loan'])->name('loan.term-loan');
    // Route::get('/disbursement', [LoanManagementController::class, 'disbursement'])->name('loan.disbursement');
    // Route::get('/recovery', [LoanManagementController::class, 'recovery'])->name('loan.recovery');
    // Route::get('/settlement', [LoanManagementController::class, 'settlement'])->name('loan.settlement');

    // // Loan Dashboard
    // Route::get('/dashboard', [LoanDashboardController::class, 'dashboard'])->name('loan.dashboard');
    // Route::get('/dashboard/loan-analytics', [LoanDashboardController::class, 'getDashboardLoanAnalytics'])->name('loan.analytics');
    // Route::get('/dashboard/loan-kpi', [LoanDashboardController::class, 'getDashboardLoanKpi'])->name('loan.kpi');
    // Route::get('/dashboard/loan-summary', [LoanDashboardController::class, 'getDashboardLoanSummary'])->name('loan.summary');


    // // Loan Report
    // Route::get('/report', [LoanReportController::class, 'index'])->name('loan.report');
    // Route::get('/report/filter', [LoanReportController::class, 'getLoanFilter'])->name('loan.report.filter');
    // Route::post('/add-scheduler', [LoanReportController::class, 'addScheduler'])->name('loan.add.scheduler');
    // Route::get('/report-send/mail', [LoanReportController::class, 'sendReportMail'])->name('loan.send.report');

    // // Interest rate
    // Route::get('/interest-rate', [LoanManagementController::class, 'interest_rate'])->name('loan.interest-rate');
    // Route::get('/interest-add', [LoanInterestRateController::class, 'add'])->name('loan.interest-add');
    // Route::post('/interest-create', [LoanInterestRateController::class, 'create'])->name('loan.interest-create');
    // Route::get('/interest-edit/{id}', [LoanInterestRateController::class, 'edit'])->name('loan.interest-edit');
    // Route::post('/interest-update/{id}', [LoanInterestRateController::class, 'update'])->name('loan.interest-update');
    // Route::get('/interest-delete/{id}', [LoanInterestRateController::class, 'delete'])->name('loan.interest-delete');

    // //Home Loan
    // Route::get('/home-loan-add', [HomeLoanController::class, 'add'])->name('loan.home-loan-add');
    // Route::post('/home-loan-create-update', [HomeLoanController::class, 'create'])->name('loan.home-loan-createUpdate');
    // Route::get('/home-loan-edit/{id}', [HomeLoanController::class, 'edit'])->name('loan.home-loan-edit');
    // Route::get('/home-loan-delete/{id}', [HomeLoanController::class, 'destroy'])->name('loan.home-loan-delete');

    // // Vehicle Loan
    // Route::post('/vehicle-loan-create-update', [VehicleLoanController::class, 'create'])->name('vehicle.loan-createUpdate');
    // Route::get('/view-vehicle-detail/{id}', [VehicleLoanController::class, 'viewVehicleDetail'])->name('loan.view_vehicle_detail');
    // Route::get('/edit-vehicle-detail/{id}', [VehicleLoanController::class, 'editVehicleDetail'])->name('loan.edit_vehicle_detail');
    // Route::get('/vehicle-loan-delete/{id}', [VehicleLoanController::class, 'destroy'])->name('loan.delete_vehicle_detail');

    // // Term Loan
    // Route::post('/term-loan-create-update', [TermLoanController::class, 'create'])->name('loan.term-loan-createUpdate');
    // Route::get('/view-term-detail/{id}', [TermLoanController::class, 'viewTermDetail'])->name('loan.view_term_detail');
    // Route::get('/term-loan-edit/{id}', [TermLoanController::class, 'editTermDetail'])->name('loan.term-loan-edit');
    // Route::get('/term-loan-delete/{id}', [TermLoanController::class, 'destroy'])->name('loan.term-loan-delete');

    // Route::get('/get-cities', [LoanManagementController::class, 'getCities'])->name('loan.getCities');
    // Route::get('/get-city-by-id', [LoanManagementController::class, 'getCityByID'])->name('loan.getCityByID');

    // Route::get('/get-state', [LoanManagementController::class, 'getStates'])->name('loan.getStates');
    // Route::get('/get-state-by-id', [LoanManagementController::class, 'getStateByID'])->name('loan.getStateByID');

    // // Filter
    // Route::post('/appr-rej', [LoanManagementController::class, 'ApprReject'])->name('loan.appr_rej');

    // // Assessment
    // Route::post('/loan-assess', [LoanManagementController::class, 'loanAssessment'])->name('loan.assess');
    // Route::get('/get-assess', [LoanManagementController::class, 'getAssessment'])->name('get.loan.assess');

    // // Disbursal schedule
    // Route::post('/loan-disbursemnt', [LoanManagementController::class, 'loanDisbursemnt'])->name('loan.disbursemnt');
    // Route::get('/get-disbursemnt', [LoanManagementController::class, 'getDisbursemnt'])->name('get.loan.disbursemnt');

    // // Recovery Schedule
    // Route::post('/loan-recovery-schedule', [LoanManagementController::class, 'loanRecoverySchedule'])->name('loan.recovery-schedule');
    // Route::get('/get-recovery-schedule', [LoanManagementController::class, 'getRecoverySchedule'])->name('get.loan.recovery.schedule');

    // // Documents
    // Route::get('/get-doc', [LoanManagementController::class, 'getDoc'])->name('get.loan.docc');

    // // Disbursement
    // Route::get('/add-disbursement', [LoanManagementController::class, 'addDisbursement'])->name('loan.add-disbursement');
    // Route::post('/disbursement-add-update', [LoanManagementController::class, 'disbursementAddUpdate'])->name('loan.disbursement.add-update');
    // Route::get('/loan-get-disburs-customer', [LoanManagementController::class, 'loanGetDisbursCustomer'])->name('loan.get.disburs.customer');
    // Route::get('/loan-get-customer', [LoanManagementController::class, 'loanGetCustomer'])->name('loan.get.customer');
    // Route::post('/dis-appr-rej', [LoanManagementController::class, 'DisApprReject'])->name('loan.dis_appr_rej');

    // // Recovery
    // Route::get('/add-recovery', [LoanManagementController::class, 'addRecovery'])->name('loan.add-recovery');
    // Route::post('/recovery-add-update', [LoanManagementController::class, 'recoveryAddUpdate'])->name('loan.recovery.add-update');
    // Route::post('/recovery-appr-rej', [LoanManagementController::class, 'RecoveryApprReject'])->name('loan.recovery_appr_rej');

    // // Settlement
    // Route::get('/add-settlement', [LoanManagementController::class, 'addSettlement'])->name('loan.add-settlement');
    // Route::post('/settlement-add-update', [LoanManagementController::class, 'settlementAddUpdate'])->name('loan.settlement.add-update');
    // Route::post('/settle-appr-rej', [LoanManagementController::class, 'SettleApprReject'])->name('loan.settle_appr_rej');

    // // get pending disbursals
    // Route::get('/get-pending-disbursal', [LoanManagementController::class, 'getPendingDisbursal'])->name('loan.get-pending-disbursal');
    // Route::get('/set-pending-status', [LoanManagementController::class, 'setPendingStatus'])->name('loan.set_pending_status');

    // //get series
    // Route::get('/get-series', [LoanManagementController::class, 'getSeries'])->name('loan.get_series');

    // Route::get('/fetch-disbursement-approve', [LoanManagementController::class, 'fetchDisbursementApprove'])->name('loan.fetch-disbursement-approve');
    // Route::get('/fetch-recovery-approve', [LoanManagementController::class, 'fetchRecoveryApprove'])->name('loan.fetch-recovery-approve');
    // Route::get('/fetch-settle-approve', [LoanManagementController::class, 'fetchSettleApprove'])->name('loan.fetch-settle-approve');


    // Route::get('/get-loan-cibil', [LoanManagementController::class, 'getLoanCibil'])->name('get.loan.cibil');
    // Route::get('/get-principal-interest', [LoanManagementController::class, 'getPrincipalInterest'])->name('loan.get.PrincipalInterest');

    // Route::get('/get-loan-request/{book_id}', [LoanManagementController::class, 'getLoanRequests'])->name('get_loan_request');
    Route::prefix('einvoice')->group(function () {
        Route::post('/generate', [EInvoiceServiceController::class, 'generateInvoice']);
        Route::get('/generate-pdf', [EinvoicePdfController::class, 'generateInvoiceQrPdf']);
    });

    Route::prefix('reports')->controller(TransactionReportController::class)->group(function () {
        Route::get('/{serviceAlias}', 'index')->name('transactions.report');
        Route::post('/send-email', 'emailReport')->name('transactions.report.email');
    });
    Route::prefix('packing-lists')->controller(PackingListController::class)->name('packingList.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/store', 'store')->name('store');
        Route::post('/revoke', 'revoke')->name('revoke');
        Route::get('/pullable-docs', 'getPullableDocuments')->name('pullable.docs');
        Route::post('/pullable-doc-items', 'getDocumentItems')->name('pullable.doc.items');
        Route::post('/item-details', 'getItemDetails')->name('item.details');
    });
    Route::prefix('request-for-quotation')->controller(ErpRFQController::class)->name('rfq.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/store', 'store')->name('store');
        Route::post('/revoke', 'revoke')->name('revoke');
        Route::post('/mail', 'mail')->name('mail');
        Route::get('/generate-pdf/{id}', 'generatePdf')->name('generate-pdf');
        Route::get('/process-item', 'processItems')->name('process.items');
        Route::post('/get-item', 'getPiItemForPulling')->name('get.items');
        Route::get('/serach-items', 'serachItems')->name('search.items');
        Route::get('/report', 'report')->name('report');
    });

    Route::prefix('purchase-quotation')->controller(ErpPqController::class)->name('pq.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/store', 'store')->name('store');
        Route::post('/revoke', 'revoke')->name('revoke');
        Route::post('/mail', 'mail')->name('mail');
        Route::get('/generate-pdf/{id}', 'generatePdf')->name('generate-pdf');
        Route::get('/process-item', 'processItems')->name('process.items');
        Route::post('/get-item', 'getRfqItemForPulling')->name('get.items');
        Route::get('/serach-items', 'serachItems')->name('search.items');
        Route::get('/vendors/address/{id}', 'getVendorAddresses')->name('vendor.addresses');
    });
    Route::prefix('purchase-quotation-comparison')->controller(ErpPqcController::class)->name('pqc.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/store', 'store')->name('store');
        Route::post('/revoke', 'revoke')->name('revoke');
        Route::post('/mail', 'mail')->name('mail');
        Route::get('/process-item', 'processItems')->name('process.items');
        Route::get('/serach-items', 'serachItems')->name('search.items');
        Route::post('/get-item', 'getRfqItemForPulling')->name('get.items');
    });

    Route::prefix('pickup-dropoff-schedule')->controller(ErpPdsController::class)->name('pds.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/store', 'store')->name('store');
        Route::post('/mail', 'mail')->name('mail');
        Route::get('/process-item', 'processItems')->name('process.items');
        Route::post('/get-item', 'getRfqItemForPulling')->name('get.items');
        Route::post('/revoke', 'revoke')->name('revoke');
        Route::get('/search-items', 'searchItems')->name('search.items');
        Route::post('/generate-pdf', 'generatePdf')->name('generate-pdf');
    });

    Route::prefix('sales-order/import')->controller(SaleOrderImportController::class)->name('salesOrder.')->group(function () {
        Route::get('/{version}', 'import')->name('import.index');
        Route::post('save/{version}', 'importSave')->name('import.save');
        Route::post('store/{version}', 'bulkUploadOrders')->name('import.store');
        Route::post('item/save/', 'importSaveItem')->name('import.item.save');
        Route::post('/item/store', 'bulkUploadItems')->name('import.item.store');
    });
    Route::prefix('purchase-order/import')->controller(PurchaseOrderImportController::class)->name('purchaseOrder.')->group(function () {
        Route::get('/', 'import')->name('import.index');
        Route::post('save', 'importSave')->name('import.save');
        Route::post('store', 'bulkUploadOrders')->name('import.store');
        Route::post('item/save', 'importSaveItem')->name('import.item.save');
        Route::post('/item/store', 'bulkUploadItems')->name('import.item.store');
    });
    Route::get('/import/{alias}', [GenericImportController::class, 'import'])->name('generic.import.page');
    Route::post('/import-save/{alias}', [GenericImportController::class, 'importSave'])->name('generic.import.save');
    Route::get('/import-sample/{alias}', [GenericImportController::class, 'downloadSample'])->name('generic.import.sample.download');
    Route::post('/import-errors/{alias}', [GenericImportController::class, 'downloadInvalid'])->name('generic.import.invalid.download');



    Route::prefix('equipment')->group(function () {

        Route::get('/', [ErpEquipmentController::class, 'index'])->name('equipment.index');
        Route::get('/create', [ErpEquipmentController::class, 'create'])->name('equipment.create');
        Route::post('/store', [ErpEquipmentController::class, 'store'])->name('equipment.store');
        Route::get('/edit/{id}', [ErpEquipmentController::class, 'edit'])->name('equipment.edit');
        Route::post('/update/{id}', [ErpEquipmentController::class, 'update'])->name('equipment.update');
        Route::post('/approve', [ErpEquipmentController::class, 'documentApproval'])->name('equipment.approval');
        Route::get('amend/{id}', [ErpEquipmentController::class, 'amendment'])->name('equipment.amendment');
        Route::post('/get-asset-codes-by-book', [ErpEquipmentController::class, 'getFixedAssetCodesByBookId'])->name('equipment.get-asset-codes-by-book');
        Route::post('/get-checklist-details', [ErpEquipmentController::class, 'getChecklistDetails'])->name('equipment.get-checklist-details');
        Route::post('/search-checklists', [ErpEquipmentController::class, 'searchChecklists'])->name('equipment.search-checklists');
    });

    Route::prefix('maintenance')->group(function () {

        Route::get('/', [ErpMaintananceController::class, 'index'])->name('maintenance.index');
        Route::get('/create', [ErpMaintananceController::class, 'create'])->name('maintenance.create');
        Route::post('/store', [ErpMaintananceController::class, 'store'])->name('maintenance.store');
        Route::get('/edit/{id}', [ErpMaintananceController::class, 'edit'])->name('maintenance.edit');
        Route::post('/update/{id}', [ErpMaintananceController::class, 'update'])->name('maintenance.update');
        Route::post('/approve', [ErpMaintananceController::class, 'documentApproval'])->name('maintenance.approval');
        Route::get('amend/{id}', [ErpMaintananceController::class, 'amendment'])->name('maintenance.amendment');
    });

    Route::prefix('defect-tracker')->group(function () {
        Route::get('/', [DefectTrackerController::class, 'index'])->name('defect-tracker.index');
        Route::post('/update/{id}', [DefectTrackerController::class, 'update'])->name('defect-tracker.update');
    });

    // generate IRN
    Route::post('/validate-gst', [GstValidationController::class, 'validateGstNumber']);




    //For testing purpose -> Stock reservation case , please ignore, will remove
    // Route::get('issue-receive-pl', function () {
    //     DB::beginTransaction();
    //     $status = StockReservation::settlementOfReservedStocks(ConstantHelper::PL_SERVICE_ALIAS, 201, 11, 2);
    //     if ($status['status'] == 'success') {
    //         DB::commit();
    //         dd("DONE");
    //     } else {
    //         DB::rollBack();
    //         dd($status['message']);
    //     }
    // });
});
