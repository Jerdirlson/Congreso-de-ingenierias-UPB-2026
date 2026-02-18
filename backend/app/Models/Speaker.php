<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Speaker extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'email', 'institution',
        'country', 'bio', 'photo', 'linkedin', 'orcid',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_speaker')
                    ->withPivot('role', 'session_title');
    }

    public function streams(): HasMany
    {
        return $this->hasMany(Stream::class);
    }

    public function documentAuthorships(): HasMany
    {
        return $this->hasMany(DocumentAuthor::class);
    }
}
