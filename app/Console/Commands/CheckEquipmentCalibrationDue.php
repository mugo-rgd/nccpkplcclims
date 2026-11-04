<?php

namespace App\Console\Commands;

use App\Mail\EquipmentCalibrationDueMail;
use App\Models\Equipment;
use App\Models\User;
use App\Notifications\EquipmentCalibrationDueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class CheckEquipmentCalibrationDue extends Command
{
    /**
     * Command name.
     */
    protected $signature = 'equipment:check-calibration-due
                            {--days=30 : Number of days ahead to check}
                            {--force : Ignore the reminder interval}
                            {--no-email : Do not send email to lab managers}';

    /**
     * Command description.
     */
    protected $description =
        'Check equipment calibration due dates, create notifications, and email lab managers.';

    /**
     * Execute the command.
     */
    public function handle(): int
    {
        $daysAhead = max(
            0,
            (int) $this->option('days')
        );

        $force = (bool) $this->option('force');
        $skipEmail = (bool) $this->option('no-email');

        $today = now()->startOfDay();
        $upcomingLimit = $today->copy()->addDays($daysAhead);

        $this->info(
            "Checking equipment due between {$today->toDateString()} "
            . "and {$upcomingLimit->toDateString()}."
        );

        /*
         * Fetch equipment that is overdue, due today, or due soon.
         */
        $equipmentItems = Equipment::query()
            ->where('calibration_reminder_enabled', true)
            ->whereNotNull('next_calibration_date')
            ->whereDate(
                'next_calibration_date',
                '<=',
                $upcomingLimit
            )
            ->whereNotIn('status', [
                'retired',
                'disposed',
                'inactive',
            ])
            ->orderBy('next_calibration_date')
            ->get();

        if ($equipmentItems->isEmpty()) {
            $this->info(
                'No equipment requires a calibration reminder.'
            );

            return self::SUCCESS;
        }

        /*
         * Group equipment by urgency.
         */
        $overdueEquipment = $equipmentItems
            ->filter(function (Equipment $equipment) use ($today) {
                return $equipment->next_calibration_date
                    && $equipment->next_calibration_date
                        ->copy()
                        ->startOfDay()
                        ->lt($today);
            })
            ->values();

        $dueTodayEquipment = $equipmentItems
            ->filter(function (Equipment $equipment) use ($today) {
                return $equipment->next_calibration_date
                    && $equipment->next_calibration_date
                        ->copy()
                        ->startOfDay()
                        ->equalTo($today);
            })
            ->values();

        $upcomingEquipment = $equipmentItems
            ->filter(function (Equipment $equipment) use (
                $today,
                $upcomingLimit
            ) {
                if (!$equipment->next_calibration_date) {
                    return false;
                }

                $dueDate = $equipment
                    ->next_calibration_date
                    ->copy()
                    ->startOfDay();

                return $dueDate->gt($today)
                    && $dueDate->lte($upcomingLimit);
            })
            ->values();

        /*
         * Find users who should receive in-system notifications.
         */
        $notificationRecipients = User::query()
            ->role([
                'lab_manager',
                'supervisor',
                'technician',
                'lab_technician',
            ])
            ->where(function ($query) {
                $query
                    ->where('is_active', true)
                    ->orWhere('status', true);
            })
            ->get();

        if ($notificationRecipients->isEmpty()) {
            $this->warn(
                'No eligible users found for database notifications.'
            );
        }

        /*
         * Only equipment allowed by the reminder interval
         * will generate a new notification.
         */
        $equipmentToNotify = $equipmentItems
            ->filter(function (Equipment $equipment) use ($force) {
                return $this->shouldSendReminder(
                    $equipment,
                    $force
                );
            })
            ->values();

        $notificationsCreated = 0;

        foreach ($equipmentToNotify as $equipment) {
            Notification::send(
                $notificationRecipients,
                new EquipmentCalibrationDueNotification(
                    $equipment,
                    $this->getReminderType($equipment)
                )
            );

            $equipment->forceFill([
                'last_calibration_reminder_at' => now(),

                'calibration_reminder_count' =>
                    (int) $equipment->calibration_reminder_count + 1,
            ])->save();

            $notificationsCreated++;
        }

        /*
         * Email lab managers with one consolidated report.
         */
        $emailsSent = 0;

        if (!$skipEmail && $equipmentToNotify->isNotEmpty()) {
            $labManagers = User::query()
                ->role('lab_manager')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->where(function ($query) {
                    $query
                        ->where('is_active', true)
                        ->orWhere('status', true);
                })
                ->get();

            if ($labManagers->isEmpty()) {
                $this->warn(
                    'No active lab manager with an email address was found.'
                );
            } else {
                /*
                 * Rebuild the email groups using only equipment that
                 * actually passed the reminder-interval check.
                 */
                $emailOverdue = $this->filterOverdue(
                    $equipmentToNotify
                );

                $emailDueToday = $this->filterDueToday(
                    $equipmentToNotify
                );

                $emailUpcoming = $this->filterUpcoming(
                    $equipmentToNotify,
                    $daysAhead
                );

                foreach ($labManagers as $manager) {
                    Mail::to($manager->email)->send(
                        new EquipmentCalibrationDueMail(
                            $emailOverdue,
                            $emailDueToday,
                            $emailUpcoming
                        )
                    );

                    $emailsSent++;
                }
            }
        }

        Log::info(
            'Equipment calibration reminder command completed.',
            [
                'days_ahead' => $daysAhead,
                'equipment_found' => $equipmentItems->count(),
                'equipment_notified' => $notificationsCreated,
                'database_recipients' =>
                    $notificationRecipients->count(),
                'emails_sent' => $emailsSent,
            ]
        );

        $this->newLine();

        $this->table(
            ['Category', 'Count'],
            [
                ['Overdue', $overdueEquipment->count()],
                ['Due Today', $dueTodayEquipment->count()],
                ['Upcoming', $upcomingEquipment->count()],
                ['Notifications Created', $notificationsCreated],
                ['Emails Sent', $emailsSent],
            ]
        );

        $this->info(
            'Equipment calibration reminder check completed.'
        );

        return self::SUCCESS;
    }

    /**
     * Prevent repeated reminders within 24 hours.
     */
    private function shouldSendReminder(
        Equipment $equipment,
        bool $force
    ): bool {
        if ($force) {
            return true;
        }

        if (!$equipment->last_calibration_reminder_at) {
            return true;
        }

        return $equipment
            ->last_calibration_reminder_at
            ->lte(now()->subDay());
    }

    /**
     * Determine reminder category.
     */
    private function getReminderType(
        Equipment $equipment
    ): string {
        if (!$equipment->next_calibration_date) {
            return 'unknown';
        }

        $dueDate = $equipment
            ->next_calibration_date
            ->copy()
            ->startOfDay();

        $today = now()->startOfDay();

        return match (true) {
            $dueDate->lt($today) => 'overdue',
            $dueDate->equalTo($today) => 'due_today',
            default => 'upcoming',
        };
    }

    /**
     * Filter overdue equipment.
     */
    private function filterOverdue(
        Collection $equipmentItems
    ): Collection {
        $today = now()->startOfDay();

        return $equipmentItems
            ->filter(function (Equipment $equipment) use ($today) {
                return $equipment->next_calibration_date
                    && $equipment->next_calibration_date
                        ->copy()
                        ->startOfDay()
                        ->lt($today);
            })
            ->values();
    }

    /**
     * Filter equipment due today.
     */
    private function filterDueToday(
        Collection $equipmentItems
    ): Collection {
        $today = now()->startOfDay();

        return $equipmentItems
            ->filter(function (Equipment $equipment) use ($today) {
                return $equipment->next_calibration_date
                    && $equipment->next_calibration_date
                        ->copy()
                        ->startOfDay()
                        ->equalTo($today);
            })
            ->values();
    }

    /**
     * Filter equipment due after today.
     */
    private function filterUpcoming(
        Collection $equipmentItems,
        int $daysAhead
    ): Collection {
        $today = now()->startOfDay();
        $limit = $today->copy()->addDays($daysAhead);

        return $equipmentItems
            ->filter(function (Equipment $equipment) use (
                $today,
                $limit
            ) {
                if (!$equipment->next_calibration_date) {
                    return false;
                }

                $dueDate = $equipment
                    ->next_calibration_date
                    ->copy()
                    ->startOfDay();

                return $dueDate->gt($today)
                    && $dueDate->lte($limit);
            })
            ->values();
    }
}
