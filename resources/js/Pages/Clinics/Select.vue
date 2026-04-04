<template>
    <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center">
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-extrabold text-blue-600">MediSaaS</h1>
            <p class="text-gray-500 mt-2">Plataforma de Gestión Médica</p>
        </div>

        <div class="w-full sm:max-w-md bg-white shadow-xl rounded-xl overflow-hidden p-8 border border-gray-100">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Bienvenido, Doctor</h2>
            <p class="text-center text-gray-600 mb-8 text-sm">¿En qué clínica trabajarás hoy?</p>

            <div class="space-y-4">
                <button
                    v-for="clinica in clinicas"
                    :key="clinica.id"
                    @click="seleccionarClinica(clinica.id)"
                    class="w-full group bg-white border-2 border-blue-100 hover:border-blue-600 hover:bg-blue-50 text-left p-4 rounded-lg transition duration-200 ease-in-out flex justify-between items-center"
                >
                    <span class="font-semibold text-gray-700 group-hover:text-blue-700 text-lg">
                        🏥 {{ clinica.nombre }}
                    </span>
                    <span class="text-xs bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-bold opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        Entrar &rarr;
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

// Recibimos las clínicas desde el controlador
defineProps({
    clinicas: Array,
});

// Enviamos la petición POST para guardar la clínica en la sesión
const seleccionarClinica = (id) => {
    router.post(route('clinics.set'), {
        clinic_id: id
    });
};
</script>
