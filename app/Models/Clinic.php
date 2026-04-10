<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
