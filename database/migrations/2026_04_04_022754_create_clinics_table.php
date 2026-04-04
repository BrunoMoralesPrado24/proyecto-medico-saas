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
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            
            // Administrador de la clínica (Dueño del Tenant)
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            
            // Datos Comerciales y de Contacto
            $table->string('nombre');
            $table->string('join_code', 10)->unique(); // Código para invitar doctores
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('logo_path', 2048)->nullable();
            
            // Datos Fiscales (Preparando el terreno para CFDI)
            $table->string('razon_social')->nullable();
            $table->string('rfc', 13)->nullable();
            
            // Dirección
            $table->string('calle')->nullable();
            $table->string('num_ext', 50)->nullable();
            $table->string('num_int', 50)->nullable();
            $table->string('colonia')->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('ciudad')->nullable();
            $table->string('estado')->nullable();
            
            // Trazabilidad
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};