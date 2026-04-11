<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();

            // Relaciones de Aislamiento e Integridad (NOM-024)
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');

            // Somatometría
            $table->decimal('peso', 5, 2)->comment('Peso en Kilogramos');
            $table->decimal('talla', 4, 2)->comment('Estatura en Metros (Ej. 1.75)');

            // Signos Vitales (NOM-004)
            $table->integer('presion_sistolica')->comment('Presión Alta (Ej. 120)');
            $table->integer('presion_diastolica')->comment('Presión Baja (Ej. 80)');
            $table->integer('frecuencia_cardiaca')->comment('Latidos por minuto (LPM)');
            $table->integer('frecuencia_respiratoria')->comment('Respiraciones por minuto (RPM) - Exigido por NOM-004');
            $table->decimal('temperatura', 4, 1)->comment('Temperatura en °C');
            $table->integer('oxigenacion')->comment('Porcentaje de SpO2');

            // Trazabilidad (Cuándo se tomó exactamente)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
    }
};
