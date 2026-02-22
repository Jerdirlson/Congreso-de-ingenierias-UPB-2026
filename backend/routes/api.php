<?php

use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\RecordingController;
use App\Http\Controllers\Api\SpeakerController;
use App\Http\Controllers\Api\StreamController;
use App\Http\Controllers\Api\UploadTestController;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Congreso Ingenierías 2026
|--------------------------------------------------------------------------
|
| Prefix: /api
| Auth:   Laravel Sanctum (token-based)
|
*/

// ── Upload test: activo en local O cuando ALLOW_UPLOAD_TEST=true en .env ───────
// Para habilitar en producción temporalmente: agregar ALLOW_UPLOAD_TEST=true al .env
// y correr: docker exec cgr-backend php artisan route:clear
if (app()->environment('local') || config('app.allow_upload_test')) {
    Route::get('/dev/files',                          [UploadTestController::class, 'index']);
    Route::post('/dev/upload',                        [UploadTestController::class, 'store']);
    Route::get('/dev/files/{filename}/download',      [UploadTestController::class, 'download']);
    Route::delete('/dev/files/{filename}',            [UploadTestController::class, 'destroy']);
}

// ── Public ────────────────────────────────────────────────────────────────────

/** GET /api/health — Sin throttle ni auth; siempre responde aunque Redis/cache fallen */
Route::get('/health', HealthController::class)->withoutMiddleware([ThrottleRequests::class]);

/** Public read-only resources — 120 requests/minute por IP */
Route::middleware('throttle:120,1')->group(function () {
    Route::get('/events',           [EventController::class, 'index']);
    Route::get('/events/{event}',   [EventController::class, 'show']);
    Route::get('/speakers',         [SpeakerController::class, 'index']);
    Route::get('/speakers/{speaker}', [SpeakerController::class, 'show']);
    Route::get('/documents',        [DocumentController::class, 'index']);
    Route::get('/documents/{document}', [DocumentController::class, 'show']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download']);
    Route::get('/streams',                        [StreamController::class, 'index']);
    Route::get('/streams/{stream}',               [StreamController::class, 'show']);
    Route::get('/recordings/{recording}',         [RecordingController::class, 'show']);
});

// ── Authenticated ─────────────────────────────────────────────────────────────

/** Rutas autenticadas — 60 requests/minute por usuario */
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // Events (admin)
    Route::post('/events',              [EventController::class, 'store']);
    Route::put('/events/{event}',       [EventController::class, 'update']);
    Route::delete('/events/{event}',    [EventController::class, 'destroy']);

    // Speakers
    Route::post('/speakers',             [SpeakerController::class, 'store']);
    Route::put('/speakers/{speaker}',    [SpeakerController::class, 'update']);
    Route::delete('/speakers/{speaker}', [SpeakerController::class, 'destroy']);

    // Documents
    Route::post('/documents',               [DocumentController::class, 'store']);
    Route::put('/documents/{document}',     [DocumentController::class, 'update']);
    Route::delete('/documents/{document}',  [DocumentController::class, 'destroy']);

    // Streams
    Route::post('/streams',                          [StreamController::class, 'store']);
    Route::put('/streams/{stream}',                  [StreamController::class, 'update']);
    Route::delete('/streams/{stream}',               [StreamController::class, 'destroy']);
    Route::post('/streams/{stream}/go-live',         [StreamController::class, 'goLive']);
    Route::post('/streams/{stream}/end',             [StreamController::class, 'end']);

    // Recordings (subida y eliminación de videos)
    Route::post('/streams/{stream}/recordings',      [RecordingController::class, 'store']);
    Route::delete('/recordings/{recording}',         [RecordingController::class, 'destroy']);
});
