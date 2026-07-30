<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Submission;
use App\Models\SubmissionArticle;
use App\Models\SubmissionDocument;

/**
 * Reevalúa el estado de un resumen / documento / artículo a partir del conjunto
 * de revisiones que le quedan.
 *
 * Se usa en los dos momentos en que ese conjunto cambia: al completar un
 * dictamen y al quitar un revisor. Antes solo se reevaluaba al completar, así
 * que quitar al último revisor pendiente dejaba la ponencia congelada aunque
 * los revisores restantes ya hubieran aprobado.
 */
class ReviewOutcomeService
{
    /**
     * El resumen avanza cuando todos sus revisores actuales aprobaron.
     * Solo desde los estados previos: nunca arrastra hacia atrás una ponencia
     * que ya eligió modalidad, subió video o pagó.
     */
    public function syncAbstract(Submission $submission, ?int $abstractId): void
    {
        $advanceableFrom = [
            Submission::STATUS_ABSTRACT_SUBMITTED,
            Submission::STATUS_ABSTRACT_REJECTED,
        ];

        if (! in_array($submission->status, $advanceableFrom, true)) {
            return;
        }

        if ($this->allApproved($submission, 'submission_abstract_id', $abstractId, Review::TYPE_ABSTRACT)) {
            $submission->advanceTo(Submission::STATUS_ABSTRACT_APPROVED);
        }
    }

    /** Documento completo — flujo legado, se conserva para ponencias antiguas. */
    public function syncDocument(Submission $submission, ?int $documentId): void
    {
        $advanceableFrom = [
            Submission::STATUS_UNDER_REVIEW,
            Submission::STATUS_REVISION_REQUESTED,
        ];

        if (! in_array($submission->status, $advanceableFrom, true)) {
            return;
        }

        if ($this->allApproved($submission, 'submission_document_id', $documentId, Review::TYPE_DOCUMENT)) {
            $submission->advanceTo(Submission::STATUS_DOCUMENT_APPROVED);
            $submission->latestDocument?->update(['status' => SubmissionDocument::STATUS_APPROVED]);
        }
    }

    /**
     * El artículo es un carril paralelo: su dictamen solo cambia el estado del
     * artículo, nunca el de la ponencia (modalidad/video/pago siguen su curso).
     */
    public function syncArticle(Submission $submission, ?SubmissionArticle $article): void
    {
        if (! $article || $article->status === SubmissionArticle::STATUS_APPROVED) {
            return;
        }

        if ($this->allApproved($submission, 'submission_article_id', $article->id, Review::TYPE_ARTICLE)) {
            $article->update(['status' => SubmissionArticle::STATUS_APPROVED]);
        }
    }

    /**
     * ¿Están todas las revisiones de ESTA versión completas y aprobadas?
     * Se ignoran las versiones anteriores. Sin revisiones no hay aprobación:
     * el ítem sigue esperando asignación.
     */
    private function allApproved(Submission $submission, string $foreignKey, ?int $id, string $type): bool
    {
        if (! $id) {
            return false;
        }

        $reviews = $submission->reviews()
            ->where($foreignKey, $id)
            ->where('type', $type)
            ->get();

        return $reviews->isNotEmpty()
            && $reviews->every(fn ($r) => $r->status === Review::STATUS_COMPLETED)
            && $reviews->every(fn ($r) => $r->decision === Review::DECISION_APPROVED);
    }
}
