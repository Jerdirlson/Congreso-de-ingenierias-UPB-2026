<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * GET /api/admin/ponentes/export — CSV con los datos de contacto de los ponentes.
 *
 * Pedido por la Decanatura (agosto 2026) para tener la base de datos de ponentes
 * con nombre, correo y celular. Se exporta una fila por ponente (no por ponencia)
 * con columnas de contexto para que se pueda filtrar en Excel.
 */
class AdminPonenteExportController extends Controller
{
    /** Estados que cuentan como "ponencia en firme" para la columna de resumen. */
    private const ESTADOS_EN_FIRME = [
        Submission::STATUS_DOCUMENT_APPROVED,
        Submission::STATUS_MODALITY_SELECTED,
        Submission::STATUS_VIDEO_PENDING,
        Submission::STATUS_VIDEO_READY,
        Submission::STATUS_PAYMENT_PENDING,
        Submission::STATUS_CONFIRMED,
    ];

    private const ESTADOS_LEGIBLES = [
        Submission::STATUS_DRAFT               => 'Borrador',
        Submission::STATUS_ABSTRACT_SUBMITTED  => 'Resumen enviado',
        Submission::STATUS_ABSTRACT_REJECTED   => 'Resumen rechazado',
        Submission::STATUS_ABSTRACT_APPROVED   => 'Resumen aprobado',
        Submission::STATUS_UNDER_REVIEW        => 'En revisión',
        Submission::STATUS_REVISION_REQUESTED  => 'Ajustes solicitados',
        Submission::STATUS_DOCUMENT_APPROVED   => 'Documento aprobado',
        Submission::STATUS_MODALITY_SELECTED   => 'Modalidad seleccionada',
        Submission::STATUS_VIDEO_PENDING       => 'Video pendiente',
        Submission::STATUS_VIDEO_READY         => 'Video listo',
        Submission::STATUS_PAYMENT_PENDING     => 'Pago pendiente',
        Submission::STATUS_CONFIRMED           => 'Confirmada',
    ];

    private const MODALIDADES_LEGIBLES = [
        Submission::MODALITY_PRESENCIAL_ORAL   => 'Presencial oral',
        Submission::MODALITY_PRESENCIAL_POSTER => 'Presencial póster',
        Submission::MODALITY_VIRTUAL           => 'Virtual',
        Submission::MODALITY_PROYECTO_AULA     => 'Proyecto de aula',
    ];

    private const COLUMNAS = [
        'Nombre', 'Correo', 'Celular', 'Documento', 'Institución', 'Ciudad', 'País',
        'N.º de ponencias', 'Ponencias en firme', 'Estados', 'Modalidades', 'Títulos',
        'Inscripción UPB', 'Correo verificado', 'Registrado el',
    ];

    public function __invoke(Request $request): StreamedResponse
    {
        // `solo_con_ponencia=1` deja por fuera a quien se registró pero nunca envió nada.
        $soloConPonencia = $request->boolean('solo_con_ponencia');

        $ponentes = User::role('ponente')
            ->with(['submissions' => fn ($q) => $q->orderBy('created_at')])
            ->when($soloConPonencia, fn ($q) => $q->has('submissions'))
            ->orderBy('name')
            ->get();

        Log::info('Export de ponentes descargado', [
            'admin_id'          => $request->user()->id,
            'total'             => $ponentes->count(),
            'solo_con_ponencia' => $soloConPonencia,
        ]);

        $nombreArchivo = 'ponentes-congreso-2026-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($ponentes) {
            $salida = fopen('php://output', 'w');

            // BOM UTF-8: sin esto Excel en Windows rompe las tildes y las eñes.
            fwrite($salida, "\xEF\xBB\xBF");

            // Separador `;`: es el que espera Excel con configuración regional de Colombia.
            // El `escape` va explícito porque en PHP 8.4 omitirlo está deprecado.
            fputcsv($salida, self::COLUMNAS, ';', '"', '\\');

            foreach ($ponentes as $ponente) {
                fputcsv($salida, $this->fila($ponente), ';', '"', '\\');
            }

            fclose($salida);
        }, $nombreArchivo, [
            'Content-Type'  => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /** @return list<string> */
    private function fila(User $ponente): array
    {
        $ponencias = $ponente->submissions;

        $documento = trim(($ponente->document_type ?? '') . ' ' . ($ponente->document_number ?? ''));

        $estados = $ponencias
            ->map(fn ($s) => self::ESTADOS_LEGIBLES[$s->status] ?? $s->status)
            ->unique()->values()->implode(' | ');

        $modalidades = $ponencias
            ->filter(fn ($s) => $s->modality !== null)
            ->map(fn ($s) => self::MODALIDADES_LEGIBLES[$s->modality] ?? $s->modality)
            ->unique()->values()->implode(' | ');

        return array_map($this->neutralizarFormula(...), [
            $ponente->name,
            $ponente->email,
            trim((string) $ponente->phone),
            $documento,
            $ponente->institution ?? '',
            $ponente->city ?? '',
            $ponente->country ?? '',
            (string) $ponencias->count(),
            (string) $ponencias->whereIn('status', self::ESTADOS_EN_FIRME)->count(),
            $estados,
            $modalidades,
            // Hay títulos guardados con saltos de línea: se aplanan para que cada
            // ponente ocupe una sola fila legible en Excel.
            $ponencias->pluck('title')->map($this->enUnaLinea(...))->implode(' | '),
            $this->inscripcion($ponente),
            $ponente->email_verified_at ? 'Sí' : 'No',
            $ponente->created_at?->format('Y-m-d') ?? '',
        ]);
    }

    /**
     * Los campos los escribe el usuario al registrarse, así que alguien podría meter
     * una fórmula (`=`, `@`, `\t`) que Excel ejecutaría al abrir el CSV. Se antepone
     * una comilla simple para que quede como texto.
     *
     * No se tocan `+` ni `-`: hay celulares que empiezan por `+` y alterarlos dañaría
     * el dato, que es justo lo que la Decanatura necesita poder copiar.
     */
    private function enUnaLinea(string $valor): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $valor));
    }

    private function neutralizarFormula(string $valor): string
    {
        return preg_match('/^[=@\t\r]/', $valor) === 1 ? "'" . $valor : $valor;
    }

    private function inscripcion(User $ponente): string
    {
        if ($ponente->external_registration_paid_at !== null) {
            return 'Pago verificado';
        }

        if ($ponente->external_registration_at !== null) {
            return 'Confirmada, pago sin verificar';
        }

        return 'Pendiente';
    }
}
