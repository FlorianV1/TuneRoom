<?php

use App\Jobs\AdvanceQueues;
use App\Jobs\CloseEmptyRooms;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Schedule::job(new AdvanceQueues)->everyMinute();

Schedule::job(new CloseEmptyRooms)->everyMinute();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
