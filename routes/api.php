<?php

use App\Http\Controllers\API\TransporterRequest\TransporterRequestApiController;
use App\Http\Controllers\CRM\API\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['apiresponse']], function () {
    Route::controller(ServiceController::class)->prefix('v1/crm')->group(function () {
        Route::post('/sync-order-summary', 'syncOrderSummmary')->name('api.crm.sync-order-summary');
        Route::post('/sync-customer-target', 'syncCustomerTarget')->name('api.crm.sync-customer-target');
        Route::post('/sync-sales-order-summary', 'syncSalesOrderSummary')->name('api.crm.sync-sales-order-summary');
    });
});

Route::group(['middleware' => ['auth:sanctum', 'apiresponse', 'auth:api']], function () {
    Route::controller(TransporterRequestApiController::class)->group(function(){
        Route::post('transporter-requests/create','create')->name('create');
        Route::post('transporter-requests/get_request_list','get_request_list')->name('get_request_list');
        Route::post('transporter-requests/get_bid_details','get_bid_details')->name('get_bid_details');
        Route::post('transporter-requests/shortlist','shortlist')->name('shortlist');
        Route::post('transporter-requests/close','close')->name('close');
        // Route::post('transporter-requests/reopen','reopen')->name('reopen');
    });
});