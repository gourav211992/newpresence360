<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Kaizen Routes
|--------------------------------------------------------------------------
|
| Here is where you can register kaizen routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group with "kaizen" prefix. Make something great!
|
*/

Route::middleware(['user.auth'])->group(function () {

    // For Web Routes
    Route::controller(IndexController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('kaizen.dashboard');
    });

    Route::controller(KaizenController::class)->group(function () {
        Route::get('/', 'index')->name('kaizen.index');
        Route::get('/create', 'create')->name('kaizen.create');
        Route::get('/edit/{id}', 'edit')->name('kaizen.edit');
        Route::get('/download-pdf/{id}', 'pdfView')->name('kaizen.pdf-view');
    });

    // For Api Routes
    Route::group(['middleware' => ['apiresponse']], function () {
        Route::controller(IndexController::class)->group(function () {
            Route::get('/fetch-employees', 'fetchEmployees')->name('kaizen.fetch-employees');
        });

        Route::controller(KaizenController::class)->group(function () {
            Route::post('/store', 'store')->name('kaizen.store');
            Route::put('/update/{id}', 'update')->name('kaizen.update');
            Route::delete('/remove-attachment/{id}', 'removeAttachment')->name('kaizen.remove-attachment');
            Route::delete('/destroy/{id}', 'destroy')->name('kaizen.destroy');
        });
    });

});