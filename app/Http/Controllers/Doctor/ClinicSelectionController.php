<?php

namespace App\Http\Controllers\Doctor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use Inertia\Inertia;

class ClinicSelectionController extends Controller
{
    public function select()
    {
        // Buscamos al doctor logueado y traemos sus clínicas gracias a la tabla pivote de Paco
        $doctor = Doctor::with('clinics')->where('user_id', Auth::id())->first();

        if (!$doctor || $doctor->clinics->isEmpty()) {
            abort(403, 'No tienes clínicas asignadas en el sistema.');
        }

        return Inertia::render('Clinics/Select', [
            'clinicas' => $doctor->clinics
        ]);
    }

    public function set(Request $request)
    {
        $request->validate(['clinic_id' => 'required|integer']);

        $doctor = Doctor::where('user_id', Auth::id())->first();

        // Verificamos que la clínica que eligió realmente le pertenece (Antihackers)
        if ($doctor->clinics()->where('clinics.id', $request->clinic_id)->exists()) {
            // Le colgamos el gafete en la sesión
            session(['active_clinic_id' => $request->clinic_id]);
            return redirect()->route('dashboard');
        }

        abort(403, 'Acceso no autorizado a esta clínica.');
    }
}
