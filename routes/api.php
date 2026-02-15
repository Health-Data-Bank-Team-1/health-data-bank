<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\FormSubmissionController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/form-submissions', [FormSubmissionController::class, 'store']);
});

// This line replaces your single GET route
Route::apiResource('patients', PatientController::class);


