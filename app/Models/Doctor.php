<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'cedula_profesional',
        'cedula_especialidad',
        'universidad_egreso',
        'info_medico',
        'documento_identidad_path',
        'is_verified',
    ];

    protected $casts = [
        'info_medico' => 'array',
        'is_verified' => 'boolean',
    ];

    /**
     * RELACIÓN: Obtiene la cuenta de usuario de este doctor.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clinics()
    {
        // Si Paco le puso 'clinic_doctor' a su tabla pivote, Laravel la encontrará automáticamente.
        // Usamos withPivot para traernos si está activo y el precio de consulta.
        return $this->belongsToMany(\App\Models\Clinic::class, 'clinic_doctor')
                    ->withPivot('precio_consulta', 'is_active')
                    ->withTimestamps();
    }
}
