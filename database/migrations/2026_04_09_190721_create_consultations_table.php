<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            
            // Llaves foráneas
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->comment('ID del Doctor que atendió');

            // Signos Vitales (Opcionales)
            $table->string('peso')->nullable()->comment('En kg');
            $table->string('talla')->nullable()->comment('En cm o m');
            $table->string('temperatura')->nullable()->comment('En °C');
            $table->string('presion_arterial')->nullable()->comment('Ej. 120/80');
            $table->string('frecuencia_cardiaca')->nullable()->comment('LPM');
            $table->string('frecuencia_respiratoria')->nullable()->comment('RPM');
            $table->string('saturacion_oxigeno')->nullable()->comment('Porcentaje %');

            // El formato SOAP (Texto para encriptación)
            $table->text('motivo_consulta')->nullable();    // (S) Subjetivo
            $table->text('exploracion_fisica')->nullable(); // (O) Objetivo
            $table->text('diagnostico')->nullable();        // (A) Análisis
            $table->text('tratamiento')->nullable();        // (P) Plan

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
