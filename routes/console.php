<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ==============================================================
// 🧹 EL LIMPIADOR NOCTURNO (Mantenimiento de Base de Datos)
// ==============================================================
// Se ejecuta todos los días a la medianoche. Busca usuarios que
// tienen más de 5 días de creados y nunca verificaron su correo, y los elimina.
Schedule::call(function () {
    User::whereNull('email_verified_at')
        ->where('created_at', '<', Carbon::now()->subDays(5))
        ->delete();
})->daily();
