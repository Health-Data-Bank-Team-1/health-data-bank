<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\OtpController;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| DEFAULT DASHBOARD (fallback)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $user = Auth::user();

    return match ($user->role->name) {
        'User' => redirect()->route('dashboard.user'),
        'Researcher' => redirect()->route('dashboard.researcher'),
        'Healthcare Provider' => redirect()->route('dashboard.provider'),
        'Administrator' => redirect()->route('dashboard.admin'),
        default => view('dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| ROLE BASED DASHBOARDS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard/user', function () {
        return view('dashboards.user');
    })->name('dashboard.user');

    Route::get('/dashboard/researcher', function () {
        return view('dashboards.researcher');
    })->name('dashboard.researcher');

    Route::get('/dashboard/provider', function () {
        return view('dashboards.provider');
    })->name('dashboard.provider');

    Route::get('/dashboard/admin', function () {
        return view('dashboards.admin');
    })->name('dashboard.admin');
});

/*
|--------------------------------------------------------------------------
| OTP VERIFICATION ROUTES
|--------------------------------------------------------------------------
*/
    Route::middleware(['auth'])->group(function () {
    Route::get('/otp', [OtpController::class, 'show'])->name('otp.show');
    Route::post('/otp', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('/otp/send', [OtpController::class, 'sendOtp'])->name('otp.send');

    // Verify OTP submission
    Route::post('/otp', [OtpController::class, 'verify'])->name('auth.otp.verify');

    // Send / Resend OTP
    Route::post('/otp/send', [OtpController::class, 'sendOtp'])->name('auth.otp.send');
});

/*
|--------------------------------------------------------------------------
| PROFILE ROUTES (keep yours)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';