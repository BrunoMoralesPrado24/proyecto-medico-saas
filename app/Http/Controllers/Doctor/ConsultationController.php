<?php

namespace App\Http\Controllers\Doctor; // <-- Namespace actualizado

use App\Http\Controllers\Controller; // <-- Extiende del base
use App\Models\Consultation;
use App\Models\Patient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ConsultationController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = session('active_clinic_id');

        // Traemos todas las consultas de esta clínica, de la más nueva a la más vieja
        $consultations = Consultation::with(['patient:id,nombre', 'doctor:id,name'])
            ->where('clinic_id', $clinicId)
            ->latest()
            ->paginate(15); // Paginación vital para el rendimiento

        return Inertia::render('Doctor/Consultations/Index', [
            'consultations' => $consultations
        ]);
    }

    public function create(Patient $patient)
    {
        $clinicId = session('active_clinic_id');
        abort_if(!$patient->clinics()->where('clinic_id', $clinicId)->exists(), 403, 'Acceso denegado');

        // Cargamos al paciente junto con su historial para el panel lateral de Vue
        $patient->load('medicalHistory');

        return Inertia::render('Doctor/Consultations/Create', [
            'patient' => $patient
        ]);
    }

    public function store(Request $request, Patient $patient)
    {
        $clinicId = session('active_clinic_id');
        abort_if(!$patient->clinics()->where('clinic_id', $clinicId)->exists(), 403, 'Acceso denegado');

        $request->validate([
            'peso' => 'nullable|string|max:50',
            'talla' => 'nullable|string|max:50',
            'temperatura' => 'nullable|string|max:50',
            'presion_arterial' => 'nullable|string|max:50',
            'frecuencia_cardiaca' => 'nullable|string|max:50',
            'frecuencia_respiratoria' => 'nullable|string|max:50',
            'saturacion_oxigeno' => 'nullable|string|max:50',
            
            'motivo_consulta' => 'required|string', // Obligatorio por lógica básica
            'exploracion_fisica' => 'nullable|string',
            'diagnostico' => 'nullable|string',
            'tratamiento' => 'nullable|string',
        ]);

        Consultation::create([
            'patient_id' => $patient->id,
            'clinic_id' => $clinicId,
            'user_id' => Auth::id(), // Registramos al doctor
            
            'peso' => $request->peso,
            'talla' => $request->talla,
            'temperatura' => $request->temperatura,
            'presion_arterial' => $request->presion_arterial,
            'frecuencia_cardiaca' => $request->frecuencia_cardiaca,
            'frecuencia_respiratoria' => $request->frecuencia_respiratoria,
            'saturacion_oxigeno' => $request->saturacion_oxigeno,

            'motivo_consulta' => $request->motivo_consulta,
            'exploracion_fisica' => $request->exploracion_fisica,
            'diagnostico' => $request->diagnostico,
            'tratamiento' => $request->tratamiento,
        ]);

        return redirect()->route('medical-records.show', $patient->id)
                         ->with('message', 'Nota médica guardada correctamente.');
    }

    public function show(Patient $patient, Consultation $consultation)
    {
        $clinicId = session('active_clinic_id');
        
        // 1. Verificamos que el paciente pertenezca a la clínica actual
        abort_if(!$patient->clinics()->where('clinic_id', $clinicId)->exists(), 403, 'Acceso denegado');
        
        // 2. Verificamos que esta consulta realmente sea de este paciente
        abort_if($consultation->patient_id !== $patient->id, 404, 'Consulta no encontrada para este paciente');

        // 3. Cargamos los datos extra que necesitamos mostrar
        $patient->load('medicalHistory');
        $consultation->load('doctor:id,name'); // Para saber qué doctor la firmó

        return Inertia::render('Doctor/Consultations/Show', [
            'patient' => $patient,
            'consultation' => $consultation
        ]);
    }
}