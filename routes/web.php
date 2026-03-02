<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormTemplateController;
use App\Livewire\Admin\FormTemplatesIndex;

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

    //admin UI page (Livewire)
    Route::get('/admin/forms', FormTemplatesIndex::class)
        ->middleware('role:admin')
        ->name('admin.forms.index');

        Route::prefix('form-templates')->group(function () {
            Route::post('/', [FormTemplateController::class, 'store'])->name('form-templates.store');
            Route::put('{template}', [FormTemplateController::class, 'update'])->name('form-templates.update');
        });


});
