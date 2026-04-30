<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\DocsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/docs', [DocsController::class, 'index'])->name('docs');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::post('/tokens', [DashboardController::class, 'createToken'])->name('tokens.create');
    Route::post('/tokens/{id}/deactivate', [DashboardController::class, 'deactivateToken'])->name('tokens.deactivate');

    // Top-up
    Route::get('/topup', [\App\Http\Controllers\Client\TopupController::class, 'index'])->name('topup.index');
    Route::get('/topup/manual', [\App\Http\Controllers\Client\TopupController::class, 'manual'])->name('topup.manual');
    Route::post('/topup/manual', [\App\Http\Controllers\Client\TopupController::class, 'storeManual'])->name('topup.manual.store');
    Route::get('/topup/stripe', [\App\Http\Controllers\Client\TopupController::class, 'stripe'])->name('topup.stripe');
    Route::post('/topup/stripe', [\App\Http\Controllers\Client\TopupController::class, 'storeStripe'])->name('topup.stripe.store');
    Route::get('/topup/stripe/success', [\App\Http\Controllers\Client\TopupController::class, 'stripeSuccess'])->name('topup.stripe.success');
    Route::get('/topup/stripe/cancel', [\App\Http\Controllers\Client\TopupController::class, 'stripeCancel'])->name('topup.stripe.cancel');
    Route::get('/topup/crypto', [\App\Http\Controllers\Client\TopupController::class, 'crypto'])->name('topup.crypto');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
