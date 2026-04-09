<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MedicalRecordController extends Controller
{
    /**
     * Muestra la lista de pacientes para seleccionar su expediente clínico.
     */
    public function index()
    {
        $clinicId = session('active_clinic_id');

        // Solo traemos a los pacientes de ESTA clínica. 
        // Nota: Solo traemos campos básicos, no necesitamos el correo aquí.
        $patients = Patient::whereHas('clinics', function ($query) use ($clinicId) {
            $query->where('clinic_id', $clinicId);
        })
        ->select('id', 'nombre', 'fecha_nacimiento')
        ->orderBy('nombre')
        ->get();

        return Inertia::render('Doctor/MedicalRecords/Index', [
            'patients' => $patients
        ]);
    }

    /**
     * Muestra el "Santuario Clínico" (Perfil Maestro) de un paciente.
     */
    public function show(Patient $patient)
    {
        $clinicId = session('active_clinic_id');
        abort_if(!$patient->clinics()->where('clinic_id', $clinicId)->exists(), 403, 'Acceso denegado');

        // Cargamos al paciente CON su historial médico (si es que ya tiene uno)
        $patient->load('medicalHistory');

        return Inertia::render('Doctor/MedicalRecords/Show', [
            'patient' => $patient
        ]);
    }

    /**
     * Guarda o actualiza los antecedentes de la NOM-004.
     */
    public function update(Request $request, Patient $patient)
    {
        $clinicId = session('active_clinic_id');
        abort_if(!$patient->clinics()->where('clinic_id', $clinicId)->exists(), 403, 'Acceso denegado');

        $request->validate([
            'alergias' => 'nullable|string',
            'personales_patologicos' => 'nullable|string',
            'antecedentes_heredofamiliares' => 'nullable|string',
            'personales_no_patologicos' => 'nullable|string',
            'antecedentes_gineco_obstetricos' => 'nullable|string', // <--- AGREGAR VALIDACIÓN
        ]);

        $patient->medicalHistory()->updateOrCreate(
            ['patient_id' => $patient->id],
            [
                'alergias' => $request->alergias,
                'personales_patologicos' => $request->personales_patologicos,
                'antecedentes_heredofamiliares' => $request->antecedentes_heredofamiliares,
                'personales_no_patologicos' => $request->personales_no_patologicos,
                'antecedentes_gineco_obstetricos' => $request->antecedentes_gineco_obstetricos, // <--- AGREGAR GUARDADO
            ]
        );

        return redirect()->route('medical-records.show', $patient->id);
    }
}