<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VitalSign extends Model
{
    //
}<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VitalSign extends Model
{
    use HasFactory;

    // Permitimos la asignación masiva de todos estos campos
    protected $fillable = [
        'clinic_id',
        'patient_id',
        'peso',
        'talla',
        'presion_sistolica',
        'presion_diastolica',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria', // <-- Nuestro nuevo campo blindado
        'temperatura',
        'oxigenacion',
    ];

    // --- RELACIONES ---

    /**
     * Un registro de signos vitales pertenece a un paciente específico.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Un registro de signos vitales se tomó dentro de una clínica específica.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
