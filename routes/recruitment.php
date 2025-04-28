<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| recruitment Routes
|--------------------------------------------------------------------------
|
| Here is where you can register recruitment routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group with "recruitment" prefix. Make something great!
|
*/

Route::middleware(['user.auth'])->group(function () {
    
    Route::controller(IndexController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('recruitment.dashboard');
        Route::get('/fetch-employees', 'fetchEmployees')->name('recruitment.fetch-employees');
        Route::get('/fetch-emails', 'fetchEmails')->name('recruitment.fetch-emails');
    });

    Route::controller(RequestController::class)->prefix('requests')->group(function () {
        Route::get('/', 'index')->name('recruitment.requests');
        Route::get('/for-approval', 'index')->name('recruitment.requests.for-approval');
        Route::get('/assigned-candidate', 'index')->name('recruitment.requests.assigned-candidate');
        Route::get('/interview-scheduled', 'index')->name('recruitment.requests.interview-scheduled');
        Route::get('/create', 'create')->name('recruitment.requests.create');
        Route::get('/edit/{id}', 'edit')->name('recruitment.requests.edit');
        Route::get('/show/{id}', 'show')->name('recruitment.requests.show');
    });

    Route::controller(JobController::class)->prefix('jobs')->group(function () {
        Route::get('/', 'index')->name('recruitment.jobs');
        Route::get('/assigned-candidate', 'index')->name('recruitment.jobs.assigned-candidate');
        Route::get('/create', 'create')->name('recruitment.jobs.create');
        Route::get('/edit/{id}', 'edit')->name('recruitment.jobs.edit');
        Route::get('/show/{id}', 'show')->name('recruitment.jobs.show');
        Route::get('/get-job-requests', 'getJobRequestsByTitle')->name('recruitment.jobs.get-job-requests');
        Route::get('/candidates/{id}', 'candidates')->name('recruitment.jobs.candidates');
        Route::get('/candidate-detail/{id}/{jobId}', 'candidateDetail')->name('recruitment.jobs.candidate-detail');
        Route::get('/fetch-candidates/{jobId}/{status}', 'fetchCandidates')->name('recruitment.jobs.fetch-candidates');
    });

    Route::controller(JobCandidateController::class)->prefix('job-candidates')->group(function () {
        Route::get('/', 'index')->name('recruitment.job-candidates');
        Route::get('/create', 'create')->name('recruitment.job-candidates.create');
        Route::get('/edit/{id}', 'edit')->name('recruitment.job-candidates.edit');
        Route::get('/show/{id}', 'show')->name('recruitment.job-candidates.show');
    });

    Route::group(['middleware' => ['apiresponse']], function () {
        Route::controller(RequestController::class)->prefix('requests')->group(function () {
            Route::post('/store', 'store')->name('recruitment.requests.store');
            Route::put('/{id}', 'update')->name('recruitment.requests.update');
            Route::post('/update-status/{id}', 'updateStatus')->name('recruitment.requests.update-status');
        });

        Route::controller(JobController::class)->prefix('jobs')->group(function () {
            Route::post('/store', 'store')->name('recruitment.jobs.store');
            Route::post('/assign-candidate/{id}', 'assignCandidate')->name('recruitment.jobs.assign-candidate');
            Route::put('/{id}', 'update')->name('recruitment.jobs.update');
            Route::delete('/remove-panel/{id}/{roundId}', 'removePanel')->name('recruitment.jobs.remove-panel');
            Route::post('/update-candidate-status', 'updateCandidateStatus')->name('recruitment.jobs.update-candidate-status');
        });

        Route::controller(JobCandidateController::class)->prefix('job-candidates')->group(function () {
            Route::post('/store', 'store')->name('recruitment.job-candidates.store');
            Route::put('/{id}', 'update')->name('recruitment.job-candidates.update');
            Route::delete('/destroy/{id}', 'destroy')->name('recruitment.job-candidates.destroy');
        });
    });
});
