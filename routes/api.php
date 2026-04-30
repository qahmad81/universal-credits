<?php

use App\Http\Controllers\Api\V1\ReserveController;
use App\Http\Middleware\AuthenticateVendorToken;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/reserve', ReserveController::class)
        ->middleware(AuthenticateVendorToken::class);
});
