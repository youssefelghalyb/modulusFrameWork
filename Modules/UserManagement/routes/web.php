<?php 



use Illuminate\Support\Facades\Route;
use Modules\UserManagement\Http\Controllers\Web\V1\ProfileController;
use Modules\UserManagement\Http\Controllers\Web\V1\AuthController as V1AuthController;

Route::middleware(['web'])->group(function () {
    // Guest Routes
    Route::middleware('guest')->group(function () {
        Route::get('login', [V1AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [V1AuthController::class, 'login']);
        Route::get('register', [V1AuthController::class, 'showRegistrationForm'])->name('register');
        Route::post('register', [V1AuthController::class, 'register']);
        Route::get('password/reset', [V1AuthController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('password/email', [V1AuthController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('password/reset/{token}', [V1AuthController::class, 'showResetForm'])->name('password.reset');
        Route::post('password/reset', [V1AuthController::class, 'reset'])->name('password.update');
    });

    // Email Verification Routes
    Route::get('email/verify', [V1AuthController::class, 'showVerificationNotice'])
        ->middleware('auth')
        ->name('verification.notice');
        
    Route::get('email/verify/{id}/{hash}', [V1AuthController::class, 'verifyEmail'])
        ->middleware(['auth', 'signed'])
        ->name('verification.verify');
        
    Route::post('email/resend', [V1AuthController::class, 'resendVerification'])
        ->middleware(['auth', 'throttle:6,1'])
        ->name('verification.resend');

    // Authenticated Routes
    Route::middleware(['auth', 'verified', 'device.tracking'])->group(function () {
        Route::post('logout', [V1AuthController::class, 'logout'])->name('logout');

        // Profile Routes
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
            Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/', [ProfileController::class, 'update'])->name('profile.update');
            Route::get('/password', [ProfileController::class, 'editPassword'])->name('profile.password.edit');
            Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
            
            // Device Management
            Route::get('/devices', [ProfileController::class, 'devices'])->name('profile.devices');
            Route::delete('/devices/{deviceId}', [ProfileController::class, 'revokeDevice'])->name('profile.devices.revoke');
            Route::post('/devices/{deviceId}/trust', [ProfileController::class, 'trustDevice'])->name('profile.devices.trust');
        });
    });
});