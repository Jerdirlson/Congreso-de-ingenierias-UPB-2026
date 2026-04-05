<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EmailVerificationCode extends Notification
{
    public function __construct(private readonly string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Código de verificación — Congreso Ingenierías 2026')
            ->view('emails.verification-code', [
                'code'           => $this->code,
                'name'           => $notifiable->name,
                'expiresMinutes' => 15,
            ]);
    }
}
