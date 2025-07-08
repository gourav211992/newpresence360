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
    Route::controller(UnloadingTaskController::class)->group(function () {
        Route::get('/unloading-tasks', 'index')->name('whm.unloading-tasks');
        Route::get('/pending-tasks', 'pendingTasks')->name('whm.pending-tasks');
        Route::post('/save-as-draft', 'saveAsDraft')->name('whm.save-as-draft');
        Route::get('/scanned-packets', 'scannedPackets')->name('whm.scanned-packets');
        Route::post('/close-job', 'closeJob')->name('whm.close-job');
        Route::post('/update-status/packet', 'updateStatus')->name('whm.update-status');

    });
    
    Route::get('/stores', 'IndexController@stores')->name('whm.stores');
    Route::get('/sub-stores', 'IndexController@subStores')->name('whm.sub-stores');
});


