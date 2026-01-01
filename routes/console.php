<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::command('app:send-renewal-reminders')
//     ->dailyAt('09:00');

// ====== جدولة إنشاء الفواتير قبل 10 أيام ======
// Schedule::command('invoices:generate-upcoming --days-before=10')
//     ->dailyAt('00:10')     // شغّل يوميًا 00:10
//     ->onOneServer()        // مفيد لو عندك أكثر من سيرفر
//     ->withoutOverlapping() // يمنع التداخل
//     ->runInBackground();   // يشغّل بالخلفية

Schedule::command('reminders:check-expiring')
    ->dailyAt('00:10')
    ->onOneServer()        
    ->withoutOverlapping() 
    ->runInBackground();   