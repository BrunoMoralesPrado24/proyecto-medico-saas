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
                    <span class="font-semibold text-gray-700 group-hover:text-blue-700 text-lg flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6.75h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                        </svg>
                        {{ clinica.nombre }}
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
