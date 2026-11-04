<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Initialize FYR periods annually on July 1st
        $schedule->command('quality:init-fyr')
            ->yearlyOn(7, 1, '00:00')
            ->emailOutputOnFailure('admin@lab.com');

        // Generate next FYR targets on June 1st
        $schedule->command('quality:generate-next-fyr --force')
            ->yearlyOn(6, 1, '00:00')
            ->emailOutputOnFailure('admin@lab.com');

        // Send quality alerts daily at 8 AM
        $schedule->command('quality:send-alerts')
            ->dailyAt('08:00')
            ->emailOutputOnFailure('admin@lab.com');

        // Generate quarterly reports on the 1st of Jan, Apr, Jul, Oct
        $schedule->command('quality:generate-reports')
            ->quarterly()
            ->emailOutputTo('labmanager@lab.com');

        // Backup quality data weekly
        $schedule->command('quality:backup')
            ->weekly()
            ->sundays()
            ->at('02:00');
    }
protected $commands = [
    // ... other commands
    \App\Console\Commands\FixTrialData::class,
];

}
