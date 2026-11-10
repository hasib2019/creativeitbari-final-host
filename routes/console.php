<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Payment hub safety net (needs the scheduler: `* * * * * php artisan schedule:run`).
Schedule::command('payments:reconcile')->everyFiveMinutes()->withoutOverlapping();
