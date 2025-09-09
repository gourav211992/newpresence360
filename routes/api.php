<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PutAwayController;
use App\Http\Controllers\API\BookController;
use App\Http\Controllers\CRM\API\ServiceController;
use App\Http\Controllers\API\Integration\FurlencoController;
use App\Http\Controllers\API\TransporterRequest\TransporterRequestApiController;

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

// Define a group of routes that use the 'apiresponse' middleware
Route::group(['middleware' => ['apiresponse']], function () {

    /**
     * CRM Service Routes
     * Prefix: v1/crm
     * Controller: ServiceController
     */
    Route::controller(ServiceController::class)->prefix('v1/crm')->group(function () {
        Route::post('/sync-order-summary', 'syncOrderSummmary')->name('api.crm.sync-order-summary'); // Sync order summary data
        Route::post('/sync-customer-target', 'syncCustomerTarget')->name('api.crm.sync-customer-target'); // Sync customer target data
        Route::post('/sync-sales-order-summary', 'syncSalesOrderSummary')->name('api.crm.sync-sales-order-summary'); // Sync sales order summary data
    });

    /**
     * Transporter Requests API
     * Controller: TransporterRequestApiController
     */
    Route::controller(TransporterRequestApiController::class)->group(function(){
        Route::post('transporter-requests/create','create')->name('create'); // Create a new transporter request
        Route::post('transporter-requests/get_request_list','get_request_list')->name('get_request_list'); // Fetch list of transporter requests
        Route::post('transporter-requests/get_bid_details','get_bid_details')->name('get_bid_details'); // Get bid details for a request
        Route::post('transporter-requests/shortlist','shortlist')->name('shortlist'); // Shortlist transporter bids
        Route::post('transporter-requests/close','close')->name('close'); // Close a transporter request
        // Route::post('transporter-requests/reopen','reopen')->name('reopen'); // (Optional) Reopen a closed request
    });

    /**
     * Routes protected with SSO Auth Middleware
     */
    Route::group(['middleware' => ['sso-api']], function () {

        // Get the authenticated user details
        Route::get('/user', function (Request $request) {
            return [
                'data' => $request->user(),
                'message' => 'success',
            ];
        });


        /**
         * Furlenco Integration related APIs
         * Controller: FurlencoController
        */

        // Create or Update consignees
        Route::post('/consignees', [FurlencoController::class, 'consigneeStoreOrUpdate']);

        // Trip/Sales Order Creation
        Route::post('/create/sale-orders', [FurlencoController::class, 'createSaleOrders']);

        /**
         * Book Module Routes
         * Prefix: book
         * Controller: BookController
         */
        Route::controller(BookController::class)->prefix('book')->group(function(){
            Route::get('get-document-number','generateDocumentNumber')->name('book.get.docNo'); // Generate document number
        });

        /**
         * Put-Away Module Routes
         * Prefix: put-away
         * Controller: PutAwayController
         */
        Route::controller(PutAwayController::class)->prefix('put-away')->group(function(){
            Route::post('location-listing', 'locationListing')->name('get.locations'); // Get list of available locations
            Route::post('sub-location-listing', 'subLocationListing')->name('get.sub-locations'); // Get sub-location listing
            Route::post('mrn-listing', 'mrnListing')->name('get.mrn-listing'); // Get MRN listing (Material Receipt Notes)
        });

    });

});

