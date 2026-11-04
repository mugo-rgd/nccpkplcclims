<?php

namespace App\Mail;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CertificateRequiresCheckingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Certificate $certificate
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Certificate Requires Checking - '
                . $this->certificate->certificate_number
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.certificate-requires-checking',
            with: [
                'certificate' => $this->certificate,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
