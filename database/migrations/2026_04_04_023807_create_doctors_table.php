<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            
            // Relación 1 a 1 con el usuario. El unique() asegura que un User no tenga dos perfiles de Doctor.
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            
            // Datos Legales (NOM-024)
            $table->string('cedula_profesional', 20);
            $table->string('cedula_especialidad', 20)->nullable();
            $table->string('universidad_egreso');
            
            // Expediente Profesional (Especialidades, biografía, etc.)
            $table->jsonb('info_medico')->nullable();
            
            // Verificación de Identidad
            $table->string('documento_identidad_path', 2048)->nullable();
            $table->boolean('is_verified')->default(false); // Candado de seguridad
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};