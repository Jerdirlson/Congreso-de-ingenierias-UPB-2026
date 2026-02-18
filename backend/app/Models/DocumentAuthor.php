<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAuthor extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'document_id', 'speaker_id', 'user_id',
        'name', 'email', 'order', 'is_corresponding',
    ];

    protected $casts = [
        'is_corresponding' => 'boolean',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Speaker::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
