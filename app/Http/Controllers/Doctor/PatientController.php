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
        // 1. Validación (Si esto falla, te regresa a la vista con errores)
        $request->validate([
            'nombre' => 'required|string|max:255',
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

                // Llamamos a tu acción de Fortify que ya tienes configurada
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

                // Aquí puedes disparar el correo después
            }

            // Creamos el paciente
            $patient = Patient::create([
                'nombre' => $request->nombre,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'user_id' => $userId,
                'privacy_notice_accepted_at' => now(),
                'sexo' => $request->sexo,                 // <--- AGREGAR
                'estado_civil' => $request->estado_civil, // <--- AGREGAR
                'ocupacion' => $request->ocupacion,       // <--- AGREGAR
                'religion' => $request->religion,         // <--- AGREGAR
            ]);

            // Lo unimos a la clínica
            $patient->clinics()->attach($clinicId, [
                'expediente_fisico' => 'EXP-' . date('Y') . '-' . str_pad($patient->id, 4, '0', STR_PAD_LEFT)
            ]);

            return redirect()->route('patients.index')->with('new_patient_id', $patient->id); // <--- CAMBIAR ESTA LÍNEA
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
            'fecha_nacimiento' => 'required|date',
            'mode' => 'required|in:editar,vincular,crear', // 'editar' es el modo normal
            'email' => 'nullable|email',
            'nombre_titular' => 'nullable|required_if:mode,crear|string|max:255',
            'sexo' => 'nullable|in:Masculino,Femenino,Otro',
            'estado_civil' => 'nullable|string|max:255',
            'ocupacion' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
        ]);
    
        $userId = $patient->user_id;
    
        // Si el médico decidió vincular o crear cuenta en este momento:
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
    
        $patient->update([
            'nombre' => $request->nombre,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'telefono' => $request->telefono,
            'email' => $request->email, // Ahora se actualiza siempre
            'user_id' => $userId, // Se actualiza si hubo vinculación
            'sexo' => $request->sexo,
            'estado_civil' => $request->estado_civil,
            'ocupacion' => $request->ocupacion,
            'religion' => $request->religion,
        ]);
    
        return redirect()->route('patients.index');
    }
}
