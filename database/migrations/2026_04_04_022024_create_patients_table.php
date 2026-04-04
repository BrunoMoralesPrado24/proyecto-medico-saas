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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            
            // Llave foránea hacia el Usuario Titular / Guardián
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Datos Estructurados
            $table->string('nombre');
            $table->date('fecha_nacimiento');
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            
            // El JSONB para el expediente estático
            $table->jsonb('info_paciente')->nullable();
            
            // Marcas de tiempo y Soft Deletes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};