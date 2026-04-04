<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ClinicPatient extends Pivot
{
    // Forzamos el nombre exacto de la tabla en la BD
    protected $table = 'clinic_patient';

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'expediente_fisico',
    ];
}