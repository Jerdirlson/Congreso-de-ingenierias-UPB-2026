<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Review;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminMetricsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'users'         => $this->userMetrics(),
            'submissions'   => $this->submissionMetrics(),
            'reviews'       => $this->reviewMetrics(),
            'registrations' => $this->registrationMetrics(),
            'payments'      => $this->paymentMetrics(),
            'generated_at'  => now()->toIso8601String(),
        ]);
    }

    private function userMetrics(): array
    {
        $total = User::count();

        // Conteo por rol via Spatie permission tables
        $byRole = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('roles.name', DB::raw('count(*) as count'))
            ->groupBy('roles.name')
            ->pluck('count', 'name')
            ->toArray();

        $newLast7Days  = User::where('created_at', '>=', now()->subDays(7))->count();
        $newLast30Days = User::where('created_at', '>=', now()->subDays(30))->count();

        // Usuarios con email verificado
        $verified = User::whereNotNull('email_verified_at')->count();

        return [
            'total'           => $total,
            'by_role'         => $byRole,
            'new_last_7_days'  => $newLast7Days,
            'new_last_30_days' => $newLast30Days,
            'verified'         => $verified,
        ];
    }

    private function submissionMetrics(): array
    {
        $total = Submission::count();

        $byStatus = Submission::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $byModality = Submission::whereNotNull('modality')
            ->select('modality', DB::raw('count(*) as count'))
            ->groupBy('modality')
            ->pluck('count', 'modality')
            ->toArray();

        $byAxis = DB::table('submissions')
            ->join('thematic_axes', 'thematic_axes.id', '=', 'submissions.thematic_axis_id')
            ->whereNull('submissions.deleted_at')
            ->whereNotNull('submissions.thematic_axis_id')
            ->select('thematic_axes.name', DB::raw('count(*) as count'))
            ->groupBy('thematic_axes.id', 'thematic_axes.name')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'count' => $r->count])
            ->toArray();

        // Tendencia últimos 30 días: una entrada por día
        $rawTrend = DB::table('submissions')
            ->whereNull('deleted_at')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Completar los 30 días aunque no haya datos
        $trend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $trend[] = ['date' => $date, 'count' => $rawTrend[$date] ?? 0];
        }

        return [
            'total'         => $total,
            'by_status'     => $byStatus,
            'by_modality'   => $byModality,
            'by_axis'       => $byAxis,
            'trend_last_30' => $trend,
        ];
    }

    private function reviewMetrics(): array
    {
        $total = Review::count();

        $byStatus = Review::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $completed = $byStatus[Review::STATUS_COMPLETED] ?? 0;
        $approved  = Review::where('status', Review::STATUS_COMPLETED)
            ->where('decision', Review::DECISION_APPROVED)
            ->count();

        $approvalRate = $completed > 0 ? round(($approved / $completed) * 100) : 0;

        // Tiempo promedio de revisión en días
        $avgDays = Review::where('status', Review::STATUS_COMPLETED)
            ->whereNotNull('assigned_at')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, assigned_at, completed_at)) / 24 AS avg_days')
            ->value('avg_days');

        return [
            'total'         => $total,
            'by_status'     => $byStatus,
            'approved'      => $approved,
            'approval_rate' => $approvalRate,
            'avg_days'      => round((float) ($avgDays ?? 0), 1),
        ];
    }

    private function registrationMetrics(): array
    {
        $total = Registration::count();

        $byType = Registration::select('registration_type', DB::raw('count(*) as count'))
            ->groupBy('registration_type')
            ->pluck('count', 'registration_type')
            ->toArray();

        $byModality = Registration::select('modality', DB::raw('count(*) as count'))
            ->groupBy('modality')
            ->pluck('count', 'modality')
            ->toArray();

        $confirmed = Registration::whereNotNull('confirmed_at')->count();
        $attended  = Registration::where('attended', true)->count();

        return [
            'total'       => $total,
            'by_type'     => $byType,
            'by_modality' => $byModality,
            'confirmed'   => $confirmed,
            'attended'    => $attended,
        ];
    }

    private function paymentMetrics(): array
    {
        $total = Payment::count();

        $byStatus = Payment::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $revenue  = (float) Payment::where('status', Payment::STATUS_COMPLETED)->sum('amount');
        $currency = Payment::where('status', Payment::STATUS_COMPLETED)->value('currency') ?? 'COP';

        return [
            'total'     => $total,
            'by_status' => $byStatus,
            'revenue'   => $revenue,
            'currency'  => $currency,
        ];
    }
}
