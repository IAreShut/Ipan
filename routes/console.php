<?php

use App\Models\User;
use App\Notifications\DailyLogReminderNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Task: Daily 5:00 PM Weekday Log Reminder
|--------------------------------------------------------------------------
| Sends a notification (DB + Email) to all students every
| weekday (Mon-Fri) at 5:00 PM reminding them to submit their daily log.
*/
Schedule::call(function () {
    $students = User::where('role', 'student')->get();

    foreach ($students as $student) {
        $student->notify(new DailyLogReminderNotification);
    }
})->weekdays()->at('17:00')->name('daily-log-reminder');
