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
        Schema::create('medical_histories', function (Blueprint $table) {
            $table->id();
            // Relación 1 a 1 obligatoria. Si borras al paciente, se borra su historial
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            
            // Campos NOM-004 (Todos en formato TEXT y nullables por si apenas los van a llenar)
            $table->text('antecedentes_heredofamiliares')->nullable();
            $table->text('personales_patologicos')->nullable();
            $table->text('personales_no_patologicos')->nullable();
            $table->text('alergias')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_histories');
    }
};
