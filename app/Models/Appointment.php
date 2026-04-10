<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'patient_id',
        'start_time',
        'end_time',
        'reason_for_visit',
        'status',
    ];

    // Convertimos automáticamente las fechas a objetos Carbon para poder compararlas fácil después
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // --- RELACIONES ---

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
