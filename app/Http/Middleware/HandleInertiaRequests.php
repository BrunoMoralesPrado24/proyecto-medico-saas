<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $prefijo = 'Dr(e).'; // Valor por defecto, por si acaso no tiene sexo definido

        // 1. Calculamos el prefijo si el usuario logueado es un médico
        if ($user && $user->hasRole('medico')) {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
            
            if ($doctor && $doctor->sexo) {
                $sexo = strtolower($doctor->sexo);
                if ($sexo === 'femenino') {
                    $prefijo = 'Dra.';
                } elseif ($sexo === 'masculino') {
                    $prefijo = 'Dr.';
                }
            }
        }

        // 2. Traemos los datos que Laravel/Inertia ya comparten por defecto
        $shared = parent::share($request);

        // 3. Extendemos el objeto auth.user de forma segura sin borrar lo que ya trae
        $authUser = $shared['auth']['user'] ?? ($user ? $user->toArray() : null);
        
        if ($authUser) {
            $authUser['prefijo_medico'] = $prefijo;
        }

        return [
            ...$shared,
            
            // Inyectamos nuestro auth.user modificado
            'auth' => [
                ...($shared['auth'] ?? []),
                'user' => $authUser,
            ],
            
            // Mandamos los roles de Spatie al frontend de manera segura
            'user_roles' => $user ? $user->getRoleNames() : [],
        ];
    }
}