<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;

trait BelongsToTenant
{
    /**
     * El método "boot" intercepta los eventos del modelo.
     * @return void
     */
    protected static function bootBelongsToTenant()
    {
        // 1. Agregar el Global Scope para bloquear las lecturas
        static::addGlobalScope(new TenantScope);

        // 2. Interceptar DESPUÉS de la creación para unir el registro a la tabla pivote
        static::created(function ($model) {
            $activeClinicId = session('active_clinic_id');

            if ($activeClinicId) {
                // Attach crea el registro en la tabla pivote (ej. clinic_patient)
                $model->clinics()->attach($activeClinicId);
            }
        });
    }

    /**
     * Define la relación Muchos a Muchos hacia las clínicas.
     * Funciona para Pacientes, Doctores, Citas, etc.
     */
    public function clinics()
    {
        // Asume que la convención de nombres de Laravel es correcta
        // ej. si se usa en Patient, buscará la tabla pivote 'clinic_patient'
        return $this->belongsToMany(\App\Models\Clinic::class);
    }
}
