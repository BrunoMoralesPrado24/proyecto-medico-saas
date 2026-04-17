<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Clinic extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'admin_id',
        'nombre',
        'join_code',
        'telefono',
        'email',
        'logo_path',
        'razon_social',
        'rfc',
        'calle',
        'num_ext',
        'num_int',
        'colonia',
        'codigo_postal',
        'ciudad',
        'estado',
        'clues',
    ];

    protected static function booted()
    {
        static::creating(function ($clinic) {
            // Si nadie le mandó un código, le inventamos uno seguro de 8 caracteres
            if (empty($clinic->join_code)) {
                $clinic->join_code = strtoupper(Str::random(8));
            }
        });
    }

    /**
     * RELACIÓN: Obtiene el usuario que administra/creó esta clínica.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function patients()
    {
        return $this->belongsToMany(\App\Models\Patient::class, 'clinic_patient')
                    ->withPivot('expediente_fisico')
                    ->withTimestamps();
    }
}
