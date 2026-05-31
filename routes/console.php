<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Festival;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('festivals:expire', function () {
    $count = Festival::expireOutdated();
    $this->info("Đã tắt {$count} festival hết hạn.");
})->purpose('Tắt festival đã quá ngày kết thúc');

Schedule::command('festivals:expire')->daily();
