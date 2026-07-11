<?php

use App\Http\Controllers\Api\AbstractController;
use App\Http\Controllers\Api\ArticleSubmissionController;
use App\Http\Controllers\Api\AdminAnalyticsController;
use App\Http\Controllers\Api\AdminMetricsController;
use App\Http\Controllers\Api\AdminSubmissionController;
use App\Http\Controllers\Api\AdminImpersonateController;
use App\Http\Controllers\Api\AdminMailController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AxisConfirmationController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\CloudflareVideoWebhookController;
use App\Http\Controllers\Api\CongressEventController;
use App\Http\Controllers\Api\DocumentSubmissionController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PublicDocController;
use App\Http\Controllers\Api\ModalityController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SettingsController;
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

Route::get('/docs/{filename}', [PublicDocController::class, 'download'])->middleware('throttle:30,1');

Route::middleware('throttle:120,1')->group(function () {
    Route::get('/thematic-axes', [ThematicAxisController::class, 'index']);
    Route::get('/events',        [CongressEventController::class, 'index']);
    Route::get('/settings',      [SettingsController::class, 'publicSettings']);
});

// ── Auth (público) ────────────────────────────────────────────────────────

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me',        [AuthController::class, 'me'])->middleware('auth:sanctum');
    Route::patch('/me/password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
    Route::post('/me/confirm-external-registration', [AuthController::class, 'confirmExternalRegistration'])->middleware('auth:sanctum');
});

// ── Restablecer contraseña por código (olvidé mi contraseña) ──────────────────

// Solicitar código: máximo 3 solicitudes/min (evita abuso y enumeración)
Route::post('/password/forgot', [PasswordResetController::class, 'sendCode'])
    ->middleware('throttle:3,1')
    ->name('password.forgot');

// Verificar código (paso intermedio): máximo 10 intentos/min
Route::post('/password/verify-code', [PasswordResetController::class, 'verifyCode'])
    ->middleware('throttle:10,1')
    ->name('password.verify-code');

// Restablecer contraseña: máximo 10 intentos/min
Route::post('/password/reset', [PasswordResetController::class, 'reset'])
    ->middleware('throttle:10,1')
    ->name('password.reset');

// ── Verificación de correo por código ─────────────────────────────────────────

// Verificar código: máximo 10 intentos/min (permite corregir errores de tipeo)
Route::post('/email/verify-code', [EmailVerificationController::class, 'verifyCode'])
    ->middleware('throttle:10,1')
    ->name('verification.verify');

// Reenviar código: máximo 3 solicitudes/min (evita abuso)
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
    ->middleware('throttle:3,1')
    ->name('verification.send');

// ── Ponente (auth + role + email verificado) ──────────────────────────────

Route::middleware(['auth:sanctum', 'verified', 'role:ponente', 'throttle:60,1'])->group(function () {
    Route::get('/submissions',                    [SubmissionController::class, 'index']);
    Route::post('/submissions',                   [SubmissionController::class, 'store']);
    Route::get('/submissions/{submission}',       [SubmissionController::class, 'show']);
    Route::patch('/submissions/{submission}',     [SubmissionController::class, 'update']);
    Route::delete('/submissions/{submission}',    [SubmissionController::class, 'destroy']);
    Route::post('/submissions/{submission}/abstracts',  [AbstractController::class, 'store']);
    Route::patch('/submissions/{submission}/axis',      [AxisConfirmationController::class, 'update']);
    Route::post('/submissions/{submission}/documents', [DocumentSubmissionController::class, 'store']);
    Route::get('/submissions/{submission}/documents/{document}/download', [DocumentSubmissionController::class, 'download']);
    Route::post('/submissions/{submission}/journal-opt-in',   [ArticleSubmissionController::class, 'optIn']);
    Route::delete('/submissions/{submission}/journal-opt-in', [ArticleSubmissionController::class, 'optOut']);
    Route::post('/submissions/{submission}/articles',         [ArticleSubmissionController::class, 'store']);
    Route::get('/submissions/{submission}/articles/{article}/download', [ArticleSubmissionController::class, 'download']);
    Route::patch('/submissions/{submission}/modality', [ModalityController::class, 'update']);
    Route::post('/submissions/{submission}/videos',        [VideoController::class, 'store']);
    Route::get('/submissions/{submission}/videos/status',  [VideoController::class, 'status']);
});

