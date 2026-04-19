<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Clinic;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Str; 
use Inertia\Inertia;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtenemos la clínica activa
        $clinicId = session('active_clinic_id');
        $clinic = Clinic::findOrFail($clinicId);

        // 2. Leemos lo que el usuario escribió en el buscador
        $search = $request->search;

        // 3. Consulta Inteligente con Buscador y Paginación
        $patients = $clinic->patients()
            ->when($search, function ($query, $search) {
                // Buscamos por nombre o por teléfono. 
                // Usamos 'ilike' que es perfecto para PostgreSQL (ignora mayúsculas/minúsculas)
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'ilike', "%{$search}%")
                      ->orWhere('telefono', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString(); // Magia: Mantiene el ?search=... en los botones de "Siguiente página"

        return Inertia::render('Doctor/Patients/Index', [
            'patients' => $patients,
            'filters' => ['search' => $search] // Regresamos el texto a Vue para que el input no se borre
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
            'curp' => 'required|string|size:18', // <--- NUEVO: CURP Obligatorio
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
                $user = User::where('email', $request->email)->first();
                $userId = $user->id;
            } 
            elseif ($request->mode === 'crear') {
                $tempPassword = Str::random(12);
                $creator = new \App\Actions\Fortify\CreateNewUser();
                $user = $creator->create([
                    'name' => $request->nombre_titular,
                    'email' => $request->email,
                    'password' => $tempPassword,
                    'password_confirmation' => $tempPassword,
                    'role_type' => 'paciente_titular'
                ]);
                $user->update(['must_change_password' => true]);
                $userId = $user->id;
            }

            // 1. Creamos el paciente en el portal del Doctor
            $patient = Patient::create([
                'nombre' => $request->nombre,
                'curp' => strtoupper($request->curp), // <--- Guardamos el CURP
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'telefono' => $request->telefono,
                'email' => $request->email,
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

            // 🔥 LA MAGIA: Auto-generar el Perfil Netflix si hay usuario vinculado
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
                        'parentesco' => 'titular', // Por defecto
                        'avatar_color' => 'indigo' // Color base
                    ]
                );
            }

            return redirect()->route('patients.index')->with('success', 'Paciente registrado con éxito.'); 
        });
    }


    public function edit(Patient $patient)
    {
        // Antihackers: Verificamos que el paciente pertenezca a la clínica actual
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
            'curp' => 'required|string|size:18', // <--- NUEVO
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
            $user = User::where('email', $request->email)->firstOrFail();
            $userId = $user->id;
        } 
        elseif ($request->mode === 'crear' && is_null($userId)) {
            $tempPassword = Str::random(12);
            $creator = new \App\Actions\Fortify\CreateNewUser();
            $user = $creator->create([
                'name' => $request->nombre_titular,
                'email' => $request->email,
                'password' => $tempPassword,
                'password_confirmation' => $tempPassword,
                'role_type' => 'paciente_titular'
            ]);
            $user->update(['must_change_password' => true]);
            $userId = $user->id;
        }
    
        // 1. Actualizamos al paciente
        $patient->update([
            'nombre' => $request->nombre,
            'curp' => strtoupper($request->curp), // <--- Guardamos el CURP
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'telefono' => $request->telefono,
            'email' => $request->email, 
            'user_id' => $userId, 
            'sexo' => $request->sexo,
            'estado_civil' => $request->estado_civil,
            'ocupacion' => $request->ocupacion,
            'religion' => $request->religion,
        ]);

        // 🔥 LA MAGIA: Si el doctor acaba de vincular la cuenta, creamos el perfil Netflix
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
    
        return redirect()->route('patients.index');
    }
}
