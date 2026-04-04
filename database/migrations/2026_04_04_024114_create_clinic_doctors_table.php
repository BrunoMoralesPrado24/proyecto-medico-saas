<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_doctor', function (Blueprint $table) {
            $table->id();
            
            // Llaves Foráneas
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            
            // Datos Operativos
            $table->decimal('precio_consulta', 8, 2);
            $table->boolean('is_active')->default(true); // Control de acceso a la sucursal
            
            $table->timestamps();

            // Candado: Un doctor no puede registrarse dos veces en la misma clínica
            $table->unique(['clinic_id', 'doctor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_doctor');
    }
};