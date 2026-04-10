<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index()
    {
        $clinicId = session('active_clinic_id');
        $doctorId = Auth::user()->doctor->id; // Obtenemos el ID del perfil de doctor

        // Traemos TODAS las citas de ESTA clínica y de ESTE doctor
        $appointments = Appointment::with('patient') // Traemos también los datos del paciente
            ->where('clinic_id', $clinicId)
            ->where('doctor_id', $doctorId)
            ->get();

        // Mandamos los datos a la vista de Vue (que crearemos en el siguiente paso)
        return Inertia::render('Doctor/Appointments/Index', [
            'appointments' => $appointments,
            // Mandamos los pacientes para que el doctor pueda seleccionarlos en el select del modal
            'patients' => Patient::whereHas('clinics', function($q) use ($clinicId) {
                $q->where('clinics.id', $clinicId);
            })->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'reason_for_visit' => 'nullable|string|max:255',
        ]);

        $clinicId = session('active_clinic_id');
        $doctorId = Auth::user()->doctor->id;

        $start = Carbon::parse($request->start_time);
        $end = Carbon::parse($request->end_time);

        // ==========================================
        // 🛑 LA LÓGICA ANTI-OVERBOOKING (Checklist 3)
        // ==========================================
        $conflict = Appointment::where('clinic_id', $clinicId)
            ->where('doctor_id', $doctorId)
            ->where(function ($query) use ($start, $end) {
                // Revisa si hay choque de horarios
                $query->where(function ($q) use ($start, $end) {
                    $q->where('start_time', '<', $end)
                      ->where('end_time', '>', $start);
                });
            })->exists();

        if ($conflict) {
            // Si hay choque, devolvemos un error que Vue podrá leer y mostrar
            return back()->withErrors([
                'start_time' => 'Ya tienes una cita agendada que choca con este horario.'
            ]);
        }

        // Si pasó la aduana, guardamos la cita
        Appointment::create([
            'clinic_id' => $clinicId,
            'doctor_id' => $doctorId,
            'patient_id' => $request->patient_id,
            'start_time' => $start,
            'end_time' => $end,
            'reason_for_visit' => $request->reason_for_visit,
            'status' => 'confirmed', // Por defecto la confirmamos si la hace el doctor
        ]);

        return back()->with('success', 'Cita agendada correctamente.');
    }
}
