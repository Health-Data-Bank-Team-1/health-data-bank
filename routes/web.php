<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\FormTemplateApprovalController;
use App\Http\Controllers\FormTemplateController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/', [FormTemplateController::class, 'store']);

    Route::put('{template}', [FormTemplateController::class, 'update']);

    //Admin form approval routes, require the admin to be logged in, have their email verified and to have the admin role
    Route::prefix('admin/forms')->group(function () {
        Route::post('{template}/submit', [FormTemplateApprovalController::class, 'submit']);
        Route::post('{template}/approve', [FormTemplateApprovalController::class, 'approve']);
        Route::post('{template}/reject', [FormTemplateApprovalController::class, 'reject']);
    });

});