// ── Ponente + Participante (pagos e inscripciones) ────────────────────────

Route::middleware(['auth:sanctum', 'verified', 'role:ponente|participante', 'throttle:60,1'])->group(function () {
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
    Route::get('/metrics',                              AdminMetricsController::class);
    Route::get('/analytics',                            AdminAnalyticsController::class);
    Route::get('/submissions',                          [AdminSubmissionController::class, 'index']);
    Route::get('/reviewers',                            [AdminSubmissionController::class, 'reviewers']);
    Route::get('/submissions/{submission}',             [AdminSubmissionController::class, 'show']);
    Route::get('/submissions/{submission}/abstracts/{abstract}/download', [AdminSubmissionController::class, 'downloadAbstractFile']);
    Route::patch('/submissions/{submission}/abstract/approve',           [AdminSubmissionController::class, 'approveAbstract']);
    Route::patch('/submissions/{submission}/abstract/reject',            [AdminSubmissionController::class, 'rejectAbstract']);
    Route::post('/submissions/{submission}/assign-reviewer',             [AdminSubmissionController::class, 'assignReviewer']);
    Route::post('/submissions/{submission}/assign-abstract-reviewer',    [AdminSubmissionController::class, 'assignAbstractReviewer']);
    Route::post('/submissions/{submission}/assign-article-reviewer',     [AdminSubmissionController::class, 'assignArticleReviewer']);
    Route::get('/submissions/{submission}/articles/{article}/download',  [ArticleSubmissionController::class, 'download']);
    Route::delete('/submissions/{submission}/reviews/{review}',          [AdminSubmissionController::class, 'removeReview']);
    Route::get('/submissions/{submission}/documents/{document}/download', [DocumentSubmissionController::class, 'download']);
    Route::get('/submissions/{submission}/video/stream',         [AdminSubmissionController::class, 'streamVideo']);
    Route::patch('/submissions/{submission}/video/approve',      [AdminSubmissionController::class, 'approveVideo']);
    Route::patch('/submissions/{submission}/video/reject',       [AdminSubmissionController::class, 'rejectVideo']);
    Route::apiResource('thematic-axes', ThematicAxisController::class);
    Route::get('/users',                         [AdminUserController::class, 'index']);
    Route::get('/users/{user}',                  [AdminUserController::class, 'show']);
    Route::patch('/users/{user}/role',           [AdminUserController::class, 'updateRole']);
    Route::post('/users/{user}/assign-reviewer', [AdminUserController::class, 'assignReviewer']);
    Route::delete('/users/{user}/remove-reviewer', [AdminUserController::class, 'removeReviewer']);
    Route::post('/users/{user}/impersonate',     [AdminImpersonateController::class, 'store']);
    Route::post('/mail/preview',        [AdminMailController::class, 'preview']);
    Route::post('/mail/send',           [AdminMailController::class, 'send']);
    Route::get('/settings',             [SettingsController::class, 'publicSettings']);
    Route::put('/settings',             [SettingsController::class, 'update']);
});

// ── Webhooks (sin auth) ───────────────────────────────────────────────────

Route::post('/webhooks/cloudflare-video', [CloudflareVideoWebhookController::class, 'handle'])
    ->withoutMiddleware([ThrottleRequests::class]);
Route::post('/webhooks/payment', [PaymentController::class, 'webhook'])
    ->withoutMiddleware([ThrottleRequests::class]);
