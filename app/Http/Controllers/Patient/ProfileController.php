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
     * Guarda un nuevo perfil familiar (Con blindaje de datos)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Validar límite de 5 perfiles (Evita abuso de la DB)
        if ($user->patientProfiles()->count() >= 5) {
            return back()->withErrors(['limit' => 'Has alcanzado el máximo de 5 perfiles por cuenta.']);
        }

        // 🛡️ 2. NORMALIZACIÓN PREVIA: Forzar mayúsculas ANTES de la validación
        // Esto asegura que tanto en DB como en las consultas de validación (unique)
        // el dato sea consistente, ignorando cómo lo haya escrito el usuario en frontend.
        $request->merge([
            'curp' => strtoupper($request->curp),
        ]);

        // 3. Validar datos legales (CURP)
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            // La validación unique ahora buscará el CURP ya en mayúsculas
            'curp' => 'required|string|size:18|unique:patient_profiles,curp',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'required|in:masculino,femenino,otro',
            'parentesco' => 'required|in:titular,hijo,conyuge,padre,madre,otro',
        ]);

        // 4. Sanitizar contra ataques XSS
        $validated['nombre_completo'] = strip_tags($validated['nombre_completo']);

        // 5. Crear el perfil vinculado a la cuenta
        $user->patientProfiles()->create($validated);

        return back()->with('success', 'Perfil creado correctamente');
    }

    /**
     * "Entra" a un perfil específico y guarda el contexto en la sesión
     */
    public function select(PatientProfile $profile)
    {
        // 🛡️ SEGURIDAD MULTI-TENANT: Verificar que el perfil pertenezca al usuario logueado
        if ($profile->user_id !== Auth::id()) {
            abort(403, 'Acceso denegado a este perfil.');
        }

        // Guardamos en la sesión el ID del perfil activo y el rol de navegación
        session([
            'active_patient_profile_id' => $profile->id,
            // Asignamos el rol dinámicamente: Si es el titular, tiene control total.
            // Si es un familiar, navegamos como dependiente (bloquea funciones admin).
            'active_role' => $profile->parentesco === 'titular' ? 'paciente_titular' : 'paciente_dependiente'
        ]);

        return redirect()->route('paciente.dashboard');
    }

    /**
     * 🔥 NUEVO: Eliminación Segura de Perfil (Cumplimiento Legal)
     */
    public function destroy(PatientProfile $profile)
    {
        // 🛡️ SEGURIDAD 1: ¿Es el dueño de la cuenta?
        if ($profile->user_id !== Auth::id()) {
            abort(403, 'No tienes permisos para eliminar este perfil.');
        }

        // 🛡️ SEGURIDAD 2: Protección del Titular
        // Por diseño de la arquitectura y la NOM-004, el expediente raíz no se puede borrar
        // mientras la cuenta de usuario principal exista.
        if ($profile->parentesco === 'titular') {
            return back()->withErrors(['error' => 'No puedes eliminar el perfil titular de la cuenta.']);
        }

        // 🛡️ SEGURIDAD 3: Borrado Lógico (Soft Deletes)
        // Usamos delete() normal de Eloquent, PERO asegúrate de que tu modelo PatientProfile
        // tenga el trait `use SoftDeletes;`. Así el registro no se esfuma de la BD,
        // manteniendo la integridad si el doctor busca su historial pasado.
        $profile->delete();

        // Si por alguna razón el usuario borra el perfil en el que estaba navegando, limpiamos la sesión
        if (session('active_patient_profile_id') == $profile->id) {
            session()->forget('active_patient_profile_id');
            session(['active_role' => 'paciente_titular']); // Lo devolvemos al rol seguro por defecto
        }

        return back()->with('success', 'Familiar eliminado correctamente.');
    }
}
