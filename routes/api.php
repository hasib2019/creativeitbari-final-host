<?php

use App\Http\Controllers\Api\PaymentController;
use App\Http\Middleware\AuthenticateMerchantApp;
use Illuminate\Support\Facades\Route;

// Payment hub API for apps. Auth: X-App-Key + X-Timestamp + X-Signature (HMAC).
Route::prefix('v1')
    ->middleware(['throttle:120,1', AuthenticateMerchantApp::class])
    ->group(function () {
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments/{reference}', [PaymentController::class, 'show']);
        Route::get('/orders/{orderId}', [PaymentController::class, 'showByOrder'])->where('orderId', '.+');
    });
