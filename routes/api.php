<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PatientController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// This line replaces your single GET route
Route::apiResource('patients', PatientController::class);
