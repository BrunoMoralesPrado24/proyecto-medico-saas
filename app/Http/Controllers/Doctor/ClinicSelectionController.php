<?php

namespace App\Http\Controllers\Doctor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Models\Clinic;
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

        return Inertia::render('Doctor/Clinics/Select', [
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

   public function store(Request $request)
    {
        $request->validate([
            'clues' => 'required|string|max:11',
            'clinic_name' => 'required|string|max:255',
        ]);

        $user = auth()->user(); // Obtenemos al usuario logueado

        // 1. Busca si la CLUES ya existe, si no, crea la clínica
        $clinic = Clinic::firstOrCreate(
            ['clues' => strtoupper($request->clues)],
            [
                'nombre' => $request->clinic_name,
                'admin_id' => $user->id // <-- SOLUCIÓN: El doctor que la crea es el admin
            ]
        );

        // 2. Vinculamos al doctor actual con esta clínica (sin borrar las que ya tenía)
        $doctor = Doctor::where('user_id', $user->id)->first();
        $doctor->clinics()->syncWithoutDetaching([
            $clinic->id => [
                'precio_consulta' => 0.00 // <-- FIX: Precio por defecto
            ]
        ]);

        // 3. Recargamos la página para que aparezca en su lista
        return redirect()->back()->with('success', 'Clínica agregada y vinculada con éxito.');
    }

}
