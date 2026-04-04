<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveClinic
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Si no está logueado, lo ignora (eso lo maneja otro middleware)
        if (!Auth::check()) {
            return $next($request);
        }

        // 2. Si NO tiene clínica activa en la sesión...
        if (!session()->has('active_clinic_id')) {

            // EXCEPCIÓN: Evitamos un ciclo infinito si ya está en las rutas de seleccionar clínica
            if ($request->routeIs('clinics.select') || $request->routeIs('clinics.set')) {
                return $next($request);
            }

            // Lo mandamos a la fuerza a la sala de selección
            return redirect()->route('clinics.select');
        }

        return $next($request);
    }
}
