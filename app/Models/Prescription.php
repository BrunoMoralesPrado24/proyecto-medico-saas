<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'folio',
        'emitted_at',
        'is_active',
    ];

    protected $casts = [
        'emitted_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Lógica automática para generar folios únicos
     */
    protected static function booted()
    {
        static::creating(function ($prescription) {
            // Generamos un folio basado en la fecha y un ID aleatorio corto
            // Ej: REC-20260412-A8BC
            $prescription->folio = 'REC-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            $prescription->emitted_at = now();
        });
    }

    // Relación: Una receta pertenece a una consulta
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    // Relación: Una receta tiene muchos medicamentos (items)
    public function items()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

}