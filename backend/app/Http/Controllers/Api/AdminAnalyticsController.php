<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleAnalyticsService;
use Illuminate\Http\JsonResponse;

class AdminAnalyticsController extends Controller
{
    public function __invoke(GoogleAnalyticsService $ga): JsonResponse
    {
        if (! $ga->isConfigured()) {
            return response()->json(['configured' => false]);
        }

        return response()->json([
            'configured' => true,
            'summary'    => $ga->getSummary(),
            'trend'      => $ga->getDailyTrend(),
            'top_pages'  => $ga->getTopPages(),
            'devices'    => $ga->getDevices(),
            'countries'  => $ga->getCountries(),
        ]);
    }
}
