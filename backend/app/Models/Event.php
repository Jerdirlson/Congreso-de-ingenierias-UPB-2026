<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'slug', 'status',
        'start_date', 'end_date', 'location', 'venue',
        'cover_image', 'max_participants',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class, 'event_speaker')
                    ->withPivot('role', 'session_title');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function streams(): HasMany
    {
        return $this->hasMany(Stream::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now());
    }
}
