<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_patient', function (Blueprint $table) {
            $table->id();
            
            // Llaves Foráneas
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            
            // Datos Operativos
            $table->string('expediente_fisico', 50)->nullable(); // Control de archivo de papel
            
            $table->timestamps();

            // Candado: Un paciente no puede tener dos expedientes abiertos en la misma sucursal
            $table->unique(['clinic_id', 'patient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_patient');
    }
};