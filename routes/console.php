<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

//  php artisan schedule:run
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Schedule::command('model:prune')->daily();
Schedule::command('library:check-due')->everyMinute();
Schedule::command('library-cards:expire')->daily();