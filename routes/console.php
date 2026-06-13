<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ScrapeSocialMediaJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwalkan scraper untuk berjalan otomatis
// Schedule::job(new ScrapeSocialMediaJob)->everyFiveMinutes();

// Jadwalkan pembersihan data duplikat setiap hari (tengah malam)
// Schedule::command('jobs:remove-duplicates')->daily();
