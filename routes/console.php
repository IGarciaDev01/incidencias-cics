<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('incidencias:enviar-reporte-diario')
    ->dailyAt('23:00')
    ->timezone('America/Mexico_City')
    ->withoutOverlapping();
