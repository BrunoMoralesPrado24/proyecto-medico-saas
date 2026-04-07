<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Clinic;
use Inertia\Inertia;

class PatientController extends Controller
{
    public function index()
    {
        // 1. Obtenemos la clínica en la que el doctor está trabajando hoy
        $clinicId = session('active_clinic_id');

        // 2. Buscamos a la clínica y le pedimos SOLO sus pacientes
        $clinic = Clinic::findOrFail($clinicId);
        $patients = $clinic->patients()->orderBy('nombre')->get();

        return Inertia::render('Patients/Index', [
            'patients' => $patients
        ]);
    }

    public function create()
    {
        return Inertia::render('Patients/Create');
    }

    public function store(Request $request)
    {
        // 1. Validación estricta, incluyendo el candado legal LFPDPPP
        $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'privacy_notice' => 'accepted' // <-- OBLIGATORIO: Debe marcar la casilla
        ]);

        $clinicId = session('active_clinic_id');

        // 2. Creamos al paciente en la tabla general
        $patient = Patient::create([
            'nombre' => $request->nombre,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'privacy_notice_accepted_at' => now(), // Guardamos la hora exacta de la firma
        ]);

        // 3. LA MAGIA: Lo unimos a la clínica actual en la tabla pivote
        // Además, le generamos un número de expediente físico automático
        $patient->clinics()->attach($clinicId, [
            'expediente_fisico' => 'EXP-' . date('Y') . '-' . str_pad($patient->id, 4, '0', STR_PAD_LEFT)
        ]);

        return redirect()->route('patients.index');
    }


    public function edit(Patient $patient)
    {
        // Antihackers: Verificamos que el paciente pertenezca a la clínica actual
        $clinicId = session('active_clinic_id');
        abort_if(!$patient->clinics()->where('clinic_id', $clinicId)->exists(), 403, 'Acceso denegado');

        return Inertia::render('Patients/Edit', [
            'patient' => $patient
        ]);
    }

    public function update(Request $request, Patient $patient)
    {
        $clinicId = session('active_clinic_id');
        abort_if(!$patient->clinics()->where('clinic_id', $clinicId)->exists(), 403, 'Acceso denegado');

        $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $patient->update([
            'nombre' => $request->nombre,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'telefono' => $request->telefono,
            'email' => $request->email,
        ]);

        return redirect()->route('patients.index');
    }
}
