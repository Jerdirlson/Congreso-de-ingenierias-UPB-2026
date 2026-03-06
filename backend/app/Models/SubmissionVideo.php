<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionVideo extends Model
{
    use HasFactory;

    protected $table = 'submission_videos';

    public $timestamps = false;

    protected $fillable = [
        'submission_id',
        'stored_path',
        'original_filename',
        'mime_type',
        'file_size',
        'cloudflare_uid',
        'cloudflare_playback_url',
        'cloudflare_thumbnail_url',
        'duration_seconds',
        'status',
        'error_message',
        'uploaded_at',
        'ready_at',
    ];

    protected $casts = [
        'file_size'        => 'integer',
        'duration_seconds' => 'integer',
        'uploaded_at'      => 'datetime',
        'ready_at'         => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY      = 'ready';
    public const STATUS_ERROR      = 'error';
    public const STATUS_REJECTED   = 'rejected';
}
