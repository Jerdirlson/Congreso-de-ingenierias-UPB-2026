<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Registration extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'payment_id',
        'submission_id',
        'congress_event_id',
        'registration_type',
        'modality',
        'ticket_code',
        'confirmed_at',
        'attended',
    ];

    protected $casts = [
        'attended'    => 'boolean',
        'confirmed_at' => 'datetime',
        'created_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function congressEvent(): BelongsTo
    {
        return $this->belongsTo(CongressEvent::class);
    }

    public const REGISTRATION_TYPE_PARTICIPANT = 'participant';
    public const REGISTRATION_TYPE_SPEAKER = 'speaker';

    public const MODALITY_PRESENCIAL = 'presencial';
    public const MODALITY_VIRTUAL = 'virtual';
    public const MODALITY_PROYECTO_AULA = 'proyecto_aula';

    public static function generateTicketCode(): string
    {
        return strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
    }
}
