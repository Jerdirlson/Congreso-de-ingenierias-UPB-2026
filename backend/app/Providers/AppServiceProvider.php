<?php

namespace App\Providers;

use App\Models\Review;
use App\Models\Submission;
use App\Models\SubmissionAbstract;
use App\Models\SubmissionArticle;
use App\Models\SubmissionDocument;
use App\Models\SubmissionVideo;
use App\Services\SubmissionTrail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerSubmissionTrail();
    }

    /**
     * Trazabilidad: hooks de modelo que registran en la bitácora
     * (submission_events) cada suceso relevante de una ponencia.
     * Al vivir en los modelos —y no en los controladores— ningún flujo
     * puede saltarse el registro.
     */
    private function registerSubmissionTrail(): void
    {
        Submission::created(function (Submission $s) {
            SubmissionTrail::log($s->id, 'ponencia_creada', [
                'titulo' => $s->title,
                'estado' => $s->status,
            ]);
        });

        Submission::updated(function (Submission $s) {
            if ($s->wasChanged('status')) {
                SubmissionTrail::log($s->id, 'estado_cambiado', [
                    'de' => $s->getOriginal('status'),
                    'a'  => $s->status,
                ]);
            }
            if ($s->wasChanged('modality')) {
                SubmissionTrail::log($s->id, 'modalidad_elegida', [
                    'de' => $s->getOriginal('modality'),
                    'a'  => $s->modality,
                ]);
            }
        });

        Submission::deleted(function (Submission $s) {
            SubmissionTrail::log($s->id, 'ponencia_eliminada', ['titulo' => $s->title]);
        });

        SubmissionAbstract::created(function (SubmissionAbstract $a) {
            SubmissionTrail::log($a->submission_id, 'resumen_subido', [
                'version' => $a->version,
                'archivo' => $a->original_filename,
                'ruta'    => $a->stored_path,
            ]);
        });

        SubmissionDocument::created(function (SubmissionDocument $d) {
            SubmissionTrail::log($d->submission_id, 'documento_subido', [
                'version' => $d->version,
                'archivo' => $d->original_filename,
                'ruta'    => $d->stored_path,
            ]);
        });

        SubmissionArticle::created(function (SubmissionArticle $a) {
            SubmissionTrail::log($a->submission_id, 'articulo_subido', [
                'version' => $a->version,
                'archivo' => $a->original_filename,
                'ruta'    => $a->stored_path,
            ]);
        });

        SubmissionVideo::created(function (SubmissionVideo $v) {
            SubmissionTrail::log($v->submission_id, 'video_subido', [
                'archivo' => $v->original_filename,
                'ruta'    => $v->stored_path,
            ]);
        });

        SubmissionVideo::updated(function (SubmissionVideo $v) {
            if ($v->wasChanged('stored_path') && $v->getOriginal('stored_path')) {
                SubmissionTrail::log($v->submission_id, 'video_reemplazado', [
                    'archivo'          => $v->original_filename,
                    'ruta'             => $v->stored_path,
                    'archivo_anterior' => $v->getOriginal('original_filename'),
                    'ruta_anterior'    => $v->getOriginal('stored_path'),
                ]);
            }
        });

        Review::created(function (Review $r) {
            SubmissionTrail::log($r->submission_id, 'revision_asignada', [
                'tipo'    => $r->type,
                'revisor' => $r->reviewer?->name,
            ], $r->assigned_at);
        });

        Review::updated(function (Review $r) {
            if ($r->wasChanged('status') && $r->status === Review::STATUS_COMPLETED) {
                SubmissionTrail::log($r->submission_id, 'revision_completada', [
                    'tipo'     => $r->type,
                    'revisor'  => $r->reviewer?->name,
                    'decision' => $r->decision,
                ]);
            }
        });
    }
}
