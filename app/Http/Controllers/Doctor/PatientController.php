<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException; // <-- Agregado para manejo de errores Inertia
use Inertia\Inertia;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = session('active_clinic_id');
        $clinic = Clinic::findOrFail($clinicId);
        $search = $request->search;

        $patients = $clinic->patients()
            ->when($search, function ($query, $search) {
                // 🛡️ ARQUITECTURA AGNÓSTICA: Reemplazamos 'ilike' por LOWER()
                // Esto funciona perfectamente en PostgreSQL, MySQL y SQLite.
                $searchTerm = strtolower($search);
                $query->where(function ($q) use ($searchTerm) {
                    $q->whereRaw('LOWER(nombre) LIKE ?', ["%{$searchTerm}%"])
                      ->orWhere('telefono', 'LIKE', "%{$searchTerm}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Doctor/Patients/Index', [
            'patients' => $patients,
            'filters' => ['search' => $search]
        ]);
    }

    public function create()
    {
        return Inertia::render('Doctor/Patients/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'curp' => 'required|string|size:18',
            'fecha_nacimiento' => 'required|date',
            'mode' => 'required|in:huerfano,vincular,crear',
            'email' => 'nullable|email',
            'nombre_titular' => 'nullable|required_if:mode,crear|string|max:255',
            'privacy_notice' => 'accepted',
            'sexo' => 'nullable|in:Masculino,Femenino,Otro',
            'estado_civil' => 'nullable|string|max:255',
            'ocupacion' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
        ]);

        $clinicId = session('active_clinic_id');

        return DB::transaction(function () use ($request, $clinicId) {
            $userId = null;

            if ($request->mode === 'vincular') {
                // 🛡️ BÚSQUEDA CASE-INSENSITIVE Y BLINDAJE DE ROLES
                // Forzamos LOWER tanto en la BD como en el input.
                $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])
                            ->whereHas('roles', function($q) {
                                // Evita que se vincule un paciente a la cuenta de un Médico/Admin
                                $q->where('name', 'paciente_titular');
                            })
                            ->first();

                // 🛡️ UX: Retornamos un error elegante a Vue si no se encuentra la cuenta
                if (!$user) {
                    throw ValidationException::withMessages([
                        'email' => 'No se encontró una cuenta de paciente con este correo.'
                    ]);
                }

                $userId = $user->id;
            }
            elseif ($request->mode === 'crear') {
                $tempPassword = Str::random(12);
                $creator = new \App\Actions\Fortify\CreateNewUser();
                $user = $creator->create([
                    'name' => $request->nombre_titular,
                    'email' => strtolower($request->email), // Guardamos normalizado
                    'password' => $tempPassword,
                    'password_confirmation' => $tempPassword,
                    'role_type' => 'paciente_titular'
                ]);
                $user->update(['must_change_password' => true]);
                $userId = $user->id;
            }

            $patient = Patient::create([
                'nombre' => $request->nombre,
                'curp' => strtoupper($request->curp),
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'telefono' => $request->telefono,
                'email' => strtolower($request->email), // Normalizado
                'user_id' => $userId,
                'privacy_notice_accepted_at' => now(),
                'sexo' => $request->sexo,
                'estado_civil' => $request->estado_civil,
                'ocupacion' => $request->ocupacion,
                'religion' => $request->religion,
            ]);

            $patient->clinics()->attach($clinicId, [
                'expediente_fisico' => 'EXP-' . date('Y') . '-' . str_pad($patient->id, 4, '0', STR_PAD_LEFT)
            ]);

            if ($userId) {
                \App\Models\PatientProfile::firstOrCreate(
                    [
                        'user_id' => $userId,
                        'curp' => strtoupper($request->curp)
                    ],
                    [
                        'nombre_completo' => $request->nombre,
                        'fecha_nacimiento' => $request->fecha_nacimiento,
                        'genero' => strtolower($request->sexo ?? 'otro'),
                        'parentesco' => 'titular',
                        'avatar_color' => 'indigo'
                    ]
                );
            }

            return redirect()->route('patients.index')->with('success', 'Paciente registrado con éxito.');
        });
    }

    public function edit(Patient $patient)
    {
        $clinicId = session('active_clinic_id');
        abort_if(!$patient->clinics()->where('clinic_id', $clinicId)->exists(), 403, 'Acceso denegado');

        return Inertia::render('Doctor/Patients/Edit', [
            'patient' => $patient
        ]);
    }

    public function update(Request $request, Patient $patient)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'curp' => 'required|string|size:18',
            'fecha_nacimiento' => 'required|date',
            'mode' => 'required|in:editar,vincular,crear',
            'email' => 'nullable|email',
            'nombre_titular' => 'nullable|required_if:mode,crear|string|max:255',
            'sexo' => 'nullable|in:Masculino,Femenino,Otro',
            'estado_civil' => 'nullable|string|max:255',
            'ocupacion' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
        ]);

        $userId = $patient->user_id;

        if ($request->mode === 'vincular' && is_null($userId)) {
            // 🛡️ MISMA PROTECCIÓN QUE EN EL STORE
            $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])
                        ->whereHas('roles', function($q) {
                            $q->where('name', 'paciente_titular');
                        })
                        ->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => 'No se encontró una cuenta de paciente con este correo.'
                ]);
            }
            $userId = $user->id;
        }
        elseif ($request->mode === 'crear' && is_null($userId)) {
            $tempPassword = Str::random(12);
            $creator = new \App\Actions\Fortify\CreateNewUser();
            $user = $creator->create([
                'name' => $request->nombre_titular,
                'email' => strtolower($request->email),
                'password' => $tempPassword,
                'password_confirmation' => $tempPassword,
                'role_type' => 'paciente_titular'
            ]);
            $user->update(['must_change_password' => true]);
            $userId = $user->id;
        }

        $patient->update([
            'nombre' => $request->nombre,
            'curp' => strtoupper($request->curp),
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'telefono' => $request->telefono,
            'email' => strtolower($request->email),
            'user_id' => $userId,
            'sexo' => $request->sexo,
            'estado_civil' => $request->estado_civil,
            'ocupacion' => $request->ocupacion,
            'religion' => $request->religion,
        ]);

        if ($userId) {
            \App\Models\PatientProfile::firstOrCreate(
                [
                    'user_id' => $userId,
                    'curp' => strtoupper($request->curp)
                ],
                [
                    'nombre_completo' => $request->nombre,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'genero' => strtolower($request->sexo ?? 'otro'),
                    'parentesco' => 'titular',
                    'avatar_color' => 'indigo'
                ]
            );
        }

        return redirect()->route('patients.index')->with('success', 'Expediente actualizado correctamente.');
    }
}
