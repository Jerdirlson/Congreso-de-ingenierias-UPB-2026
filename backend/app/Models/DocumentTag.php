<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DocumentTag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_tag', 'document_tag_id', 'document_id');
    }
}
