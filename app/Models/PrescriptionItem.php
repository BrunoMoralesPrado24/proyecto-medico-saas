<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'medicamento',
        'dosis',
        'frecuencia',
        'duracion',
        'indicaciones_extra',
    ];

    // Relación: Este item pertenece a una receta
    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}