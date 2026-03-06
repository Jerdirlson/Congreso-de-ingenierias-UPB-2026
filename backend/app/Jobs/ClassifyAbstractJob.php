<?php

namespace App\Jobs;

use App\Models\SubmissionAbstract;
use App\Models\ThematicAxis;
use App\Services\LlmClassificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ClassifyAbstractJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly SubmissionAbstract $abstract
    ) {}

    public function handle(LlmClassificationService $llm): void
    {
        $axes = ThematicAxis::active()->get();
        if ($axes->isEmpty()) {
            $this->markRejected('No hay ejes temáticos activos.');
            return;
        }

        $result = $llm->classify($this->abstract->content, $axes);

        $approved = $llm->isApproved($result['confidence_score'])
            && $result['axis_id'] !== null;

        $this->abstract->update([
            'llm_status'           => $approved ? 'approved' : 'rejected',
            'llm_axis_id'         => $result['axis_id'],
            'llm_confidence_score' => $result['confidence_score'],
            'llm_justification'    => $result['justification'],
            'llm_raw_response'    => $result['raw_response'],
            'processed_at'        => now(),
        ]);

        $submission = $this->abstract->submission;
        $submission->advanceTo(
            $approved ? 'abstract_approved' : 'abstract_rejected'
        );
        if ($approved && $result['axis_id']) {
            $submission->update(['thematic_axis_id' => $result['axis_id']]);
        }
    }

    private function markRejected(string $reason): void
    {
        $this->abstract->update([
            'llm_status'        => 'rejected',
            'llm_justification' => $reason,
            'processed_at'      => now(),
        ]);
        $this->abstract->submission->advanceTo('abstract_rejected');
    }
}
