<?php

use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\PhonePeCallbackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/submit-lead', [LeadController::class, 'store']);

Route::post('/payment/phonepe/callback', PhonePeCallbackController::class)
    ->name('api.payment.phonepe.callback');
