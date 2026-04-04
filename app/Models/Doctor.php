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
}