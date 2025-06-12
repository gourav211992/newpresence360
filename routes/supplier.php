<?php

use App\Http\Controllers\Supplier\HomeController;
use Illuminate\Support\Facades\Route;

Route::name('supplier.')
        ->middleware(['user.auth'])
        ->controller(HomeController::class)
        ->group(function(){
            Route::get('/', 'index')->name('dashboard');
            Route::get('on-change', 'onChangeVendor')->name('change.vendor');
        });

Route::name('supplier.po.')
    ->middleware(['user.auth'])
    ->prefix('po')
    ->controller(PoController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/edit/{id}', 'edit')->name('edit');
        /*Shobhit Code*/
        Route::get('get-item-attribute', 'getItemAttribute')->name('item.attr');
        Route::get('/tax-calculation', 'taxCalculation')->name('tax.calculation');
        Route::get('/get-itemdetail', 'getItemDetail')->name('get.itemdetail');
        Route::get('/{id}/pdf', 'generatePdf')->name('generate-pdf');

    });

Route::name('supplier.invoice.')
    ->middleware(['user.auth'])
    ->prefix('invoice')
    ->controller(SiController::class)
        ->group(function () {
            Route::get('revoke-document','revokeDocument')->name('revoke.document');
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            /*Shobhit Code*/
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
            Route::get('process-pi-item', 'processPiItem')->name('process.pi-item');
        });

    Route::prefix('transporter-bid')
    ->name('supplier.transporter.')
    ->middleware(['user.auth'])
        ->controller(ErpTransportersController::class)
        ->group(function(){
            Route::get('/{vendor_id?}', 'index')->name('index');
            Route::post('/submit_bid', 'submit_bid')->name('submit_bid');
            Route::post('/change_bid', 'change_bid')->name('change_bid');
            Route::post('/submit_vehicle', 'submit_vehicle')->name('submit_vehicle');
            Route::post('/cancel_trip', 'cancel_trip')->name('cancel_trip');
            Route::post('/get-locations', 'get_locations')->name('get-locations');
    });