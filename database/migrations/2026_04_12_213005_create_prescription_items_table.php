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
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            
            $table->string('medicamento'); // Ej. Paracetamol 500mg
            $table->string('dosis'); // Ej. 1 Tableta
            $table->string('frecuencia'); // Ej. Cada 8 horas
            $table->string('duracion'); // Ej. Por 5 días
            $table->text('indicaciones_extra')->nullable(); // Ej. Tomar con alimentos
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
