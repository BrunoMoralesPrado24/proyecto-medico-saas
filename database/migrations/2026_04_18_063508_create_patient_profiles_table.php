<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_profiles', function (Blueprint $table) {
            $table->id();

            // Relación con la cuenta principal (User)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Datos del perfil del paciente
            $table->string('nombre_completo');
            $table->string('curp', 18)->unique(); // Indispensable para trazabilidad legal
            $table->date('fecha_nacimiento');
            $table->enum('genero', ['masculino', 'femenino', 'otro']);

            // Tipo de perfil dentro de la cuenta
            $table->enum('parentesco', ['titular', 'hijo', 'conyuge', 'padre', 'madre', 'otro']);

            // Para el estilo Netflix
            $table->string('avatar_color')->default('emerald'); // Guardaremos colores o rutas de imagen

            $table->timestamps();
            $table->softDeletes(); // NOM-004 exige conservar datos aunque se "borren"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_profiles');
    }
};
