<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FormSubmissionController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Reporting\TrendController;
use App\Http\Controllers\Reporting\ResearcherAggregateController;
use App\Http\Controllers\Admin\FormTemplateApprovalController;
use App\Http\Controllers\Admin\FormTemplateVersionController;
use App\Http\Controllers\Admin\AdminFormTemplateController;
use App\Http\Controllers\Admin\ReportModerationController;
use App\Http\Controllers\Provider\PatientSearchController;
use App\Http\Controllers\Provider\PatientRecordController;
use App\Http\Controllers\Provider\ProviderDashboardController;
use App\Http\Controllers\Provider\ProviderFeedbackController;
use App\Services\CohortFilterBuilder;
use App\Services\KThresholdService;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Api\Reports\DashboardReportController;
use App\Http\Controllers\Researcher\ResearcherCohortController;
use App\Http\Controllers\Researcher\ResearcherReportController;
use App\Http\Controllers\Api\HealthGoalController;
use App\Http\Controllers\Api\PersonalComparisonController;
use App\Http\Controllers\Api\MeSummaryController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/reports/dashboard/trends', [DashboardReportController::class, 'trends']);
    Route::get('/reports/dashboard/trends/export.csv', [DashboardReportController::class, 'exportTrendsCsv']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('patients', PatientController::class);
});

Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin/forms')
    ->group(function () {
        Route::get('/', [AdminFormTemplateController::class, 'index']);
        Route::post('{template:id}/approve', [FormTemplateApprovalController::class, 'approve']);
        Route::post('{template:id}/reject', [FormTemplateApprovalController::class, 'reject']);
        Route::post('{template:id}/submit', [FormTemplateApprovalController::class, 'submit']);
    });

Route::middleware('auth:sanctum')->get(
    'form-templates/{template}/versions',
    [FormTemplateVersionController::class, 'index']
);

Route::middleware(['auth:sanctum', 'role:admin'])->post(
    'form-templates/{template:id}/rollback/{version}',
    [FormTemplateVersionController::class, 'rollback']
);

Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin/reports')
    ->group(function () {
        Route::post('{report}/archive', [ReportModerationController::class, 'archive']);
        Route::post('{report}/delete', [ReportModerationController::class, 'delete']);
        Route::post('{report}/restore', [ReportModerationController::class, 'restore']);
        Route::get('{report}/moderation-status', [ReportModerationController::class, 'status']);
        Route::post('{report}/permanent-delete', [ReportModerationController::class, 'permanentDelete']);
    });

Route::middleware(['auth:sanctum', 'role:researcher'])->get(
    '/research/reporting/aggregate',
    [ResearcherAggregateController::class, 'index']
);

Route::middleware(['auth:sanctum', 'role:researcher'])->group(function () {
    Route::post('/researcher/cohorts', [ResearcherCohortController::class, 'store']);
    Route::post('/researcher/reports/aggregated', [ResearcherReportController::class, 'aggregated']);
    Route::post('/researcher/reports/aggregated/export.csv', [ResearcherReportController::class, 'exportAggregatedCsv']);
    Route::post('/researcher/reports/{report}/append', [ResearcherReportController::class, 'append']);
});

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

Route::middleware('auth:sanctum')->get(
    '/me/summary',
    [MeSummaryController::class, 'show']
);

Route::middleware('auth:sanctum')->post(
    '/form-submissions',
    [FormSubmissionController::class, 'store']
);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/reporting/trends', [TrendController::class, 'index'])
        ->name('reporting.trends.index');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/goals', [HealthGoalController::class, 'index']);
    Route::post('/goals', [HealthGoalController::class, 'store']);
    Route::get('/goals/{goalId}', [HealthGoalController::class, 'show']);
    Route::put('/goals/{goalId}', [HealthGoalController::class, 'update']);
});

Route::middleware('auth:sanctum')->get(
    '/me/comparison',
    [PersonalComparisonController::class, 'show']
);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/audit-log', [AdminAuditLogController::class, 'index'])
        ->name('admin.audit-log.index');
});
