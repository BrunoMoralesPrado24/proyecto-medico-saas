<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\VitalSign;
use App\Models\Patient;
use Illuminate\Http\Request;

class VitalSignController extends Controller
{
    /**
     * Guarda los signos vitales de un paciente específico.
     */
    public function store(Request $request, Patient $patient)
    {
        // 1. Validación estricta de datos (NOM-004)
        $validated = $request->validate([
            'peso' => 'required|numeric|min:1|max:300', // kg
            'talla' => 'required|numeric|min:0.3|max:2.5', // metros
            'presion_sistolica' => 'required|integer|min:50|max:250',
            'presion_diastolica' => 'required|integer|min:30|max:150',
            'frecuencia_cardiaca' => 'required|integer|min:30|max:220',
            'frecuencia_respiratoria' => 'required|integer|min:10|max:60', // Exigencia legal
            'temperatura' => 'required|numeric|min:34|max:42', // °C
            'oxigenacion' => 'required|integer|min:50|max:100', // %
        ], [
            // Mensajes personalizados por si alguien se equivoca
            'numeric' => 'Este campo debe ser un número.',
            'integer' => 'Este campo debe ser un número entero sin decimales.',
        ]);

        // 2. Extraer la clínica activa por seguridad (Aislamiento Multi-Tenant)
        $clinicId = session('active_clinic_id');

        // 3. Guardar en la bóveda
        $vitalSign = VitalSign::create(array_merge($validated, [
            'clinic_id' => $clinicId,
            'patient_id' => $patient->id,
        ]));

        // 4. Devolver una respuesta exitosa
        // Como Paco aún no tiene la vista, devolvemos un redirect 'back' genérico con un mensaje.
        return back()->with('success', 'Signos vitales registrados correctamente.');
    }
}
