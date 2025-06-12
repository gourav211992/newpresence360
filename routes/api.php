<?php

use App\Http\Controllers\API\BookController;
use App\Http\Controllers\API\TransporterRequest\TransporterRequestApiController;
use App\Http\Controllers\CRM\API\ServiceController;
use App\Http\Controllers\PutAwayController;
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

Route::middleware(['sso-api', 'apiresponse'])->get('/user', function (Request $request) {
    return [
        'data' => $request->user(),
        'message' => 'success',
    ];
});

Route::group(['middleware' => ['apiresponse']], function () {
    Route::controller(ServiceController::class)->prefix('v1/crm')->group(function () {
        Route::post('/sync-order-summary', 'syncOrderSummmary')->name('api.crm.sync-order-summary');
        Route::post('/sync-customer-target', 'syncCustomerTarget')->name('api.crm.sync-customer-target');
        Route::post('/sync-sales-order-summary', 'syncSalesOrderSummary')->name('api.crm.sync-sales-order-summary');
    });
});

Route::group(['middleware' => ['apiresponse']], function () {
    Route::controller(TransporterRequestApiController::class)->group(function(){
        Route::post('transporter-requests/create','create')->name('create');
        Route::post('transporter-requests/get_request_list','get_request_list')->name('get_request_list');
        Route::post('transporter-requests/get_bid_details','get_bid_details')->name('get_bid_details');
        Route::post('transporter-requests/shortlist','shortlist')->name('shortlist');
        Route::post('transporter-requests/close','close')->name('close');
        // Route::post('transporter-requests/reopen','reopen')->name('reopen');
    });
    Route::group(['middleware' => ['sso-api', 'apiresponse']], function () {
        Route::controller(BookController::class)->prefix('book')->group(function(){
            Route::get('get-document-number','generateDocumentNumber')->name('book.get.docNo');
        });
        Route::controller(PutAwayController::class)->prefix('put-away')->group(function(){
            Route::post('location-listing', 'locationListing')->name('get.locations');
            Route::post('sub-location-listing', 'subLocationListing')->name('get.sub-locations');
            Route::post('mrn-listing', 'mrnListing')->name('get.mrn-listing');
        });
    });
});
