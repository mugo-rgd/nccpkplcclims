<?php

namespace App\Mail;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CalibrationCertificateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Certificate $certificate,
        protected string $pdfPath
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Calibration Certificate - '
                . $this->certificate->certificate_number
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.calibration-certificate'
        );
    }

    public function attachments(): array
    {
        if (
            !$this->pdfPath
            || !file_exists($this->pdfPath)
        ) {
            return [];
        }

        $certificateNumber = preg_replace(
            '/[^A-Za-z0-9_-]/',
            '_',
            (string) $this->certificate->certificate_number
        );

        return [
            Attachment::fromPath($this->pdfPath)
                ->as(
                    "Calibration-Certificate-{$certificateNumber}.pdf"
                )
                ->withMime('application/pdf'),
        ];
    }
}
