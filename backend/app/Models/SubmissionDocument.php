<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmissionDocument extends Model
{
    use HasFactory;

    protected $table = 'submission_documents';

    public $timestamps = false;

    protected $fillable = [
        'submission_id',
        'version',
        'original_filename',
        'stored_path',
        'file_size',
        'mime_type',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'file_size'    => 'integer',
        'submitted_at' => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'submission_document_id');
    }

    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_REVISION_REQUESTED = 'revision_requested';
    public const STATUS_APPROVED = 'approved';
}
