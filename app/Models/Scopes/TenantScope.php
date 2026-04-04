<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Aplica el scope a todas las consultas de Eloquent.
     * Adaptado para arquitectura Multi-Workspace (Muchos a Muchos).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // 1. Omitir en consola (Artisan)
        if (app()->runningInConsole()) {
            return;
        }

        // 2. Leer la "Clínica Activa" de la sesión del usuario
        $activeClinicId = session('active_clinic_id');

        // 3. Si hay una clínica activa, filtramos usando la relación de la tabla pivote
        if ($activeClinicId) {
            $builder->whereHas('clinics', function ($query) use ($activeClinicId) {
                $query->where('clinics.id', $activeClinicId);
            });
        }
    }
}
