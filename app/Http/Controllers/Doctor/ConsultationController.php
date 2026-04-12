<?php

namespace App\Http\Controllers\Doctor; // <-- Namespace actualizado

use App\Http\Controllers\Controller; // <-- Extiende del base
use App\Models\Consultation;
use App\Models\Patient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\VitalSign;

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
        // 1. VALIDACIÓN MAESTRA (Las reglas de T9 + Las reglas de T8)
        $validated = $request->validate([
            // Signos Vitales (Tu mundo - Opcionales por si el doc no los toma)
            'peso' => 'nullable|numeric|min:1|max:300',
            'talla' => 'nullable|numeric|min:0.4|max:3.0',
            'presion_sistolica' => 'nullable|integer|min:50|max:250',
            'presion_diastolica' => 'nullable|integer|min:30|max:150',
            'frecuencia_cardiaca' => 'nullable|integer|min:30|max:220',
            'frecuencia_respiratoria' => 'nullable|integer|min:10|max:60',
            'temperatura' => 'nullable|numeric|min:34|max:42',
            'oxigenacion' => 'nullable|integer|min:50|max:100',

            // SOAP (El mundo de Paco - Motivo es obligatorio)
            'motivo_consulta' => 'required|string',
            'exploracion_fisica' => 'nullable|string',
            'diagnostico' => 'nullable|string',
            'tratamiento' => 'nullable|string',
        ]);

        $clinicId = session('active_clinic_id');

        // 2. GUARDAR SIGNOS VITALES (Si el doctor capturó al menos el peso o la presión)
        if ($request->filled('peso') || $request->filled('presion_sistolica')) {
            VitalSign::create([
                'patient_id' => $patient->id,
                'clinic_id' => $clinicId,
                'peso' => $request->peso,
                'talla' => $request->talla,
                'presion_sistolica' => $request->presion_sistolica,
                'presion_diastolica' => $request->presion_diastolica,
                'frecuencia_cardiaca' => $request->frecuencia_cardiaca,
                'frecuencia_respiratoria' => $request->frecuencia_respiratoria,
                'temperatura' => $request->temperatura,
                'oxigenacion' => $request->oxigenacion,
            ]);
        }

        // 3. GUARDAR CONSULTA SOAP (Dejando vacías las columnas viejas de Paco)
        Consultation::create([
            'patient_id' => $patient->id,
            'clinic_id' => $clinicId,
            'user_id' => auth()->id(),
            // Guardamos solo el texto encriptado
            'motivo_consulta' => $request->motivo_consulta,
            'exploracion_fisica' => $request->exploracion_fisica,
            'diagnostico' => $request->diagnostico,
            'tratamiento' => $request->tratamiento,
        ]);

        // 4. REDIRECCIONAR AL EXPEDIENTE CON ÉXITO
        return redirect()->route('medical-records.show', $patient->id)
                         ->with('success', 'Consulta firmada y signos vitales registrados con éxito.');
    }

    public function show(Patient $patient, Consultation $consultation)
    {
        $clinicId = session('active_clinic_id');

        abort_if(!$patient->clinics()->where('clinic_id', $clinicId)->exists(), 403, 'Acceso denegado');
        abort_if($consultation->patient_id !== $patient->id, 404, 'Consulta no encontrada');

        $patient->load('medicalHistory');
        $consultation->load('doctor:id,name');

        // BUSCAMOS LOS SIGNOS VITALES DEL MISMO DÍA (La integración con lo de Bruno)
        $vitalSign = VitalSign::where('patient_id', $patient->id)
            ->where('clinic_id', $clinicId)
            ->whereDate('created_at', $consultation->created_at->toDateString())
            ->latest()
            ->first();

        return Inertia::render('Doctor/Consultations/Show', [
            'patient' => $patient,
            'consultation' => $consultation,
            'vitalSign' => $vitalSign // Mandamos los signos vitales a la vista
        ]);
    }
}
