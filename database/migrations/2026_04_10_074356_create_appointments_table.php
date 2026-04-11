<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            // 1. Relaciones Multi-Tenant (El Aislamiento)
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');

            // 2. Datos de la Cita
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('reason_for_visit')->nullable();

            // 3. Control de Estado
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');

            // 4. Trazabilidad (Auditoría médica)
            $table->timestamps();
            $table->softDeletes(); // Nunca borramos citas de verdad por temas legales
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
