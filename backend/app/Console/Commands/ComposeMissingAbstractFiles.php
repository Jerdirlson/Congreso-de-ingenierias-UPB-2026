<?php

namespace App\Console\Commands;

use App\Models\SubmissionAbstract;
use App\Services\AbstractTemplateComposerService;
use App\Services\SubmissionTrail;
use Illuminate\Console\Command;

class ComposeMissingAbstractFiles extends Command
{
    protected $signature = 'abstracts:compose-missing
        {--only= : Solo estas ponencias (IDs separados por coma)}
        {--force : Regenerar aunque ya exista un documento generado}';

    protected $description = 'Genera el documento en la plantilla oficial para los resúmenes sin archivo original guardado';

    public function handle(AbstractTemplateComposerService $composer): int
    {
        $query = SubmissionAbstract::whereNull('stored_path');

        if (! $this->option('force')) {
            $query->whereNull('generated_path');
        }
        if ($only = $this->option('only')) {
            $query->whereIn('submission_id', array_map('intval', explode(',', $only)));
        }

        $ok = 0;
        $conWarning = 0;
        $errores = 0;

        foreach ($query->orderBy('submission_id')->orderBy('version')->get() as $abstract) {
            try {
                $composer->compose($abstract);
                $problems = $abstract->template_problems ?? [];

                SubmissionTrail::log($abstract->submission_id, 'resumen_reconstruido', [
                    'version'             => $abstract->version,
                    'ruta'                => $abstract->generated_path,
                    'coincide_plantilla'  => $problems === [],
                ]);

                if ($problems === []) {
                    $ok++;
                    $this->line("[{$abstract->submission_id}] v{$abstract->version} OK");
                } else {
                    $conWarning++;
                    $this->warn("[{$abstract->submission_id}] v{$abstract->version} OK con advertencia: " . implode('; ', $problems));
                }
            } catch (\Throwable $e) {
                $errores++;
                $this->error("[{$abstract->submission_id}] v{$abstract->version} ERROR: {$e->getMessage()}");
            }
        }

        $this->info("Limpios: {$ok} · Con advertencia: {$conWarning} · Errores: {$errores}");

        return $errores === 0 ? self::SUCCESS : self::FAILURE;
    }
}
