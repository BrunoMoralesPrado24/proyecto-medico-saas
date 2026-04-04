<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'user_id',
        'nombre',
        'fecha_nacimiento',
        'telefono',
        'email',
        'info_paciente',
    ];

    /**
     * Los atributos que deben ser convertidos (Casteados).
     */
    protected $casts = [
        'fecha_nacimiento' => 'date',
        'info_paciente' => 'array', // Transforma el JSONB de PostgreSQL a un Array en PHP
    ];

    /**
     * RELACIÓN: Obtiene el usuario (Guardián/Titular) al que pertenece este paciente.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}