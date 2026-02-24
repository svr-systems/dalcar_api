<?php

use App\Console\Commands\SendCalendarPendingDocsEmails;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('dalcar:calendar-pendings', function () {
  $this->call(SendCalendarPendingDocsEmails::class);
})->purpose('Envía correos por facturas/documentos calendarizados pendientes (sin archivo)');

Schedule::command('dalcar:calendar-pendings')
  ->cron('0 9 * * 1-6')
  ->timezone(config('app.timezone'))
  ->withoutOverlapping(30);