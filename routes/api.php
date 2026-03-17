<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Reporting\TrendController;
use App\Http\Controllers\Reporting\ResearcherAggregateController;
use App\Http\Controllers\Admin\FormTemplateApprovalController;
use App\Http\Controllers\Admin\FormTemplateVersionController;
use App\Http\Controllers\Admin\AdminFormTemplateController;
use App\Http\Controllers\Provider\PatientSearchController;
use App\Http\Controllers\Provider\PatientRecordController;
use App\Http\Controllers\Provider\ProviderDashboardController;
use App\Http\Controllers\Provider\ProviderFeedbackController;

use App\Services\CohortFilterBuilder;
use App\Services\KThresholdService;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('patients', PatientController::class);

Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin/forms')
    ->group(function () {
        Route::get('/', [AdminFormTemplateController::class, 'index']);
        Route::post('{template}/approve', [FormTemplateApprovalController::class, 'approve']);
        Route::post('{template}/reject', [FormTemplateApprovalController::class, 'reject']);
        Route::post('{template}/submit', [FormTemplateApprovalController::class, 'submit']);
    });

Route::middleware('auth:sanctum')->get(
    'form-templates/{template}/versions',
    [FormTemplateVersionController::class, 'index']
);

Route::middleware(['auth:sanctum', 'role:admin'])->post(
    'form-templates/{template}/rollback/{version}',
    [FormTemplateVersionController::class, 'rollback']
);

Route::middleware(['auth:sanctum', 'role:researcher'])->get(
    '/research/reporting/aggregate',
    [ResearcherAggregateController::class, 'index']
);

Route::middleware(['auth:sanctum', 'role:provider'])->get(
    '/provider/patients/search',
    [PatientSearchController::class, 'index']
);

Route::middleware(['auth:sanctum', 'role:provider'])->get(
    '/provider/patients/{patient}/record',
    [PatientRecordController::class, 'show']
);

Route::middleware(['auth:sanctum', 'role:provider'])->get(
    '/provider/dashboard',
    [ProviderDashboardController::class, 'index']
);

Route::middleware(['auth:sanctum', 'role:provider'])->post(
    '/provider/feedback',
    [ProviderFeedbackController::class, 'store']
);

Route::middleware('auth:sanctum')->get('/me/summary',
    [\App\Http\Controllers\Api\MeSummaryController::class, 'show']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/reporting/trends', [TrendController::class, 'index'])
        ->name('reporting.trends.index');
});