<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubmissionVideo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CloudflareVideoWebhookController extends Controller
{
    /** POST /api/webhooks/cloudflare-video — cuando un video de Stream está listo */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $uid = $payload['uid'] ?? $payload['meta']['submission_id'] ?? null;

        if (! $uid) {
            Log::warning('Cloudflare video webhook: no UID in payload', ['payload' => $payload]);
            return response()->json(['ok' => false], 400);
        }

        $video = SubmissionVideo::where('cloudflare_uid', $uid)->first();
        if (! $video) {
            $video = SubmissionVideo::where('submission_id', $uid)->first();
        }
        if (! $video) {
            Log::warning('Cloudflare video webhook: video not found', ['uid' => $uid]);
            return response()->json(['ok' => false], 404);
        }

        $status = $payload['status']['state'] ?? $payload['status'] ?? 'ready';

        if (in_array($status, ['ready', 'complete'])) {
            $video->update([
                'status'     => SubmissionVideo::STATUS_READY,
                'ready_at'   => now(),
                'duration_seconds' => $payload['duration'] ?? $payload['meta']['duration'] ?? null,
            ]);
            $video->submission->advanceTo('video_ready');
        } elseif ($status === 'error') {
            $video->update([
                'status'       => SubmissionVideo::STATUS_ERROR,
                'error_message' => $payload['error'] ?? $payload['message'] ?? 'Unknown error',
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
