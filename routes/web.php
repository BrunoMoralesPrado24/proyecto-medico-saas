<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ClinicSelectionController; // <-- NUEVO
use App\Http\Middleware\EnsureActiveClinic; // <-- NUEVO

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Todo lo que está aquí adentro requiere que el usuario haya iniciado sesión
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // 1. Rutas de Selección de Clínica (El Cadenero las deja pasar)
    Route::get('/select-clinic', [ClinicSelectionController::class, 'select'])->name('clinics.select');
    Route::post('/select-clinic', [ClinicSelectionController::class, 'set'])->name('clinics.set');

    // 2. Grupo ULTRA PROTEGIDO (Requiere sesión + Clínica Seleccionada obligatoria)
    Route::middleware([EnsureActiveClinic::class])->group(function () {

        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');

        // *Aquí pondremos después todas las rutas de Pacientes, Citas, etc.*
        //  RUTAS DE PACIENTES
        Route::resource('patients', App\Http\Controllers\PatientController::class);
    });
});
