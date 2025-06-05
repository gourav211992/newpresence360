<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Bank Routes
|--------------------------------------------------------------------------
|
| Here is where you can register bank routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group with "recruitment" prefix. Make something great!
|
*/

Route::middleware(['user.auth'])->group(function () {
    Route::controller(LedgerController::class)->prefix('ledgers')->group(function () {
        Route::get('/', 'index')->name('bank.ledgers.index');
    });
    
    Route::controller(StatementController::class)->prefix('statements')->group(function () {
        Route::get('/upload/{id}', 'upload')->name('bank.statements.upload');
        Route::get('/match-entries/{id}', 'matchEntries')->name('bank.statements.match-entries');
        Route::get('/not-match-entries/{id}', 'notMatchEntries')->name('bank.statements.not-match-entries');
        Route::get('/export/{id}', 'export')->name('bank.statements.export');
    });
    
    Route::controller(BankReconciliationController::class)->prefix('reconcile')->group(function () {
        Route::get('/{id}', 'index')->name('bank.reconcile.index');
    });

    Route::group(['middleware' => ['apiresponse']], function () {
        Route::controller(StatementController::class)->prefix('statements')->group(function () {
            Route::post('/save/{id}', 'save')->name('bank.statements.save');
        });

        Route::controller(BankReconciliationController::class)->prefix('reconcile')->group(function () {
            Route::post('/save-date', 'storeBankDates')->name('bank.reconcile.save-date');
        });
    });
});