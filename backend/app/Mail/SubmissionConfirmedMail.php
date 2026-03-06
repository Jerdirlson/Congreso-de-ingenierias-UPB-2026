<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubmissionConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Submission $submission
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu participación en el Congreso de Ingenierías 2026 está confirmada!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.submission-confirmed',
        );
    }
}
