<?php

use App\Http\Controllers\Api\AbstractController;
use App\Http\Controllers\Api\AdminSubmissionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\CloudflareVideoWebhookController;
use App\Http\Controllers\Api\CongressEventController;
use App\Http\Controllers\Api\DocumentSubmissionController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ModalityController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SubmissionController;
use App\Http\Controllers\Api\ThematicAxisController;
use App\Http\Controllers\Api\UploadTestController;
use App\Http\Controllers\Api\VideoController;
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

// ── Upload test (solo local) ─────────────────────────────────────────────
if (app()->environment('local') || config('app.allow_upload_test')) {
    Route::get('/dev/files',                     [UploadTestController::class, 'index']);
    Route::post('/dev/upload',                  [UploadTestController::class, 'store']);
    Route::get('/dev/files/{filename}/download', [UploadTestController::class, 'download']);
    Route::delete('/dev/files/{filename}',      [UploadTestController::class, 'destroy']);
}

// ── Public ────────────────────────────────────────────────────────────────

Route::get('/health', HealthController::class)->withoutMiddleware([ThrottleRequests::class]);

Route::middleware('throttle:120,1')->group(function () {
    Route::get('/thematic-axes', [ThematicAxisController::class, 'index']);
    Route::get('/events',        [CongressEventController::class, 'index']);
});

// ── Auth (público) ────────────────────────────────────────────────────────

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/logout',  [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me',       [AuthController::class, 'me'])->middleware('auth:sanctum');
});

// ── Verificación de correo (sin auth para el enlace del email) ─────────────

Route::middleware('throttle:6,1')->group(function () {
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->name('verification.send');
});

// ── Ponente (auth + role + email verificado) ──────────────────────────────

Route::middleware(['auth:sanctum', 'role:ponente', 'throttle:60,1'])->group(function () {
    Route::get('/submissions',                    [SubmissionController::class, 'index']);
    Route::post('/submissions',                   [SubmissionController::class, 'store']);
    Route::get('/submissions/{submission}',       [SubmissionController::class, 'show']);
    Route::patch('/submissions/{submission}',     [SubmissionController::class, 'update']);
    Route::delete('/submissions/{submission}',    [SubmissionController::class, 'destroy']);
    Route::post('/submissions/{submission}/abstracts', [AbstractController::class, 'store']);
    Route::post('/submissions/{submission}/documents', [DocumentSubmissionController::class, 'store']);
    Route::get('/submissions/{submission}/documents/{document}/download', [DocumentSubmissionController::class, 'download']);
    Route::patch('/submissions/{submission}/modality', [ModalityController::class, 'update']);
    Route::post('/submissions/{submission}/videos',        [VideoController::class, 'store']);
    Route::get('/submissions/{submission}/videos/status',  [VideoController::class, 'status']);
});

// ── Ponente + Participante (pagos e inscripciones) ────────────────────────

Route::middleware(['auth:sanctum', 'role:ponente|participante', 'throttle:60,1'])->group(function () {
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/registrations', [RegistrationController::class, 'index']);
});

// ── Revisor (auth + role + email verificado) ────────────────────────────────

Route::middleware(['auth:sanctum', 'role:revisor', 'throttle:60,1'])->group(function () {
    Route::get('/reviews',                              [ReviewController::class, 'index']);
    Route::get('/reviews/{review}',                     [ReviewController::class, 'show']);
    Route::patch('/reviews/{review}',                   [ReviewController::class, 'update']);
    Route::get('/reviews/{review}/document',            [ReviewController::class, 'downloadDocument']);
});

// ── Admin / Administrativo ───────────────────────────────────────────────

Route::middleware(['auth:sanctum', 'role:admin|administrativo', 'throttle:60,1'])->prefix('admin')->group(function () {
    Route::get('/submissions',                          [AdminSubmissionController::class, 'index']);
    Route::get('/reviewers',                            [AdminSubmissionController::class, 'reviewers']);
    Route::get('/submissions/{submission}',             [AdminSubmissionController::class, 'show']);
    Route::post('/submissions/{submission}/assign-reviewer',    [AdminSubmissionController::class, 'assignReviewer']);
    Route::get('/submissions/{submission}/video/stream',         [AdminSubmissionController::class, 'streamVideo']);
    Route::patch('/submissions/{submission}/video/approve',      [AdminSubmissionController::class, 'approveVideo']);
    Route::patch('/submissions/{submission}/video/reject',       [AdminSubmissionController::class, 'rejectVideo']);
    Route::apiResource('thematic-axes', ThematicAxisController::class);
});

// ── Webhooks (sin auth) ───────────────────────────────────────────────────

Route::post('/webhooks/cloudflare-video', [CloudflareVideoWebhookController::class, 'handle'])
    ->withoutMiddleware([ThrottleRequests::class]);
Route::post('/webhooks/payment', [PaymentController::class, 'webhook'])
    ->withoutMiddleware([ThrottleRequests::class]);
