<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CongressEvent extends Model
{
    protected $fillable = [
        'name',
        'description',
        'location',
        'modality',
        'event_date',
        'start_time',
        'end_time',
        'speaker',
        'category',
        'capacity',
        'price',
        'currency',
        'is_free',
        'is_active',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_free'    => 'boolean',
        'is_active'  => 'boolean',
        'price'      => 'decimal:2',
        'capacity'   => 'integer',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function registrationCount(): int
    {
        return $this->registrations()->count();
    }

    public function isFull(): bool
    {
        if (! $this->capacity) return false;
        return $this->registrationCount() >= $this->capacity;
    }
}
