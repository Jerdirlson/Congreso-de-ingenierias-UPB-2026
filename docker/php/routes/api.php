<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Congreso Ingenierías 2026
|--------------------------------------------------------------------------
*/

/**
 * Health Check
 * GET /api/health
 *
 * Returns a JSON response confirming the API is up and running.
 * Used by the frontend to verify backend connectivity.
 */
Route::get('/health', function () {
    return response()->json([
        'status'      => 'ok',
        'service'     => 'congreso-ingenierias-2026-api',
        'version'     => '1.0.0',
        'timestamp'   => now()->toISOString(),
        'php_version' => PHP_VERSION,
        'laravel'     => app()->version(),
    ]);
});
