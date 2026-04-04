<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

// Usamos "Pivot" en lugar de "Model" para que Laravel entienda que es una tabla intermedia
class ClinicDoctor extends Pivot 
{
    // Forzamos el nombre exacto de la tabla en la BD
    protected $table = 'clinic_doctor';

    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'precio_consulta',
        'is_active',
    ];

    protected $casts = [
        'precio_consulta' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}