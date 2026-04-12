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
        Schema::table('consultations', function (Blueprint $table) {
            // Borramos las 7 columnas viejas en un solo comando
            $table->dropColumn([
                'peso', 
                'talla', 
                'temperatura', 
                'presion_arterial', 
                'frecuencia_cardiaca', 
                'frecuencia_respiratoria', 
                'saturacion_oxigeno'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // El método down siempre debe saber cómo deshacer el cambio
            // por si algún día ejecutas un "rollback"
            $table->string('peso')->nullable();
            $table->string('talla')->nullable();
            $table->string('temperatura')->nullable();
            $table->string('presion_arterial')->nullable();
            $table->string('frecuencia_cardiaca')->nullable();
            $table->string('frecuencia_respiratoria')->nullable();
            $table->string('saturacion_oxigeno')->nullable();
        });
    }
};