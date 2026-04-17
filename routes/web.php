<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 1. IMPORTACIONES LIMPIAS:
// Traemos los controladores aquí arriba para no tener que escribir
// "App\Http\Controllers\Doctor\..." en cada línea de abajo.
use App\Http\Controllers\Doctor\ClinicSelectionController;
use App\Http\Controllers\Doctor\PatientController;
use App\Http\Controllers\Doctor\MedicalRecordController;
use App\Http\Controllers\Doctor\ConsultationController;
use App\Http\Middleware\EnsureActiveClinic;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Doctor\AppointmentController;
use App\Http\Controllers\Doctor\VitalSignController;
use App\Http\Controllers\Doctor\DashboardController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::post('/check-email', function (Request $request) {
    $exists = \App\Models\User::where('email', $request->email)->exists();
    return response()->json(['exists' => $exists]);
})->middleware('auth:sanctum');

// ZONA PROTEGIDA: Solo usuarios logueados
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // 🚦 CONTROLADOR DE TRÁFICO PRINCIPAL
    // Atrapa a todos los que inician sesión y deciden su destino
    Route::get('/dashboard', function () {
        // Si no hay una clínica activa en la memoria de la sesión, lo obligamos a elegir una
        if (!session()->has('active_clinic_id')) {
            return redirect()->route('clinics.select');
        }
        // Si ya tiene una clínica activa, le mostramos el Dashboard con sus KPIs
        return app(\App\Http\Controllers\Doctor\DashboardController::class)->index();
    })->name('dashboard');

    // ==========================================
    // ÁREA DEL MÉDICO (Prefijo URL: /doctor/...)
    // ==========================================
    Route::prefix('doctor')->group(function () {

        // 1. Rutas de Selección de Clínica
        Route::get('/select-clinic', [ClinicSelectionController::class, 'select'])->name('clinics.select');
        Route::post('/select-clinic', [ClinicSelectionController::class, 'set'])->name('clinics.set');
        Route::post('/clinics', [ClinicSelectionController::class, 'store'])->name('clinics.store');

        // 2. Grupo ULTRA PROTEGIDO (Requiere Clínica Seleccionada)
        Route::middleware([EnsureActiveClinic::class])->group(function () {
            // RUTAS DE PACIENTES
            // URL: /doctor/patients | Nombres cortos: patients.index, patients.create, etc.
            Route::resource('patients', PatientController::class);

            //MÓDULO: Expedientes Clínicos (Santuario Médico)
            Route::get('/medical-records', [MedicalRecordController::class, 'index'])->name('medical-records.index');
            Route::get('/medical-records/{patient}', [MedicalRecordController::class, 'show'])->name('medical-records.show');
            Route::put('/medical-records/{patient}', [MedicalRecordController::class, 'update'])->name('medical-records.update');

            // RUTAS DE CONSULTAS
            Route::get('/consultations', [ConsultationController::class, 'index'])->name('consultations.index');
            Route::get('/patients/{patient}/consultations/create', [ConsultationController::class, 'create'])->name('consultations.create');
            Route::post('/patients/{patient}/consultations', [ConsultationController::class, 'store'])->name('consultations.store');
            Route::get('/patients/{patient}/consultations/{consultation}', [ConsultationController::class, 'show'])->name('consultations.show');
            Route::get('/patients/{patient}/consultations/{consultation}/prescription-pdf', [ConsultationController::class, 'printPrescription'])->name('consultations.prescription.pdf');

            // *Aquí irán las futuras rutas del doctor*
            Route::resource('appointments', AppointmentController::class);

            // MÓDULO: Signos Vitales
            Route::post('/patients/{patient}/vital-signs', [VitalSignController::class, 'store'])->name('vital-signs.store');
        });
    });

    // ==========================================
    // ÁREA DEL PACIENTE (Prefijo URL: /paciente/...)
    // ==========================================
    // Route::prefix('paciente')->group(function () {
    //      Aquí meteremos las rutas cuando armemos el portal del paciente
    // });

});
