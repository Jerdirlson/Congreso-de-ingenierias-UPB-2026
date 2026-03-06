<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionAbstract extends Model
{
    use HasFactory;

    protected $table = 'submission_abstracts';

    public $timestamps = false;

    protected $fillable = [
        'submission_id',
        'llm_axis_id',
        'content',
        'version',
        'llm_status',
        'llm_confidence_score',
        'llm_justification',
        'llm_raw_response',
        'processed_at',
    ];

    protected $casts = [
        'llm_confidence_score' => 'decimal:2',
        'llm_raw_response'     => 'array',
        'processed_at'         => 'datetime',
        'created_at'           => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function llmAxis(): BelongsTo
    {
        return $this->belongsTo(ThematicAxis::class, 'llm_axis_id');
    }

    public const LLM_STATUS_PENDING = 'pending';
    public const LLM_STATUS_APPROVED = 'approved';
    public const LLM_STATUS_REJECTED = 'rejected';
}
