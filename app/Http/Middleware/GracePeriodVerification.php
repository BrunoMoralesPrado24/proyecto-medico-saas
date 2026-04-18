<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GracePeriodVerification
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // 1. Si no hay usuario o si la verificación está apagada en el archivo .env, lo dejamos pasar.
        if (!$user || !env('REQUIRE_EMAIL_VERIFICATION', true)) {
            return $next($request);
        }

        // 2. Si ya está verificado, pasa libremente.
        if ($user->hasVerifiedEmail()) {
            return $next($request);
        }

        // 3. Calculamos cuántos días han pasado desde su registro
        $daysSinceRegistration = $user->created_at->diffInDays(Carbon::now());

        // 4. Si ya pasaron los 5 días de gracia y sigue sin verificar, lo bloqueamos
        if ($daysSinceRegistration > 5) {
            // Lo mandamos a la pantalla que le dice "Tu tiempo expiró, revisa tu correo"
            return redirect()->route('verification.notice')
                ->with('error', 'Tu periodo de gracia expiró. Debes verificar tu correo para continuar.');
        }

        // 5. Si está dentro de los 5 días, lo dejamos pasar al sistema (pero el Frontend le mostrará la alerta)
        return $next($request);
    }
}
