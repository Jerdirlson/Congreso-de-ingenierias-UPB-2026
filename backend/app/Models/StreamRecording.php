<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamRecording extends Model
{
    use HasFactory;

    protected $fillable = [
        'stream_id', 'title', 'file_path', 'file_size',
        'duration', 'format', 'resolution', 'status',
        'thumbnail', 'view_count', 'visibility',
    ];

    protected $casts = [
        'file_size'  => 'integer',
        'duration'   => 'integer',
        'view_count' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function getDurationFormattedAttribute(): string
    {
        if (! $this->duration) return '0:00';
        $h = intdiv($this->duration, 3600);
        $m = intdiv($this->duration % 3600, 60);
        $s = $this->duration % 60;
        return $h > 0
            ? sprintf('%d:%02d:%02d', $h, $m, $s)
            : sprintf('%d:%02d', $m, $s);
    }
}
