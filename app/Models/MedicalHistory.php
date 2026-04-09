<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\MedicalEncrypt; // <-- IMPORTA TU CAST AQUÍ

class MedicalHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'antecedentes_heredofamiliares',
        'personales_patologicos',
        'personales_no_patologicos',
        'alergias',
        'antecedentes_gineco_obstetricos',
    ];

    // LA MAGIA DE LA ENCRIPTACIÓN: 
    // Laravel encripta al guardar y desencripta al leer automáticamente.
    protected $casts = [
        'antecedentes_heredofamiliares' => MedicalEncrypt::class,
        'personales_patologicos' => MedicalEncrypt::class,
        'personales_no_patologicos' => MedicalEncrypt::class,
        'alergias' => MedicalEncrypt::class,
        'antecedentes_gineco_obstetricos' => MedicalEncrypt::class,
    ];

    // Relación de regreso al paciente
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}