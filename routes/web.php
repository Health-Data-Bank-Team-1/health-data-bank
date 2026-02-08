<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\MyProgress;

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
});
