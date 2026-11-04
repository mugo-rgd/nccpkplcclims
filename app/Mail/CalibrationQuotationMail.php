<?php

namespace App\Mail;

use App\Models\Calibration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CalibrationQuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Calibration $calibration
    ) {
    }

    public function envelope(): Envelope
    {
        $quotationNumber =
            $this->calibration->quotation_number
            ?? 'Quotation #' . $this->calibration->id;

        return new Envelope(
            subject: 'Calibration Quotation - ' . $quotationNumber
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.calibration-quotation',
            with: [
                'calibration' => $this->calibration,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
