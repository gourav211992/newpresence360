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
        Route::get('/hr-dashboard', 'hrDashboard')->name('recruitment.hr-dashboard');
        Route::get('/fetch-employees', 'fetchEmployees')->name('recruitment.fetch-employees');
        Route::get('/fetch-candidates', 'fetchCandidates')->name('recruitment.fetch-candidates');
        Route::get('/fetch-team', 'fetchTeam')->name('recruitment.fetch-team');
        Route::get('/fetch-emails', 'fetchEmails')->name('recruitment.fetch-emails');
        Route::get('/fetch-applicants', 'fetchApplicants')->name('recruitment.fetch-applicants');
        Route::get('/fetch-interview-summary', 'interviewSummary')->name('recruitment.fetch-interview-summary');
        Route::get('/interview-events', 'getInterviewEvents')->name('recruitment.interview-events');

    });

    Route::controller(RequestController::class)->prefix('requests')->group(function () {
        Route::get('/', 'index')->name('recruitment.requests');
        Route::get('/for-approval', 'index')->name('recruitment.requests.for-approval');
        Route::get('/assigned-candidate', 'assignedCandidateList')->name('recruitment.requests.assigned-candidate');
        Route::get('/interview-scheduled', 'jobInterviewList')->name('recruitment.requests.interview-scheduled');
        Route::get('/create', 'create')->name('recruitment.requests.create');
        Route::get('/edit/{id}', 'edit')->name('recruitment.requests.edit');
        Route::get('/show/{id}', 'show')->name('recruitment.requests.show');
        Route::get('/job-view/{id}', 'jobView')->name('recruitment.requests.job-view');
    });

    Route::controller(JobController::class)->prefix('jobs')->group(function () {
        Route::get('/', 'index')->name('recruitment.jobs');
        Route::get('/assigned-candidate', 'getAssignedCandidateList')->name('recruitment.jobs.assigned-candidate');
        Route::get('/interview-scheduled', 'getJobInterviewList')->name('recruitment.jobs.interview-scheduled');
        Route::get('/create', 'create')->name('recruitment.jobs.create');
        Route::get('/edit/{id}', 'edit')->name('recruitment.jobs.edit');
        Route::get('/show/{id}', 'show')->name('recruitment.jobs.show');
        Route::get('/get-job-requests', 'getJobRequestsByTitle')->name('recruitment.jobs.get-job-requests');
        Route::get('/candidates/{id}', 'candidates')->name('recruitment.jobs.candidates');
        Route::get('/candidate-detail/{id}/{jobId}', 'candidateDetail')->name('recruitment.jobs.candidate-detail');
        Route::get('/fetch-candidates/{jobId}/{status}', 'fetchCandidates')->name('recruitment.jobs.fetch-candidates');
        Route::get('/candidate-interview-detail/{id}/{jobId}', 'candidateInterviewDetail')->name('recruitment.jobs.candidate-interview-detail');
    });

    Route::controller(JobCandidateController::class)->prefix('job-candidates')->group(function () {
        Route::get('/', 'index')->name('recruitment.job-candidates');
        Route::get('/create', 'create')->name('recruitment.job-candidates.create');
        Route::get('/edit/{id}', 'edit')->name('recruitment.job-candidates.edit');
        Route::get('/show/{id}', 'show')->name('recruitment.job-candidates.show');
    });

    Route::controller(ReferalController::class)->prefix('my-referal')->group(function () {
        Route::get('/', 'index')->name('recruitment.my-referal');
        Route::get('/show/{id}/{jobId}', 'show')->name('recruitment.my-referal.show');
    });

    Route::controller(InternalJobController::class)->prefix('internal-jobs')->group(function () {
        Route::get('/', 'index')->name('recruitment.internal-jobs');
        Route::get('/apply/{jobId}', 'apply')->name('recruitment.internal-jobs.apply');
    });

    Route::controller(AssessmentController::class)->prefix('assessments')->group(function () {
        Route::get('/', 'index')->name('recruitment.assessments');
        Route::get('/create', 'create')->name('recruitment.assessments.create');
        Route::get('/result', 'result')->name('recruitment.assessments.result');
        Route::get('/template-data', 'templateData')->name('recruitment.assessments.get-template-data');
        Route::get('/edit/{id}', 'edit')->name('recruitment.assessments.edit');
        Route::get('/preview/{id}', 'show')->name('recruitment.assessments.preview');
    });

    Route::controller(HrRequestController::class)->prefix('request-hr')->group(function () {
        Route::get('/', 'index')->name('recruitment.request-hr');
        Route::get('/show/{id}', 'show')->name('recruitment.request-hr.show');
    });

    Route::controller(ActivityController::class)->prefix('my-activities')->group(function () {
        Route::get('/', 'index')->name('recruitment.my-activities');
        Route::get('/interview-log', 'interviewLog')->name('recruitment.my-activities.interview-log');
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
            Route::post('/assign-vendor/{id}', 'assignVendor')->name('recruitment.jobs.assign-vendor');
            Route::put('/{id}', 'update')->name('recruitment.jobs.update');
            Route::delete('/remove-panel/{id}/{roundId}', 'removePanel')->name('recruitment.jobs.remove-panel');
            Route::post('/update-candidate-status', 'updateCandidateStatus')->name('recruitment.jobs.update-candidate-status');
            Route::post('/recruitment.jobs.scheduled-interview/{id}', 'updateCandidateStatus')->name('recruitment.jobs.scheduled-interview');
            Route::post('/update-status/{id}', 'updateStatus')->name('recruitment.jobs.update-status');
            Route::get('/get-assigned-vendors/{id}', 'getAssignedVendors');
            Route::post('/get-request-detail', 'getRequestDetail')->name('recruitment.jobs.get-request-detail');
        });

        Route::controller(JobInterviewController::class)->prefix('jobs-interviews')->group(function () {
            Route::post('/scheduled/{jobId}', 'scheduled')->name('recruitment.jobs-interviews.scheduled');
            Route::post('/feedback', 'feedback')->name('recruitment.jobs-interviews.feedback');
            Route::post('/hr-feedback', 'hrFeedback')->name('recruitment.jobs-interviews.hr-feedback');
        });

        Route::controller(JobCandidateController::class)->prefix('job-candidates')->group(function () {
            Route::post('/store', 'store')->name('recruitment.job-candidates.store');
            Route::put('/{id}', 'update')->name('recruitment.job-candidates.update');
            Route::delete('/destroy/{id}', 'destroy')->name('recruitment.job-candidates.destroy');
        });

        Route::controller(InternalJobController::class)->prefix('internal-jobs')->group(function () {
            Route::post('/store-referrals/{jobId}', 'storeReferrals')->name('recruitment.internal-jobs.store-referrals');
        });

        Route::controller(ReferalController::class)->prefix('my-referal')->group(function () {
            Route::post('/applied-for-job/{jobId}', 'appliedForJob')->name('recruitment.my-referal.applied-for-job');
        });
         
        Route::controller(IndexController::class)->group(function () {
            Route::post('/user-configuration', 'userConfiguration')->name('recruitment.user-configuration');
            Route::get('/get-locations/{groupId}', 'getLocations')->name('recruitment.get-locations');

        });

        Route::controller(AssessmentController::class)->prefix('assessments')->group(function () {
            Route::post('/store', 'store')->name('recruitment.assessments.store');
            Route::post('/update-status', 'updateStatus')->name('recruitment.assessments.update-status');
            Route::delete('/remove-question/{id}', 'removeQuestion')->name('recruitment.assessments.remove-question');
            Route::delete('/remove-option/{id}', 'removeOption')->name('recruitment.assessments.remove-option');
            Route::delete('/remove-assessment/{id}', 'destroy')->name('recruitment.assessments.remove-assessment');
            Route::put('/{id}', 'update')->name('recruitment.assessments.update');
        });
    });
});
