<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Http;
use App\Models\ExchangeRate;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $response = Http::get('https://ve.dolarapi.com/v1/dolares/oficial');
    if ($response->successful()) {
        ExchangeRate::create(['rate' => $response->json()['promedio']]);
    }
})->dailyAt('02:34'); // Se ejecuta a las 9 AM todos los días
