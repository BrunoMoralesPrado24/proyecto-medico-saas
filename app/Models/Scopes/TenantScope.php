<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{/**
     * Aplica el scope a todas las consultas de Eloquent.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // Ignorar si estamos corriendo comandos en la terminal (migraciones/seeders)
        if (app()->runningInConsole()) {
            return;
        }

        // Aplicar el filtro SQL de seguridad
        if (Auth::hasUser() && Auth::user()->clinic_id) {
            $builder->where($model->getTable() . '.clinic_id', Auth::user()->clinic_id);
        }
    }
}
