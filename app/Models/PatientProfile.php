<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nombre_completo',
        'curp',
        'fecha_nacimiento',
        'genero',
        'parentesco',
        'avatar_color'
    ];

    // Relación inversa: Un perfil pertenece a una cuenta de usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con sus consultas médicas (Fase 2)
    // Nota: Aquí es donde conectaremos las recetas y signos vitales después
    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'patient_id'); // Usaremos el ID del perfil
    }
}
