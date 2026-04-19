<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient; // El paciente clínico creado por el médico
use Illuminate\Http\Request;
use Inertia\Inertia;
use Vinkla\Hashids\Facades\Hashids;
use Carbon\Carbon;

class MedicalRecordController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtenemos el ID del perfil activo de la sesión (Lo que hiciste en la Tarjeta 13)
        $profileId = session('active_patient_profile_id');
        abort_if(!$profileId, 403, 'No has seleccionado un perfil.');

        // Buscamos el perfil del usuario para sacar su nombre y CURP
        $userProfile = auth()->user()->profiles()->findOrFail($profileId);
        $curp = $userProfile->curp;

        // 2. EL MATCH MAGICO: Buscamos si algún doctor ya registró a este paciente por su CURP
        // Traemos su historial (alergias) y sus consultas que SÍ tengan receta.
        $clinicalPatient = Patient::with(['medicalHistory', 'consultations' => function($query) {
            $query->has('prescription')->with(['prescription', 'doctor']);
        }])->where('curp', $curp)->first();

        // 3. Formateamos las recetas para la vista (Ocultamos el ID real con Hashids)
        $recetas = [];
        $historial = null;

        if ($clinicalPatient) {
            $historial = $clinicalPatient->medicalHistory;

            // Recorremos las consultas para armar la tabla de recetas limpia para Vue
            $recetas = $clinicalPatient->consultations->map(function ($consulta) {
                $receta = $consulta->prescription;
                
                // Calculamos los días transcurridos desde que se emitió
                $diasTranscurridos = $receta->emitted_at->diffInDays(now());
                $isExpired = $diasTranscurridos > 3; // Nuestra regla de los 3 días
                
                return [
                    // ENCRIPTAMOS EL ID AQUÍ PARA LA URL DE DESCARGA
                    'hash_id' => Hashids::encode($consulta->id), 
                    'folio' => $receta->folio,
                    'fecha' => $receta->emitted_at->format('d/m/Y'),
                    'doctor' => $consulta->doctor->name,
                    'is_expired' => $isExpired,
                    'dias_restantes' => $isExpired ? 0 : (3 - $diasTranscurridos),
                ];
            })->sortByDesc('fecha')->values();
        }

        // 4. Mandamos todo a la vista del Paciente
        return Inertia::render('Patient/Boveda', [
            'profileName' => $userProfile->name ?? 'Paciente',
            'curp' => $curp,
            'historial' => $historial,
            'recetas' => $recetas
        ]);
    }

    public function printPrescription($hash)
    {
        // 1. Decodificar el Hash seguro
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($hash);
        abort_if(empty($decoded), 404, 'Receta no encontrada o enlace inválido.');
        $consultationId = $decoded[0];

        // 2. Traer la consulta con sus relaciones
        $consultation = \App\Models\Consultation::with(['patient', 'doctor', 'prescription.items', 'clinic'])->findOrFail($consultationId);

        // 3. SEGURIDAD ESTRICTA: ¿El CURP de la receta pertenece al perfil activo de la sesión?
        $profileId = session('active_patient_profile_id');
        abort_if(!$profileId, 403, 'Sesión de paciente inválida.');
        
        $userProfile = auth()->user()->profiles()->find($profileId);
        abort_if($consultation->patient->curp !== $userProfile->curp, 403, 'No tienes permiso para ver este documento médico.');
        abort_if(!$consultation->prescription, 404, 'Esta consulta no tiene receta generada.');

        // 4. Datos del Doctor y Clínica (Para la hoja membretada)
        $clinic = $consultation->clinic;
        $doctorProfile = \App\Models\Doctor::where('user_id', $consultation->user_id)->first();
        $vitalSign = \App\Models\VitalSign::where('patient_id', $consultation->patient_id)
            ->where('clinic_id', $consultation->clinic_id)
            ->whereDate('created_at', $consultation->created_at->toDateString())
            ->latest()
            ->first();

        // 5. Determinar estado de vigencia (Para la marca de agua roja)
        $diasTranscurridos = $consultation->prescription->emitted_at->diffInDays(now());
        $isExpired = $diasTranscurridos > 3;

        // 6. Generar el PDF reutilizando la vista del doctor, pero pasándole "Banderas"
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.prescription', [
            'patient' => $consultation->patient,
            'consultation' => $consultation,
            'clinic' => $clinic,
            'doctorUser' => $consultation->doctor,
            'doctorProfile' => $doctorProfile,
            'vitalSign' => $vitalSign,
            'prescription' => $consultation->prescription,
            
            // 🔥 BANDERAS DE SEGURIDAD PARA LA VISTA 🔥
            'isPatientCopy' => true,
            'isExpired' => $isExpired
        ]);

        return $pdf->stream('Receta_Digital_' . $consultation->patient->nombre . '.pdf');
    }
}