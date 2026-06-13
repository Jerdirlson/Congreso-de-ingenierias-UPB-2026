<?php

namespace App\Models;

use App\Notifications\EmailVerificationCode;
use App\Notifications\PasswordResetCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'document_type',
        'document_number',
        'institution',
        'country',
        'city',
        'email_verified_at',
        'external_registration_at',
        'external_registration_paid_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_code',
        'email_verification_expires_at',
        'password_reset_code',
        'password_reset_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'              => 'datetime',
            'email_verification_expires_at'  => 'datetime',
            'password_reset_expires_at'      => 'datetime',
            'external_registration_at'       => 'datetime',
            'external_registration_paid_at'  => 'datetime',
            'password'                       => 'hashed',
        ];
    }

    /**
     * Genera un código de 6 dígitos, lo guarda en la BD y lo envía por correo.
     * Expira en 15 minutos.
     */
    public function sendEmailVerificationNotification(): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->email_verification_code        = $code;
        $this->email_verification_expires_at  = now()->addMinutes(15);
        $this->saveQuietly();

        $this->notify(new EmailVerificationCode($code));
    }

    /**
     * Genera un código de 6 dígitos para restablecer la contraseña, lo guarda
     * en la BD y lo envía por correo. Expira en 15 minutos.
     */
    public function sendPasswordResetCode(): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->password_reset_code        = $code;
        $this->password_reset_expires_at  = now()->addMinutes(15);
        $this->saveQuietly();

        $this->notify(new PasswordResetCode($code));
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
