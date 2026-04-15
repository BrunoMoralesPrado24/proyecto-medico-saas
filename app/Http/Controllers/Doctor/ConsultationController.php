<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\VitalSign;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Clinic;
use App\Models\Doctor;

class ConsultationController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = session('active_clinic_id');

        $consultations = Consultation::with(['patient:id,nombre', 'doctor:id,name'])
            ->where('clinic_id', $clinicId)
            ->latest()
            ->paginate(15);

        return Inertia::render('Doctor/Consultations/Index', [
            'consultations' => $consultations
        ]);
    }

    public function create(Patient $patient)
    {
        $clinicId = session('active_clinic_id');
        abort_if(!$patient->clinics()->where('clinic_id', $clinicId)->exists(), 403, 'Acceso denegado');

        $patient->load('medicalHistory');

        return Inertia::render('Doctor/Consultations/Create', [
            'patient' => $patient
        ]);
    }

    public function store(Request $request, Patient $patient)
    {
        $clinicId = session('active_clinic_id');
        abort_if(!$patient->clinics()->where('clinic_id', $clinicId)->exists(), 403, 'Acceso denegado');

        // 1. VALIDACIÓN MAESTRA (Signos + SOAP + Receta)
        $validated = $request->validate([
            'peso' => 'nullable|numeric|min:1|max:300',
            'talla' => 'nullable|numeric|min:0.4|max:3.0',
            'presion_sistolica' => 'nullable|integer|min:50|max:250',
            'presion_diastolica' => 'nullable|integer|min:30|max:150',
            'frecuencia_cardiaca' => 'nullable|integer|min:30|max:220',
            'frecuencia_respiratoria' => 'nullable|integer|min:10|max:60',
            'temperatura' => 'nullable|numeric|min:34|max:42',
            'oxigenacion' => 'nullable|integer|min:50|max:100',

            'motivo_consulta' => 'required|string',
            'exploracion_fisica' => 'nullable|string',
            'diagnostico' => 'nullable|string',
            'tratamiento' => 'nullable|string',

            // 💊 VALIDACIÓN DINÁMICA DEL RECETARIO
            'medicamentos' => 'nullable|array',
            'medicamentos.*.medicamento' => 'required_with:medicamentos|string|max:255',
            'medicamentos.*.dosis' => 'required_with:medicamentos|string|max:255',
            'medicamentos.*.frecuencia' => 'required_with:medicamentos|string|max:255',
            'medicamentos.*.duracion' => 'required_with:medicamentos|string|max:255',
            'medicamentos.*.indicaciones_extra' => 'nullable|string|max:1000',
        ]);

        // 2. GUARDAR SIGNOS VITALES
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

        // 3. GUARDAR CONSULTA SOAP (Guardamos en variable $consultation para obtener su ID)
        $consultation = Consultation::create([
            'patient_id' => $patient->id,
            'clinic_id' => $clinicId,
            'user_id' => auth()->id(),
            'motivo_consulta' => $request->motivo_consulta,
            'exploracion_fisica' => $request->exploracion_fisica,
            'diagnostico' => $request->diagnostico,
            'tratamiento' => $request->tratamiento,
        ]);

        // 4. GENERAR RECETA MÉDICA (Si el doctor agregó medicamentos)
        if (is_array($request->medicamentos) && count($request->medicamentos) > 0) {

            // Creamos la receta base (el folio se genera automático en el modelo)
            $prescription = Prescription::create([
                'consultation_id' => $consultation->id,
            ]);

            // Recorremos el arreglo y guardamos cada fármaco asociado a esta receta
            foreach ($request->medicamentos as $med) {
                $prescription->items()->create([
                    'medicamento' => $med['medicamento'],
                    'dosis' => $med['dosis'],
                    'frecuencia' => $med['frecuencia'],
                    'duracion' => $med['duracion'],
                    'indicaciones_extra' => $med['indicaciones_extra'] ?? null,
                ]);
            }
        }

        // 5. REDIRECCIONAR A LA VISTA DE LA NOTA RECIÉN CREADA
        return redirect()->route('consultations.show', [
            'patient' => $patient->id,
            'consultation' => $consultation->id
        ])->with('success', 'Consulta y receta firmadas con éxito.');
    }

    public function show(Patient $patient, Consultation $consultation)
    {
        $clinicId = session('active_clinic_id');

        abort_if(!$patient->clinics()->where('clinic_id', $clinicId)->exists(), 403, 'Acceso denegado');
        abort_if($consultation->patient_id !== $patient->id, 404, 'Consulta no encontrada');

        $patient->load('medicalHistory');

        // Aquí también traemos la receta (si es que existe) y sus ítems
        $consultation->load(['doctor:id,name', 'prescription.items']);

        $vitalSign = VitalSign::where('patient_id', $patient->id)
            ->where('clinic_id', $clinicId)
            ->whereDate('created_at', $consultation->created_at->toDateString())
            ->latest()
            ->first();

        return Inertia::render('Doctor/Consultations/Show', [
            'patient' => $patient,
            'consultation' => $consultation,
            'vitalSign' => $vitalSign
        ]);
    }

    public function printPrescription(Patient $patient, Consultation $consultation)
    {
        $clinicId = session('active_clinic_id');

        abort_if(!$patient->clinics()->where('clinic_id', $clinicId)->exists(), 403, 'Acceso denegado');
        abort_if($consultation->patient_id !== $patient->id, 404, 'Consulta no encontrada');

        // Cargamos todo lo necesario para el documento legal
        $consultation->load(['doctor', 'prescription.items']);
        $clinic = Clinic::findOrFail($clinicId);

        // Buscamos el perfil profesional del doctor (Cédula y Universidad)
        $doctorProfile = Doctor::where('user_id', $consultation->user_id)->first();

        // Buscamos los signos vitales de ese día
        $vitalSign = VitalSign::where('patient_id', $patient->id)
            ->where('clinic_id', $clinicId)
            ->whereDate('created_at', $consultation->created_at->toDateString())
            ->latest()
            ->first();

        // Si no hay receta, no hay nada que imprimir
        abort_if(!$consultation->prescription, 404, 'Esta consulta no tiene una receta médica generada.');

        // Renderizamos la vista Blade
        $pdf = Pdf::loadView('pdf.prescription', [
            'patient' => $patient,
            'consultation' => $consultation,
            'clinic' => $clinic,
            'doctorUser' => $consultation->doctor,
            'doctorProfile' => $doctorProfile,
            'vitalSign' => $vitalSign,
            'prescription' => $consultation->prescription
        ]);

        // .stream() abre el PDF en una pestaña nueva para que el doctor decida si imprime o descarga
        return $pdf->stream('Receta_' . $patient->nombre . '_' . date('Y-m-d') . '.pdf');
    }
}
