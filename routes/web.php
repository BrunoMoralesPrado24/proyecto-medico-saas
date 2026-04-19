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
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), \App\Http\Middleware\GracePeriodVerification::class])->group(function () {


    Route::post('/seleccionar-rol', function (\Illuminate\Http\Request $request) {
        $request->validate(['role' => 'required|in:medico,paciente_titular']);

        // Guardamos su decisión en la sesión
        session(['active_role' => $request->role]);

        // Lo mandamos de vuelta al dashboard para que el Guardia de Tráfico lo redirija correctamente
        return redirect()->route('dashboard');
    })->name('role.select');

    // 🚦 CONTROLADOR DE TRÁFICO PRINCIPAL
    // Atrapa a todos los que inician sesión y deciden su destino
    // 🚦 EL GRAN DISTRIBUIDOR (Controlador de Tráfico NexSalud)
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $activeRole = session('active_role'); // Revisamos si ya eligió qué hacer hoy

        // 1. Si ya tiene un rol activo en esta sesión, lo mandamos a su área
        if ($activeRole === 'medico') {
            if (!session()->has('active_clinic_id')) {
                return redirect()->route('clinics.select'); // Aduana de clínicas
            }
            return app(\App\Http\Controllers\Doctor\DashboardController::class)->index();
        }
        if ($activeRole === 'paciente_titular') {
            return redirect()->route('patient.profiles.index'); // O al selector Netflix
        }

        // 2. 🔥 Si NO ha elegido y tiene AMBOS roles (El Menú "¿Qué quieres revisar hoy?")
        if ($user->hasRole('medico') && $user->hasRole('paciente_titular')) {
            // Pronto crearemos esta vista con dos botones gigantes
            return Inertia::render('Auth/SelectRole');
        }

        // 3. Si solo tiene UN rol, le auto-asignamos la sesión y lo mandamos directo
        if ($user->hasRole('medico')) {
            session(['active_role' => 'medico']);
            return redirect()->route('clinics.select');
        }
        if ($user->hasRole('paciente_titular')) {
            session(['active_role' => 'paciente_titular']);
            return redirect()->route('patient.profiles.index');
        }

        abort(403, 'Rol no reconocido en el sistema');
    })->name('dashboard');

    // ==========================================
    // ÁREA DEL MÉDICO (Prefijo URL: /doctor/...)
    // ==========================================
    Route::prefix('doctor')->group(function () {

        // 🔥 El Atajo: Habilitar/Entrar al Portal de Paciente desde la cuenta de Doctor
        Route::post('/mi-portal-paciente', function () {
            $user = auth()->user();

            // 1. Nos aseguramos de que el rol exista en la BD para que no explote
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'paciente_titular', 'guard_name' => 'web']);

            // 2. Si el doctor aún no lo tiene, se lo asignamos
            if (!$user->hasRole('paciente_titular')) {
                $user->assignRole('paciente_titular');
            }

            // 3. Le decimos a la sesión que hoy quiere ser paciente
            session(['active_role' => 'paciente_titular']);

            // 4. Lo mandamos al selector Netflix
            return redirect()->route('patient.profiles.index');
        })->name('doctor.enter_patient_portal');

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
    // ÁREA DEL PACIENTE (Portal Familiar)
    // ==========================================
    /*Route::prefix('paciente')->middleware(['auth', 'role:paciente_titular'])->group(function () {

        // Selector de Perfiles (Netflix Style)
        Route::get('/selector', [App\Http\Controllers\Patient\ProfileController::class, 'index'])->name('patient.profiles.index');
        Route::post('/selector', [App\Http\Controllers\Patient\ProfileController::class, 'store'])->name('patient.profiles.store');
        Route::post('/selector/{profile}/select', [App\Http\Controllers\Patient\ProfileController::class, 'select'])->name('patient.profiles.select');
        // Dashboard principal (La Bóveda)
        Route::get('/dashboard', [App\Http\Controllers\Patient\MedicalRecordController::class, 'index'])->name('paciente.dashboard');
        
        // Descarga de receta encriptada por el paciente
        Route::get('/boveda/recetas/{hash}/pdf', [App\Http\Controllers\Patient\MedicalRecordController::class, 'printPrescription'])->name('patient.prescriptions.pdf');


        // Dashboard del Perfil Seleccionado (Requiere haber elegido un perfil)
        Route::get('/dashboard', function () {
            // Aquí irá la T14 y T15
            return Inertia::render('Patient/Dashboard', [
                'profile' => \App\Models\PatientProfile::find(session('active_patient_profile_id'))
            ]);
        })->name('paciente.dashboard');
    });*/ //COPIA DE SEGURIDAD POR SI ACASO

    // ==========================================
    // ÁREA DEL PACIENTE (Portal Familiar)
    // ==========================================
    Route::prefix('paciente')->middleware(['auth', 'role:paciente_titular'])->group(function () {

        // Selector de Perfiles (Netflix Style)
        Route::get('/selector', [App\Http\Controllers\Patient\ProfileController::class, 'index'])->name('patient.profiles.index');
        Route::post('/selector', [App\Http\Controllers\Patient\ProfileController::class, 'store'])->name('patient.profiles.store');
        Route::post('/selector/{profile}/select', [App\Http\Controllers\Patient\ProfileController::class, 'select'])->name('patient.profiles.select');

        // 👇 EL NUEVO DASHBOARD ES LA BÓVEDA 👇
        // Al entrar al dashboard, Laravel irá al controlador, buscará las recetas y renderizará Boveda.vue
        Route::get('/dashboard', [App\Http\Controllers\Patient\MedicalRecordController::class, 'index'])->name('paciente.dashboard');
        
        // Descarga de receta encriptada por el paciente
        Route::get('/boveda/recetas/{hash}/pdf', [App\Http\Controllers\Patient\MedicalRecordController::class, 'printPrescription'])->name('patient.prescriptions.pdf');
    });
});
