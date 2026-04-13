<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\MedicalEncrypt;
use Spatie\Activitylog\Models\Concerns\LogsActivity; // Trait actualizado para v5
use Spatie\Activitylog\Support\LogOptions;            // Clase LogOptions en su nueva ubicación

class Consultation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'patient_id', 'clinic_id', 'user_id',
        'motivo_consulta', 'exploracion_fisica', 'diagnostico', 'tratamiento'
    ];

    protected $casts = [
        'motivo_consulta' => MedicalEncrypt::class,
        'exploracion_fisica' => MedicalEncrypt::class,
        'diagnostico' => MedicalEncrypt::class,
        'tratamiento' => MedicalEncrypt::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['patient_id', 'diagnostico', 'tratamiento'])
            ->logOnlyDirty()
            // <- Aquí borramos el dontSubmitEmptyLogs() que daba el error
            ->setDescriptionForEvent(fn(string $eventName) => "Consulta {$eventName}");
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class);
    }
}