<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'thematic_axis_id',
        'title',
        'status',
        'modality',
        'abstract_attempts',
        'document_version',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ABSTRACT_SUBMITTED = 'abstract_submitted';
    public const STATUS_ABSTRACT_REJECTED = 'abstract_rejected';
    public const STATUS_ABSTRACT_APPROVED = 'abstract_approved';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_REVISION_REQUESTED = 'revision_requested';
    public const STATUS_DOCUMENT_APPROVED = 'document_approved';
    public const STATUS_MODALITY_SELECTED = 'modality_selected';
    public const STATUS_VIDEO_PENDING = 'video_pending';
    public const STATUS_VIDEO_READY = 'video_ready';
    public const STATUS_PAYMENT_PENDING = 'payment_pending';
    public const STATUS_CONFIRMED = 'confirmed';

    public const MODALITY_PRESENCIAL_ORAL = 'presencial_oral';
    public const MODALITY_PRESENCIAL_POSTER = 'presencial_poster';
    public const MODALITY_VIRTUAL = 'virtual';
    public const MODALITY_PROYECTO_AULA = 'proyecto_aula';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function thematicAxis(): BelongsTo
    {
        return $this->belongsTo(ThematicAxis::class, 'thematic_axis_id');
    }

    public function abstracts(): HasMany
    {
        return $this->hasMany(SubmissionAbstract::class)->orderByDesc('version');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SubmissionDocument::class)->orderByDesc('version');
    }

    public function latestDocument(): HasOne
    {
        return $this->hasOne(SubmissionDocument::class)->latestOfMany('version');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function video(): HasOne
    {
        return $this->hasOne(SubmissionVideo::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function advanceTo(string $status): void
    {
        $this->update(['status' => $status]);
    }
}
