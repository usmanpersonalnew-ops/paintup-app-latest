<?php

use App\Http\Controllers\Api\LeadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/submit-lead', [LeadController::class, 'store']);
