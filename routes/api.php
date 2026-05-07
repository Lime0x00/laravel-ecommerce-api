<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| This project uses Strictly Stateless JWT Authentication.
| All protected routes must use the 'auth:api' middleware.
|
*/

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
});