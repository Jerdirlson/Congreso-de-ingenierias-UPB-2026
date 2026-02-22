<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareStreamService
{
    private string $accountId;
    private string $baseUrl;

    public function __construct()
    {
        $this->accountId = config('services.cloudflare_stream.account_id');
        $this->baseUrl   = config('services.cloudflare_stream.base_url');
    }

    // ── Live Inputs ─────────────────────────────────────────────────────────

    /**
     * Create a new Live Input in Cloudflare Stream.
     * Returns the full API response with RTMP URL, stream key, etc.
     */
    public function createLiveInput(string $name, array $options = []): ?array
    {
        $payload = [
            'meta'                    => ['name' => $name],
            'recording'               => ['mode' => $options['recording_mode'] ?? 'automatic'],
            'deleteRecordingAfterDays' => $options['delete_after_days'] ?? null,
        ];

        $response = $this->client()->post(
            "{$this->accountUrl()}/stream/live_inputs",
            array_filter($payload)
        );

        if (! $response->successful()) {
            Log::error('Cloudflare: Failed to create live input', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);
            return null;
        }

        return $response->json('result');
    }

    /**
     * Get details of a Live Input by its UID.
     */
    public function getLiveInput(string $uid): ?array
    {
        $response = $this->client()->get("{$this->accountUrl()}/stream/live_inputs/{$uid}");

        return $response->successful() ? $response->json('result') : null;
    }

    /**
     * Update a Live Input.
     */
    public function updateLiveInput(string $uid, array $data): ?array
    {
        $response = $this->client()->put(
            "{$this->accountUrl()}/stream/live_inputs/{$uid}",
            $data
        );

        return $response->successful() ? $response->json('result') : null;
    }

    /**
     * Delete a Live Input.
     */
    public function deleteLiveInput(string $uid): bool
    {
        $response = $this->client()->delete("{$this->accountUrl()}/stream/live_inputs/{$uid}");

        if (! $response->successful()) {
            Log::error('Cloudflare: Failed to delete live input', [
                'uid'    => $uid,
                'status' => $response->status(),
            ]);
        }

        return $response->successful();
    }

    // ── Videos / Recordings ─────────────────────────────────────────────────

    /**
     * List videos associated with a Live Input.
     */
    public function listVideos(string $liveInputUid): ?array
    {
        $response = $this->client()->get(
            "{$this->accountUrl()}/stream/live_inputs/{$liveInputUid}/videos"
        );

        return $response->successful() ? $response->json('result') : null;
    }

    /**
     * Get details of a specific video/recording.
     */
    public function getVideo(string $videoUid): ?array
    {
        $response = $this->client()->get("{$this->accountUrl()}/stream/{$videoUid}");

        return $response->successful() ? $response->json('result') : null;
    }

    /**
     * Delete a video/recording.
     */
    public function deleteVideo(string $videoUid): bool
    {
        $response = $this->client()->delete("{$this->accountUrl()}/stream/{$videoUid}");

        return $response->successful();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Build playback URLs for a video.
     */
    public function getPlaybackUrls(string $videoUid): array
    {
        return [
            'hls'    => "https://customer-{$this->getSubdomain()}.cloudflarestream.com/{$videoUid}/manifest/video.m3u8",
            'dash'   => "https://customer-{$this->getSubdomain()}.cloudflarestream.com/{$videoUid}/manifest/video.mpd",
            'iframe' => "https://customer-{$this->getSubdomain()}.cloudflarestream.com/{$videoUid}/iframe",
        ];
    }

    /**
     * Build embed iframe HTML for the Cloudflare Stream player.
     */
    public function getEmbedHtml(string $videoUid, array $options = []): string
    {
        $autoplay = ($options['autoplay'] ?? false) ? 'autoplay' : '';
        $muted    = ($options['muted'] ?? true) ? 'muted' : '';
        $loop     = ($options['loop'] ?? false) ? 'loop' : '';

        return sprintf(
            '<iframe src="https://customer-%s.cloudflarestream.com/%s/iframe" style="border: none; width: 100%%; aspect-ratio: 16/9;" allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;" allowfullscreen %s %s %s></iframe>',
            $this->getSubdomain(),
            $videoUid,
            $autoplay,
            $muted,
            $loop,
        );
    }

    /**
     * Verify webhook signature from Cloudflare.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = config('services.cloudflare_stream.webhook_secret');

        if (! $secret) {
            return false;
        }

        $computed = hash_hmac('sha256', $payload, $secret);

        return hash_equals($computed, $signature);
    }

    /**
     * Check if Cloudflare Stream is properly configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->accountId)
            && ! empty(config('services.cloudflare_stream.api_token'));
    }

    // ── Private ─────────────────────────────────────────────────────────────

    private function client(): PendingRequest
    {
        return Http::withToken(config('services.cloudflare_stream.api_token'))
            ->acceptJson()
            ->timeout(30);
    }

    private function accountUrl(): string
    {
        return "{$this->baseUrl}/accounts/{$this->accountId}";
    }

    /**
     * Retrieve the customer subdomain for playback URLs.
     * Cached for the lifetime of the request.
     */
    private function getSubdomain(): string
    {
        static $subdomain = null;

        if ($subdomain === null) {
            $subdomain = cache()->remember('cf_stream_subdomain', 86400, function () {
                $response = $this->client()->get("{$this->accountUrl()}/stream");

                return $response->successful()
                    ? ($response->json('result.0.preview') ? parse_url($response->json('result.0.preview'), PHP_URL_HOST) : $this->accountId)
                    : $this->accountId;
            });
        }

        return $subdomain;
    }
}
