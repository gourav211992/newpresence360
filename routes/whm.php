<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Ware House Management Routes
|--------------------------------------------------------------------------
|
| Here is where you can register whm routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group with "whm" prefix. Make something great!
|
*/
// Route::controller(IndexController::class)->group(function () {
//     Route::get('/dashboard', 'userDashboard')->name('whm.user-dashboard');
// });


Route::controller(PrintQrController::class)->group(function () {
    Route::get('/get-qrcodes', 'getQrcodes')->name('whm.download-qrs')->middleware('sso-api');
});

Route::group(['middleware' => ['sso-api', 'apiresponse']], function () {
    Route::controller(IndexController::class)->group(function () {
        Route::get('/stores', 'stores')->name('whm.stores');
        Route::get('/sub-stores', 'subStores')->name('whm.sub-stores');
        Route::get('/items', 'items')->name('whm.items');
        Route::get('/items-attributes', 'getItemAttributes')->name('whm.items-attributes');
        Route::get('/get-structure-mapping', 'getStructureMapping')->name('whm.get-structure-mapping');
        Route::get('/get-jobs', 'getJobs')->name('whm.get-jobs');//Testing
        Route::get('/get-unique-codes', 'getUniqueCodes')->name('whm.get-unique-codes');//Testing
        Route::get('/get-item-storage', 'getItemStorage')->name('whm.get-item-storage');//Testing
        Route::get('/get-configuration', 'getConfiguration')->name('whm.get-configuration');//Testing
        Route::get('/storage-points', 'storagePoints')->name('whm.storage-points');
        Route::get('/storage-point/detail', 'storagePointDetail')->name('whm.storage-point.detail');
        Route::get('/track-packet', 'trackPacket')->name('whm.track-packet');
        Route::get('/storage-point/packets', 'getStoragePointPackets')->name('whm.storage-packets');
        Route::get('/dashboard', 'userDashboard')->name('whm.user-dashboard');
    });

    Route::controller(UnloadingTaskController::class)->group(function () {
        Route::get('/unloading-tasks', 'index')->name('whm.unloading-tasks');
        Route::get('/pending-tasks', 'pendingTasks')->name('whm.pending-tasks');
        Route::post('/save-as-draft', 'saveAsDraft')->name('whm.save-as-draft');
        Route::get('/scanned-packets', 'scannedPackets')->name('whm.scanned-packets');
        Route::post('/close-job', 'closeJob')->name('whm.close-job');
        Route::post('/update-status/packet', 'updateStatus')->name('whm.update-status');

    });

    Route::controller(PutawayTaskController::class)->group(function () {
        Route::get('/putaway/tasks', 'index')->name('whm.putaway.tasks');
        Route::get('/putaway/items', 'items')->name('whm.putaway.items');
        Route::get('/putaway/pending-tasks', 'pendingTasks')->name('whm.putaway.pending-tasks');
        Route::get('/putaway/item-detail', 'itemDetail')->name('whm.putaway.item-detail');
        Route::post('/putaway/save-as-draft', 'saveAsDraft')->name('whm.putaway.save-as-draft');
        Route::post('/putaway/update-status', 'updateStatus')->name('whm.putaway.update-status');
        Route::post('/putaway/close-job', 'closeJob')->name('whm.putaway.close-job');
        Route::get('/putaway/scanned-packets', 'scannedItemQrs')->name('whm.putaway.scanned-packets');

    });

    Route::controller(PicklistTaskController::class)->group(function () {
        Route::get('/picklist/tasks', 'index')->name('whm.picklist.tasks');
        Route::get('/picklist/items', 'items')->name('whm.picklist.items');
        Route::get('/picklist/item-detail', 'itemDetail')->name('whm.picklist.item-detail');
        Route::get('/picklist/pending-tasks', 'pendingTasks')->name('whm.picklist.pending-tasks');
        Route::post('/picklist/save-as-draft', 'saveAsDraft')->name('whm.picklist.save-as-draft');
        Route::post('/picklist/update-status', 'updateStatus')->name('whm.picklist.update-status');
        Route::post('/picklist/close-job', 'closeJob')->name('whm.picklist.close-job');
        Route::post('/picklist/san-storage', 'scanStorage')->name('whm.picklist.san-storage');
        Route::get('/picklist/scanned-packets', 'scannedItemQrs')->name('whm.picklist.scanned-packets');
    });

    Route::controller(DispatchController::class)->group(function () {
        Route::get('/dispatch/tasks', 'index')->name('whm.dispatch.unloading-tasks');
        Route::get('/dispatch/items', 'items')->name('whm.dispatch.items');
        Route::get('/dispatch/pending-tasks', 'pendingTasks')->name('whm.dispatch.pending-tasks');
        Route::post('/dispatch/save-as-draft', 'saveAsDraft')->name('whm.dispatch.save-as-draft');
        Route::get('/dispatch/scanned-packets', 'scannedPackets')->name('whm.dispatch.scanned-packets');
        Route::post('/dispatch/close-job', 'closeJob')->name('whm.dispatch.close-job');
        Route::post('/dispatch/remove-packet', 'removePacket')->name('whm.dispatch.remove-packet');
    });

    Route::controller(BinTransferController::class)->group(function () {
        Route::get('/bin/items', 'index')->name('whm.bin.items');
        Route::post('/bin/validate-qr', 'validateQr')->name('whm.bin.validate-qr');
        Route::post('/bin/transfer', 'binTransfer')->name('whm.bin.transfer');
        Route::post('/bin/scan-packets', 'scanPackets')->name('whm.bin.scan-packets');
        Route::get('/bin/validate-storage-point', 'validatePoint')->name('whm.bin.validate-storage-point');
    });

    Route::controller(StockLookoutController::class)->group(function () {
        Route::post('/stock', 'index')->name('whm.stock.index');
        Route::get('/stock/item', 'item')->name('whm.stock.item');
        // Route::get('/stock/get-filtered-items', 'getFilteredItems')->name('whm.stock.get-filtered-items');
        Route::post('/stock/apply-filter', 'applyFilter')->name('whm.stock.apply-filter');
    });

    Route::controller(RgrJobController::class)->group(function () {
        Route::get('get-rgr/{store_id}', 'getRgr')->name('rgr.get-by-store');           
        Route::get('get-rgr-detail/{job_id}', 'getRgrDetails')->name('rgr.get-rgr-detail');      
        Route::get('get-defect-severity', 'getDefectSeverity')->name('defect.severity.list'); 
        Route::get('damage-nature-options', 'getDamageNatureOptions')->name('damage.nature.list'); 
        Route::get('defect-types/{severity}/{itemId}', 'getDefectTypes')->name('defect.types.by-severity'); 
        Route::get('/get-items', 'getItems')->name('items.goods.list');                   
        Route::get('/get-items/{itemId}/attributes', 'getAttributesByItemId')->name('items.attributes.get'); 
        Route::get('/scan-item/{item_uid}', 'scanItem')->name('items.scan');                
        Route::get('segregation/{uniqueItemId}', 'getSegregationByUniqueItemId')->name('segregation.details');
        Route::get('jobs/{jobId}/item-status','getJobItemStatus')->name('jobs.item-status');      
        Route::post('/segregate-item', 'createSegregation')->name('segregation.create-or-update'); 
        Route::post('/unique-items/store', 'storeUniqueItem')->name('unique-items.create');    
        Route::delete('scanned-item/{uniqueItemId}', 'deleteScannedItem')->name('unique-items.delete');
    });

    Route::controller(RepairOrderJobController::class)->group(function () {
        Route::get('get-repair-orders/{store_id}', 'getRepairOrder')->name('repair.get-by-store');
        Route::get('get-repair-orders-detail/{job_id}', 'getRepairOrderDetails')->name('repair.get-rgr-detail');     
        Route::get('/get-service-items', 'getServiceItems')->name('service.items.list');
        Route::get('/get-repair-action','getRepairAction')->name('get.repair.action');
        Route::get('/get-vendors', 'getVendors')->name('vendors.get');
        Route::get('get-repair-defects-count/{store_id}', 'getDefectStatusCounts')->name('repair-orders.defects.counts');
        Route::post('/repair-action', 'repairAction')->name('repair.action');
    });
     
});


