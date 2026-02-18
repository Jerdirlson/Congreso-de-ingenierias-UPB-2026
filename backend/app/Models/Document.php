<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'abstract', 'status', 'visibility',
        'category_id', 'event_id', 'uploaded_by',
        'file_type', 'file_size', 'page_count',
        'language', 'doi', 'publication_year',
        'download_count', 'view_count',
    ];

    protected $casts = [
        'file_size'        => 'integer',
        'page_count'       => 'integer',
        'download_count'   => 'integer',
        'view_count'       => 'integer',
        'publication_year' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderByDesc('created_at');
    }

    public function currentVersion(): HasOne
    {
        return $this->hasOne(DocumentVersion::class)->where('is_current', true);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(DocumentTag::class, 'document_tag', 'document_id', 'document_tag_id');
    }

    public function authors(): HasMany
    {
        return $this->hasMany(DocumentAuthor::class)->orderBy('order');
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function incrementDownloads(): void
    {
        $this->increment('download_count');
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }
}
