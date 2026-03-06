<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThematicAxis extends Model
{
    use HasFactory;

    protected $table = 'thematic_axes';

    protected $fillable = [
        'name',
        'description',
        'keywords',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'thematic_axis_id');
    }

    public function submissionAbstracts(): HasMany
    {
        return $this->hasMany(SubmissionAbstract::class, 'llm_axis_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
