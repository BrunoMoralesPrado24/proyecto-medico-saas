<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Inyectamos el CURP en la tabla de Usuarios (Para médicos y pacientes)
        Schema::table('users', function (Blueprint $table) {
            // El CURP mexicano siempre tiene 18 caracteres.
            // Lo hacemos nullable temporalmente para no romper los usuarios que ya creaste.
            $table->string('curp', 18)->unique()->nullable()->after('email');
        });

        // 2. Inyectamos la CLUES en la tabla de Clínicas
        Schema::table('clinics', function (Blueprint $table) {
            // La CLUES tiene 11 caracteres alfanuméricos.
            $table->string('clues', 11)->unique()->nullable()->after('nombre');
        });
    }

    public function down(): void
    {
        // Si nos arrepentimos, Laravel sabrá cómo quitar estas columnas
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('curp');
        });

        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('clues');
        });
    }
};
