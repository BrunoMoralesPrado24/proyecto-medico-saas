<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Patient;
use App\Models\Consultation;
use App\Models\Appointment; // Asumiendo que tienes este modelo para las citas
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $clinicId = session('active_clinic_id');

        // 1. Conteo de pacientes totales de la clínica
        $totalPacientes = Patient::whereHas('clinics', function ($query) use ($clinicId) {
            $query->where('clinic_id', $clinicId);
        })->count();

        // 2. Consultas realizadas en el mes actual
        $consultasMes = Consultation::where('clinic_id', $clinicId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // 3. Citas para hoy (Próximos Pacientes)
        $citasHoy = [];
        if (class_exists(Appointment::class)) {
            $citasHoy = Appointment::with('patient:id,nombre,fecha_nacimiento')
                ->where('clinic_id', $clinicId)
                ->whereDate('start_time', Carbon::today()) // <-- Usamos la columna real
                ->whereNotIn('status', ['completed', 'cancelled']) // <-- Ignoramos canceladas y terminadas
                ->orderBy('start_time', 'asc')
                ->take(5)
                ->get()
                ->map(function ($cita) {
                    $cita->scheduled_at = $cita->start_time;
                    $cita->motivo = $cita->reason_for_visit;

                    // 🛑 TRUCO SENIOR: Formateamos la hora directo en el servidor
                    // para que JavaScript no se confunda con la zona horaria.
                    // 'h:i A' nos dará el formato "12:30 PM"
                    $cita->hora_formateada = \Carbon\Carbon::parse($cita->start_time)->format('h:i A');

                    return $cita;
                });
        }

        return Inertia::render('Doctor/Dashboard/Index', [
            'stats' => [
                'totalPacientes' => $totalPacientes,
                'consultasMes' => $consultasMes,
                'citasHoyTotal' => count($citasHoy),
            ],
            'proximosPacientes' => $citasHoy
        ]);
    }
}
