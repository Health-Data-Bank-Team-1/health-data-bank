<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Reporting\TrendController;
use App\Http\Controllers\Admin\FormTemplateApprovalController;
use App\Http\Controllers\Admin\FormTemplateVersionController;
use App\Http\Controllers\Admin\AdminFormTemplateController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('patients', PatientController::class);

Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin/forms')
    ->group(function () {

        Route::get('/', [AdminFormTemplateController::class, 'index']);

        Route::post('{template:id}/approve', [FormTemplateApprovalController::class, 'approve']);
        Route::post('{template:id}/reject', [FormTemplateApprovalController::class, 'reject']);
        Route::post('{template:id}/submit', [FormTemplateApprovalController::class, 'submit']);
    });

/*
 * Form Template Versioning
 */

// get version history
Route::middleware('auth:sanctum')->get(
    'form-templates/{template:id}/versions',
    [FormTemplateVersionController::class, 'index']
);

// rollback to a version (admin only)
Route::middleware(['auth:sanctum', 'role:admin'])->post(
    'form-templates/{template:id}/rollback/{version}',
    [FormTemplateVersionController::class, 'rollback']
);

Route::middleware('auth:sanctum')->get(
    '/me/summary',
    [\App\Http\Controllers\Api\MeSummaryController::class, 'show']
);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/reporting/trends', [TrendController::class, 'index'])
        ->name('reporting.trends.index');
});