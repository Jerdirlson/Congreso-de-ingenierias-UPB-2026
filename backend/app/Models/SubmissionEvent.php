<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bitácora inmutable de trazabilidad por ponencia: subidas de archivos,
 * cambios de estado, asignaciones de revisión y dictámenes.
 * Solo se insertan filas (vía SubmissionTrail::log), nunca se editan.
 */
class SubmissionEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'submission_id',
        'user_id',
        'event',
        'details',
        'created_at',
    ];

    protected $casts = [
        'details'    => 'array',
        'created_at' => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
