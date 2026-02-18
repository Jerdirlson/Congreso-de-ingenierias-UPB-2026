<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id', 'uploaded_by', 'version_number',
        'changelog', 'is_current', 'file_path', 'file_size',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'file_size'  => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /** Marks this version as current and demotes all others */
    public function setCurrent(): void
    {
        $this->document->versions()->update(['is_current' => false]);
        $this->update(['is_current' => true]);
    }
}
