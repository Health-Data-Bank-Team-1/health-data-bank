<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\MyProgress;
use App\Livewire\UserFormSelect;
use App\Livewire\UserTodo;
use App\Livewire\FormIndex;
use App\Livewire\FormRenderer;
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
    Route::get('/my-progress', MyProgress::class)
        ->name('my-progress');
    Route::get('/user-form-select', UserFormSelect::class)
        ->name('user-form-select');
    Route::get('/user-todo', UserTodo::class)
        ->name('user-todo');
    Route::get('/forms', FormIndex::class)
        ->name('forms.index');
    Route::get('/forms/{form}', FormRenderer::class)
        ->name('forms.show');

    //admin UI page (Livewire)
    Route::get('/admin/forms', FormTemplatesIndex::class)
        ->middleware('role:admin')
        ->name('admin.forms.index');

    Route::prefix('form-templates')->group(function () {
        Route::post('/', [FormTemplateController::class, 'store'])->name('form-templates.store');
        Route::put('{template}', [FormTemplateController::class, 'update'])->name('form-templates.update');
    });


});
