<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => config('app.name') . ' API is running.',
        'data' => [
            'version' => '1.0.0',
        ],
    ]);
});
