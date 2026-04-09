<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre', 
        'fecha_nacimiento', 
        'sexo', // <-- NUEVO
        'telefono', 
        'email', 
        'estado_civil', // <-- NUEVO
        'ocupacion', // <-- NUEVO
        'religion', // <-- NUEVO
        'user_id', 
        'privacy_notice_accepted_at'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'privacy_notice_accepted_at' => 'datetime', // <-- NUEVO
        'info_paciente' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * RELACIÓN: Obtiene las clínicas donde este paciente tiene un expediente abierto.
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_patient')
                    ->withPivot('expediente_fisico')
                    ->withTimestamps();
    }

    /**
     * Obtiene el historial médico confidencial del paciente.
     */
    public function medicalHistory()
    {
        return $this->hasOne(MedicalHistory::class);
    }


}
