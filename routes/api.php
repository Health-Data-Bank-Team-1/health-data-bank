<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Reporting\TrendController;
use App\Http\Controllers\Admin\FormTemplateApprovalController;
use App\Http\Controllers\Admin\FormTemplateVersionController;
use App\Http\Controllers\Admin\AdminFormTemplateController;
use App\Http\Controllers\Researcher\ResearcherReportController;

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

Route::middleware('auth:sanctum')->get('/me/summary',
    [\App\Http\Controllers\Api\MeSummaryController::class, 'show']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/reporting/trends', [TrendController::class, 'index'])
        ->name('reporting.trends.index');
});

Route::middleware(['auth:sanctum', 'role:researcher'])->group(function () {
    Route::post('/researcher/reports/{report}/append', [ResearcherReportController::class, 'append']);
});