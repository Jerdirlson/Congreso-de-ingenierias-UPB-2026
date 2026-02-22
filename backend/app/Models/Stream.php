<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Stream extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'description', 'status',
        'event_id', 'speaker_id', 'created_by',
        'scheduled_at', 'started_at', 'ended_at',
        'stream_key', 'rtmp_url', 'hls_url',
        'platform', 'platform_url', 'type',
        'peak_viewers', 'total_views', 'chat_enabled',
        'thumbnail',
        'cloudflare_uid', 'cloudflare_video_uid', 'cloudflare_meta',
    ];

    protected $casts = [
        'scheduled_at'   => 'datetime',
        'started_at'     => 'datetime',
        'ended_at'       => 'datetime',
        'chat_enabled'   => 'boolean',
        'peak_viewers'   => 'integer',
        'total_views'    => 'integer',
        'cloudflare_meta' => 'array',
    ];

    protected $hidden = [
        'stream_key',
        'cloudflare_meta',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Speaker::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(StreamRecording::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeLive($query)
    {
        return $query->where('status', 'live');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function goLive(): void
    {
        $this->update([
            'status'     => 'live',
            'started_at' => now(),
        ]);
    }

    public function end(): void
    {
        $this->update([
            'status'   => 'ended',
            'ended_at' => now(),
        ]);
    }

    public function getDurationAttribute(): ?int
    {
        if ($this->started_at && $this->ended_at) {
            return $this->ended_at->diffInSeconds($this->started_at);
        }
        return null;
    }

    /** Generate a unique stream key */
    public static function generateStreamKey(): string
    {
        return Str::upper(Str::random(4)) . '-' . Str::upper(Str::random(4));
    }

    public function isCloudflare(): bool
    {
        return $this->platform === 'cloudflare' && $this->cloudflare_uid !== null;
    }

    public function getPlaybackUrlAttribute(): ?string
    {
        if ($this->isCloudflare() && $this->cloudflare_video_uid) {
            return "https://iframe.videodelivery.net/{$this->cloudflare_video_uid}";
        }

        return $this->hls_url ?? $this->platform_url;
    }
}
