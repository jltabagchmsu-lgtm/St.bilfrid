<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/v1/health', function () {
    return response()->json([
        'status' => 'online',
        'framework' => 'Laravel 10.x',
        'php_version' => PHP_VERSION,
        'timestamp' => now()->toIso8601String(),
    ]);
});
