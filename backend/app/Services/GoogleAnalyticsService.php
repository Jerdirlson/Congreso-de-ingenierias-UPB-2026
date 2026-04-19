<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleAnalyticsService
{
    private string $propertyId;
    private ?array $credentials;

    public function __construct()
    {
        $this->propertyId  = config('services.ga4.property_id', '');
        $raw               = config('services.ga4.service_account_json', '');
        $this->credentials = $raw ? json_decode($raw, true) : null;
    }

    public function isConfigured(): bool
    {
        return $this->propertyId !== ''
            && $this->credentials !== null
            && isset($this->credentials['private_key'], $this->credentials['client_email']);
    }

    // ── Auth ────────────────────────────────────────────────────────────────────

    private function getAccessToken(): string
    {
        return Cache::remember('ga4_access_token', 3500, function () {
            $now     = time();
            $header  = $this->base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $payload = $this->base64url(json_encode([
                'iss'   => $this->credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ]));

            $signingInput = "$header.$payload";
            openssl_sign($signingInput, $signature, $this->credentials['private_key'], 'SHA256');
            $jwt = "$signingInput.{$this->base64url($signature)}";

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            return $response->json('access_token', '');
        });
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // ── API call ─────────────────────────────────────────────────────────────────

    private function runReport(array $body): array
    {
        $token    = $this->getAccessToken();
        $response = Http::withToken($token)
            ->post("https://analyticsdata.googleapis.com/v1beta/properties/{$this->propertyId}:runReport", $body);

        if ($response->failed()) {
            Log::error('GA4 API error', ['status' => $response->status(), 'body' => $response->body()]);
            return [];
        }

        return $response->json() ?? [];
    }

    // ── Reports ──────────────────────────────────────────────────────────────────

    public function getSummary(): array
    {
        $data = $this->runReport([
            'dateRanges' => [['startDate' => '7daysAgo', 'endDate' => 'today']],
            'metrics'    => [
                ['name' => 'activeUsers'],
                ['name' => 'sessions'],
                ['name' => 'screenPageViews'],
                ['name' => 'bounceRate'],
                ['name' => 'averageSessionDuration'],
            ],
        ]);

        $v = $data['rows'][0]['metricValues'] ?? [];

        return [
            'active_users'         => (int)   ($v[0]['value'] ?? 0),
            'sessions'             => (int)   ($v[1]['value'] ?? 0),
            'page_views'           => (int)   ($v[2]['value'] ?? 0),
            'bounce_rate'          => round((float) ($v[3]['value'] ?? 0) * 100, 1),
            'avg_session_duration' => round((float) ($v[4]['value'] ?? 0)),
        ];
    }

    public function getDailyTrend(): array
    {
        $data = $this->runReport([
            'dateRanges' => [['startDate' => '6daysAgo', 'endDate' => 'today']],
            'dimensions' => [['name' => 'date']],
            'metrics'    => [['name' => 'sessions'], ['name' => 'activeUsers']],
            'orderBys'   => [['dimension' => ['dimensionName' => 'date']]],
        ]);

        return collect($data['rows'] ?? [])->map(fn ($row) => [
            'date'         => $row['dimensionValues'][0]['value'],
            'sessions'     => (int) $row['metricValues'][0]['value'],
            'active_users' => (int) $row['metricValues'][1]['value'],
        ])->values()->toArray();
    }

    public function getTopPages(): array
    {
        $data = $this->runReport([
            'dateRanges' => [['startDate' => '7daysAgo', 'endDate' => 'today']],
            'dimensions' => [['name' => 'pagePath']],
            'metrics'    => [['name' => 'screenPageViews']],
            'orderBys'   => [['metric' => ['metricName' => 'screenPageViews'], 'desc' => true]],
            'limit'      => 6,
        ]);

        return collect($data['rows'] ?? [])->map(fn ($row) => [
            'path'  => $row['dimensionValues'][0]['value'],
            'views' => (int) $row['metricValues'][0]['value'],
        ])->values()->toArray();
    }

    public function getDevices(): array
    {
        $data = $this->runReport([
            'dateRanges' => [['startDate' => '7daysAgo', 'endDate' => 'today']],
            'dimensions' => [['name' => 'deviceCategory']],
            'metrics'    => [['name' => 'sessions']],
            'orderBys'   => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
        ]);

        return collect($data['rows'] ?? [])->map(fn ($row) => [
            'device'   => $row['dimensionValues'][0]['value'],
            'sessions' => (int) $row['metricValues'][0]['value'],
        ])->values()->toArray();
    }

    public function getCountries(): array
    {
        $data = $this->runReport([
            'dateRanges' => [['startDate' => '7daysAgo', 'endDate' => 'today']],
            'dimensions' => [['name' => 'country']],
            'metrics'    => [['name' => 'sessions']],
            'orderBys'   => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
            'limit'      => 5,
        ]);

        return collect($data['rows'] ?? [])->map(fn ($row) => [
            'country'  => $row['dimensionValues'][0]['value'],
            'sessions' => (int) $row['metricValues'][0]['value'],
        ])->values()->toArray();
    }
}
