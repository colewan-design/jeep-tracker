<?php

use App\Console\Commands\TimeoutInactiveJeeps;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Mark jeeps inactive and close their open trips when silent for >2 minutes.
Schedule::command(TimeoutInactiveJeeps::class)->everyMinute();
