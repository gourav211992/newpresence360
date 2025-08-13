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

Route::group(['middleware' => ['sso-api', 'apiresponse']], function () {
    Route::controller(IndexController::class)->group(function () {
        Route::get('/stores', 'stores')->name('whm.stores');
        Route::get('/sub-stores', 'subStores')->name('whm.sub-stores');
        Route::get('/items', 'items')->name('whm.items');
        Route::get('/items-attributes', 'getItemAttributes')->name('whm.items-attributes');
        Route::get('/get-structure-mapping', 'getStructureMapping')->name('whm.get-structure-mapping');
        Route::get('/get-jobs', 'getJobs')->name('whm.get-jobs');//Testing
        Route::get('/get-unique-codes', 'getUniqueCodes')->name('whm.get-unique-codes');//Testing
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

    });

    Route::controller(PicklistTaskController::class)->group(function () {
        Route::get('/picklist/tasks', 'index')->name('whm.picklist.tasks');
        Route::get('/picklist/items', 'items')->name('whm.picklist.items');
        Route::get('/picklist/item-detail', 'itemDetail')->name('whm.picklist.item-detail');
        Route::get('/picklist/pending-tasks', 'pendingTasks')->name('whm.picklist.pending-tasks');
        Route::post('/picklist/save-as-draft', 'saveAsDraft')->name('whm.picklist.save-as-draft');
        Route::post('/picklist/update-status', 'updateStatus')->name('whm.picklist.update-status');
        Route::post('/picklist/close-job', 'closeJob')->name('whm.picklist.close-job');
    });

    Route::controller(DispatchController::class)->group(function () {
        Route::get('/dispatch/tasks', 'index')->name('whm.dispatch.unloading-tasks');
        Route::get('/dispatch/pending-tasks', 'pendingTasks')->name('whm.dispatch.pending-tasks');
        Route::post('/dispatch/save-as-draft', 'saveAsDraft')->name('whm.dispatch.save-as-draft');
        Route::get('/dispatch/scanned-packets', 'scannedPackets')->name('whm.dispatch.scanned-packets');
        Route::post('/dispatch/close-job', 'closeJob')->name('whm.dispatch.close-job');
        Route::post('/dispatch/update-status/packet', 'updateStatus')->name('whm.dispatch.update-status');
    });

    Route::controller(BinTransferController::class)->group(function () {
        Route::get('/bin/items', 'index')->name('whm.bin.items');
        Route::post('/bin/transfer', 'binTransfer')->name('whm.bin.transfer');
        Route::post('/bin/scan-packets', 'scanPackets')->name('whm.bin.scan-packets');
    });

    Route::controller(StockLookoutController::class)->group(function () {
        Route::get('/stock', 'index')->name('whm.stock.index');
        Route::get('/stock/item', 'item')->name('whm.stock.item');
        Route::get('/stock/get-filtered-items', 'getFilteredItems')->name('whm.stock.get-filtered-items');
    });
    
    
});


