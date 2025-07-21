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

Route::group(['middleware' => ['sso-api', 'apiresponse']], function () {
    Route::controller(IndexController::class)->group(function () {
        Route::get('/stores', 'stores')->name('whm.stores');
        Route::get('/get-jobs', 'getJobs')->name('whm.get-jobs');//Testing
        Route::get('/get-unique-codes', 'getUniqueCodes')->name('whm.get-unique-codes');//Testing
        Route::get('/get-configuration', 'getConfiguration')->name('whm.get-configuration');//Testing
        Route::get('/sub-stores', 'subStores')->name('whm.sub-stores');
        Route::get('/storage-points', 'storagePoints')->name('whm.storage-points');
        Route::get('/storage-point/detail', 'storagePointDetail')->name('whm.storage-point.detail');
        Route::get('/track-packet', 'trackPacket')->name('whm.track-packet');
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
        // Route::get('/putaway/scanned-packets', 'scannedPackets')->name('whm.putaway.scanned-packets');
        Route::post('/putaway/save-as-draft', 'saveAsDraft')->name('whm.putaway.save-as-draft');
        Route::post('/putaway/update-status', 'updateStatus')->name('whm.putaway.update-status');

    });

    Route::controller(PicklistTaskController::class)->group(function () {
        Route::get('/picklist/tasks', 'index')->name('whm.picklist.tasks');
        Route::get('/picklist/items', 'items')->name('whm.picklist.items');
        Route::get('/picklist/item-detail', 'itemDetail')->name('whm.picklist.item-detail');
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
    
    
});


