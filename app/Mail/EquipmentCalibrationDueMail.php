<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class EquipmentCalibrationDueMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Collection $overdueEquipment,
        public Collection $dueTodayEquipment,
        public Collection $upcomingEquipment
    ) {
    }

    public function envelope(): Envelope
    {
        $overdueCount = $this->overdueEquipment->count();
        $dueTodayCount = $this->dueTodayEquipment->count();
        $upcomingCount = $this->upcomingEquipment->count();

        return new Envelope(
            subject:
                'Equipment Calibration Due Report'
                . " - Overdue: {$overdueCount}"
                . " | Due Today: {$dueTodayCount}"
                . " | Upcoming: {$upcomingCount}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.equipment-calibration-due',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
