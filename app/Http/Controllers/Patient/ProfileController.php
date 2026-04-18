<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Muestra el selector de perfiles (Estilo Netflix)
     */
    public function index()
    {
        $profiles = Auth::user()->patientProfiles;

        // Si el paciente titular NO tiene perfiles creados aún,
        // lo mandamos a la vista pero con el modal de "Añadir" sugerido.
        return Inertia::render('Patient/ProfileSelector', [
            'profiles' => $profiles,
            'maxProfiles' => 5
        ]);
    }

    /**
     * Guarda un nuevo perfil familiar
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Validar límite de 5 perfiles
        if ($user->patientProfiles()->count() >= 5) {
            return back()->withErrors(['limit' => 'Has alcanzado el máximo de 5 perfiles por cuenta.']);
        }

        // 2. Validar datos legales (CURP)
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'curp' => 'required|string|size:18|unique:patient_profiles,curp',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'required|in:masculino,femenino,otro',
            'parentesco' => 'required|in:titular,hijo,conyuge,padre,madre,otro',
        ]);

        // 3. Crear el perfil vinculado a la cuenta
        $user->patientProfiles()->create($validated);

        // Redirigir de vuelta para que Inertia refresque la lista de perfiles
        return back()->with('success', 'Perfil creado correctamente');
    }

    /**
     * "Entra" a un perfil específico y guarda el contexto en la sesión
     */
    public function select(PatientProfile $profile)
    {
        // Seguridad: Verificar que el perfil pertenezca al usuario logueado
        if ($profile->user_id !== Auth::id()) {
            abort(403);
        }

        // Guardamos en la sesión el ID del perfil activo
        session(['active_patient_profile_id' => $profile->id]);
        session(['active_role' => 'paciente_titular']);

        return redirect()->route('paciente.dashboard');
    }
}
