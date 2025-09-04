<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Kaizen\{IndexController,KaizenController,DesignationController,ImprovementController};

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
    Route::prefix('dashboard')->controller(IndexController::class)->group(function () {
        Route::get('/', 'index')->name('kaizen.dashboard');
        Route::get('get/', 'getDashboard')->name('kaizen.get-dashboard');
    });

    Route::controller(KaizenController::class)->group(function () {
        Route::get('/', 'index')->name('kaizen.index');
        Route::get('/create', 'create')->name('kaizen.create');
        Route::get('/edit/{id}', 'edit')->name('kaizen.edit');
        Route::get('/view/{id}', 'view')->name('kaizen.view');
        Route::get('/download-pdf/{id}', 'pdfView')->name('kaizen.pdf-view');
        Route::get('/export', 'exportKaizens')->name('kaizens.export');
    });

    Route::prefix('designation')->controller(DesignationController::class)->group(function () {
        Route::get('/', 'index')->name('designation.index');
        Route::put('/{id}', 'update')->name('designation.update');
    });
    Route::prefix('improvement-masters')->controller(ImprovementController::class)->group(function () {
        Route::get('/', 'index')->name('improvement-masters.index');
        Route::post('/', 'store')->name('improvement-masters.store');
        Route::get('/download-pdf', 'pdfView')->name('improvement.pdf-download');
        Route::put('/{id}', 'update')->name('improvement-masters.update');
        Route::delete('/{id}', 'destroy')->name('improvement-masters.destroy');
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
            Route::post('/update-status/{id}', 'updateStatus')->name('kaizen.update-status');
        });
    });

});