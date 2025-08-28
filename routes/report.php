<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report\ProductionReportController;


Route::middleware(['user.auth'])->group(function () {
    // Report for BOM vs Actual
    Route::get('/bom-vs-actual', [ProductionReportController::class, 'bomVsActualReport'])->name('bomVsActual.report');
    Route::get('/bom-vs-actual/download', [ProductionReportController::class, 'downloadBomVsActualWithOutfile'])->name('bomVsActual.download');
    
    
    // Report for Production Tracking
    Route::get('/production-tracking', [ProductionReportController::class, 'productionTrackingReport'])->name('productionTracking.report');
    Route::get('/production-tracking/details/{id}', [ProductionReportController::class, 'productionTrackingDetails'])->name('productionTracking.details');
    Route::get('/production-tracking/download', [ProductionReportController::class, 'downloadProductionTrackingWithOutfile'])->name('productionTracking.download');
});
