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
        // 1. Agregamos los datos demográficos al paciente
        Schema::table('patients', function (Blueprint $table) {
            $table->enum('sexo', ['Masculino', 'Femenino', 'Otro'])->nullable()->after('fecha_nacimiento');
            $table->string('estado_civil')->nullable()->after('email');
            $table->string('ocupacion')->nullable()->after('estado_civil');
            $table->string('religion')->nullable()->after('ocupacion');
        });

        // 2. Agregamos el AGO al historial médico
        Schema::table('medical_histories', function (Blueprint $table) {
            $table->text('antecedentes_gineco_obstetricos')->nullable()->after('alergias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
