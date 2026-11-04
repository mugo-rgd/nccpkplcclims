<?php

namespace App\Notifications;

use App\Models\Equipment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentCalibrationDueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Equipment $equipment,
        public string $reminderType = 'due'
    ) {
    }

    /**
     * Delivery channels.
     *
     * Database notification is sent to:
     * - lab_manager
     * - supervisor
     * - technician
     * - lab_technician
     *
     * Email will be handled separately for lab managers only.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Store the notification in the notifications table.
     */
    public function toDatabase(object $notifiable): array
    {
        $dueDate = $this->equipment->next_calibration_date;

        $daysRemaining = $dueDate
            ? now()->startOfDay()->diffInDays(
                $dueDate->copy()->startOfDay(),
                false
            )
            : null;

        $status = match (true) {
            $daysRemaining === null => 'unknown',
            $daysRemaining < 0 => 'overdue',
            $daysRemaining === 0 => 'due_today',
            $daysRemaining <= 7 => 'due_within_7_days',
            default => 'due_soon',
        };

        return [
            'title' => $this->getTitle($status),

            'message' => $this->getMessage(
                $status,
                $daysRemaining
            ),

            'equipment_id' => $this->equipment->id,

            'equipment_number' =>
                $this->equipment->equipment_number,

            'equipment_name' =>
                $this->equipment->name,

            'serial_number' =>
                $this->equipment->serial_number,

            'next_calibration_date' =>
                $dueDate?->format('Y-m-d'),

            'days_remaining' => $daysRemaining,

            'status' => $status,

            'reminder_type' => $this->reminderType,

            'url' => route(
                'equipment.show',
                $this->equipment
            ),

            'icon' => 'fa-exclamation-triangle',

            'color' => match ($status) {
                'overdue' => 'danger',
                'due_today' => 'danger',
                'due_within_7_days' => 'warning',
                default => 'info',
            },
        ];
    }

    private function getTitle(string $status): string
    {
        return match ($status) {
            'overdue' =>
                'Equipment Calibration Overdue',

            'due_today' =>
                'Equipment Calibration Due Today',

            'due_within_7_days' =>
                'Equipment Calibration Due Soon',

            default =>
                'Upcoming Equipment Calibration',
        };
    }

    private function getMessage(
        string $status,
        ?int $daysRemaining
    ): string {
        $equipmentName =
            $this->equipment->name
            ?? 'Equipment';

        $equipmentNumber =
            $this->equipment->equipment_number
            ?? 'N/A';

        return match ($status) {
            'overdue' =>
                "{$equipmentName} ({$equipmentNumber}) is overdue for calibration by "
                . abs($daysRemaining ?? 0)
                . ' day(s).',

            'due_today' =>
                "{$equipmentName} ({$equipmentNumber}) is due for calibration today.",

            'due_within_7_days' =>
                "{$equipmentName} ({$equipmentNumber}) is due for calibration in "
                . ($daysRemaining ?? 0)
                . ' day(s).',

            default =>
                "{$equipmentName} ({$equipmentNumber}) has an upcoming calibration due date.",
        };
    }
}
