<?php

use App\Http\Controllers\Api\V1\ReserveController;
use App\Http\Controllers\Api\V1\SettlementController;
use App\Http\Middleware\AuthenticateVendorToken;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::middleware(AuthenticateVendorToken::class)->group(function () {
        Route::post('/reserve', ReserveController::class);
        Route::post('/confirm', [SettlementController::class, 'confirm']);
        Route::post('/cancel', [SettlementController::class, 'cancel']);
    });
});
