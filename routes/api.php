<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Reporting\TrendController;
use App\Http\Controllers\Reporting\ResearcherAggregateController;
use App\Http\Controllers\Admin\FormTemplateApprovalController;
use App\Http\Controllers\Admin\FormTemplateVersionController;
use App\Http\Controllers\Admin\AdminFormTemplateController;
use App\Services\CohortFilterBuilder;
use App\Services\KThresholdService;
use App\Http\Controllers\Api\Reports\DashboardReportController;
use App\Http\Controllers\Researcher\ResearcherCohortController;
use App\Http\Controllers\Researcher\ResearcherReportController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/reports/dashboard/trends', [DashboardReportController::class, 'trends']);
    Route::get('/reports/dashboard/trends/export.csv', [DashboardReportController::class, 'exportTrendsCsv']);
});


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

/*
 * Form Template Versioning
 */

//get version history
Route::middleware('auth:sanctum')->get(
    'form-templates/{template}/versions',
    [FormTemplateVersionController::class, 'index']
);

//rollback to a version (admin only)
Route::middleware(['auth:sanctum', 'role:admin'])->post(
    'form-templates/{template}/rollback/{version}',
    [FormTemplateVersionController::class, 'rollback']
);

Route::middleware(['auth:sanctum', 'role:researcher'])->get(
    '/research/reporting/aggregate',
    [ResearcherAggregateController::class, 'index']
);

Route::middleware(['auth:sanctum', 'role:researcher'])->group(function () {
    // Create a cohort
    Route::post('/researcher/cohorts', [ResearcherCohortController::class, 'store']);
    // Generate aggregated report
    Route::post('/researcher/reports/aggregated', [ResearcherReportController::class, 'aggregated']);
    // Export aggregated report as CSV
    Route::post('/researcher/reports/aggregated/export.csv', [ResearcherReportController::class, 'exportAggregatedCsv']);
});

Route::middleware('auth:sanctum')->get('/me/summary',
    [\App\Http\Controllers\Api\MeSummaryController::class, 'show']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/reporting/trends', [TrendController::class, 'index'])
        ->name('reporting.trends.index');
});
