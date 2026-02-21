<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            $checks = [
                'database' => $this->checkDatabase(),
                'redis'    => $this->checkRedis(),
            ];

            $healthy = collect($checks)->every(fn ($c) => $c['status'] === 'ok');

            return response()->json([
                'status'    => $healthy ? 'ok' : 'degraded',
                'service'   => 'congreso-ingenierias-2026-api',
                'timestamp' => now()->toISOString(),
                'checks'    => $checks,
            ], $healthy ? 200 : 503);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'service' => 'congreso-ingenierias-2026-api',
            ], 503);
        }
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'ok'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkRedis(): array
    {
        try {
            Redis::ping();
            return ['status' => 'ok'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
