<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment('Hello, world!');
})->purpose('Display an inspiring quote');

Schedule::command('equipment:check-calibration-due')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground();

    Schedule::command(
    'documents:check-compliance'
)
    ->dailyAt('08:00')
    ->withoutOverlapping();
