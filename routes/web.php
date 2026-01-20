<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Web Routes for Central Tenant 
Route::group(['domain' => config('tenancy.central_domains.0')], function () {

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified', 'check.license'])->name('dashboard');


    Route::middleware(['auth', 'check.license'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('/check-license-status', function () {
            $licenseService = app(\App\Services\LicenseService::class);
            return response()->json([
                'valid' => $licenseService->isValid(),
                'days' => $licenseService->daysLeft(),
            ]);
        });
    });

    require __DIR__ . '/auth.php';
});

