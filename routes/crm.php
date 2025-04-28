<?php

// use App\Http\Controllers\CRM\NotesController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| CRM Routes
|--------------------------------------------------------------------------
|
| Here is where you can register crm routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group with "crm" prefix. Make something great!
|
*/

Route::middleware(['user.auth'])->group(function () {
    Route::get('/home', 'IndexController@index')->name('crm.home');

    // Notes route
    Route::controller(NotesController::class)->prefix('notes')->group(function () {
        Route::get('/', 'index')->name('notes.index');
        Route::get('/prospects', 'prospects')->name('notes.prospects');
        Route::get('/create', 'create')->name('notes.create');
        Route::post('/render-diaries', 'renderDiaries')->name('notes.render-diaries');
        Route::get('/get-customers', 'getCustomers')->name('customers.get');
        Route::get('/get-customer/{erpCustomer}', 'getCustomer')->name('get-customer');
    });

    // Orders
    Route::controller(CustomersController::class)->prefix('customers')->group(function () {
        Route::get('/dashboard', 'dashbaord')->name('customers.dashboard');
        Route::get('/view/{customerCode}', 'view')->name('customers.view');
        Route::get('/', 'index')->name('customers.index');
        Route::get('/orders/{customerCode}', 'getOrders')->name('customers.orders');
        Route::get('/order/csv', 'orderCsv')->name('customers.order-csv');
        Route::get('/order-detail/csv/{customerCode}', 'orderDetailCsv')->name('customers.order-detail.csv');
        
    });
    
    Route::controller(ProspectsController::class)->prefix('prospects')->group(function () {
        Route::get('/dashboard', 'dashboard')->name('prospects.dashboard');
        Route::get('/', 'index')->name('prospects.index');
        Route::get('/csv', 'prospectsCsv')->name('prospects.csv');
        Route::get('/{customerCode}', 'view')->name('prospects.view');
    });

});

Route::group(['middleware' => ['user.auth', 'apiresponse']], function () {
    Route::controller(IndexController::class)->group(function () {
        Route::get('/get-states/{country}', 'getStates')->name('crm.get-states');
        Route::get('/get-cities/{state}', 'getCities')->name('crm.get-cities');
        Route::get('/get-countries-states/{type}', 'getCountriesStates')->name('crm.get-countries-states');
    });

    // Notes route
    Route::controller(NotesController::class)->prefix('notes')->group(function () {
        Route::post('/store', 'store')->name('notes.store');
        Route::post('/store-answer', 'storeAnswer')->name('notes.store-answer');
        Route::post('/add-lead-contacts', 'addLeadContacts')->name('notes.add-lead-contacts');
        Route::delete('/remove-lead-contacts/{id}', 'removeLeadContact')->name('notes.remove-lead-contact');
    });

    Route::controller(ProspectsController::class)->prefix('prospects')->group(function () {
        Route::post('/supply-split/store', 'supplySplit')->name('prospects.supply-split.store');
        Route::post('/update-status/{id}', 'updateLeadStatus')->name('prospects.update-status');
        Route::delete('/supply-split/remove/{id}', 'removeSupplySplit')->name('prospects.supply-split.remove');
    });
});


