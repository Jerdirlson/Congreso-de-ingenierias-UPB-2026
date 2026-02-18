<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'color',
        'parent_id', 'sort_order',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(DocumentCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'category_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->orderBy('sort_order');
    }
}
