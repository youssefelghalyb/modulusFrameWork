<?php 


use Illuminate\Support\Facades\Route;
use Modules\UserManagement\Http\Controllers\Api\V1\AuthController;
use Modules\UserManagement\Http\Controllers\Api\V1\ProfileController;

Route::prefix('api/v1')->group(function () {
    // Auth Routes
    Route::post('register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::get('verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('api.auth.verify');
    Route::post('password/forgot', [AuthController::class, 'forgotPassword'])->name('api.auth.password.forgot');
    Route::post('password/reset', [AuthController::class, 'resetPassword'])->name('api.auth.password.reset');
    Route::post('refresh-token', [AuthController::class, 'refresh'])->name('api.auth.refresh');

    // Protected Routes
    Route::middleware(['auth:sanctum', 'verified', 'device.tracking'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        
        // Profile Routes
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'show'])->name('api.profile.show');
            Route::put('/', [ProfileController::class, 'update'])->name('api.profile.update');
            Route::put('password', [ProfileController::class, 'updatePassword'])->name('api.profile.password');
            
            // Device Management
            Route::get('devices', [ProfileController::class, 'devices'])->name('api.profile.devices');
            Route::delete('devices/{deviceId}', [ProfileController::class, 'revokeDevice'])->name('api.profile.devices.revoke');
            Route::post('devices/{deviceId}/trust', [ProfileController::class, 'trustDevice'])->name('api.profile.devices.trust');
        });
    });
});