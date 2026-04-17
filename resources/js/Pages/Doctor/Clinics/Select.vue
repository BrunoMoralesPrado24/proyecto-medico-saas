<template>
    <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-extrabold text-blue-600 tracking-tight">Nex<span class="text-gray-800">Salud</span></h1>
            <p class="text-gray-500 mt-2 font-medium">Plataforma de Gestión Médica</p>
        </div>

        <div class="w-full sm:max-w-md bg-white shadow-2xl rounded-2xl overflow-hidden p-8 border border-gray-100 relative transition-all duration-300">

            <div v-if="!showAddForm">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Bienvenido, Doctor</h2>
                        <p class="text-gray-500 text-sm mt-1">¿En qué clínica trabajarás hoy?</p>
                    </div>
                    <button @click="showAddForm = true" class="bg-blue-50 text-blue-600 hover:bg-blue-100 p-2 rounded-lg transition" title="Agregar nueva clínica">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                </div>

                <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                    <button
                        v-for="clinica in clinicas"
                        :key="clinica.id"
                        @click="seleccionarClinica(clinica.id)"
                        class="w-full group bg-white border-2 border-gray-100 hover:border-blue-500 hover:bg-blue-50 text-left p-4 rounded-xl transition duration-200 ease-in-out flex justify-between items-center shadow-sm hover:shadow-md"
                    >
                        <span class="font-bold text-gray-700 group-hover:text-blue-800 text-base flex items-center gap-3">
                            <div class="bg-blue-100 text-blue-600 p-2 rounded-lg group-hover:bg-blue-200 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6.75h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                                </svg>
                            </div>
                            {{ clinica.nombre }}
                        </span>
                        <span class="text-xs bg-blue-100 text-blue-800 px-3 py-1.5 rounded-full font-bold opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center">
                            Entrar <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    </button>

                    <div v-if="!clinicas || clinicas.length === 0" class="text-center py-6">
                        <p class="text-sm text-gray-500">No tienes clínicas registradas.</p>
                    </div>
                </div>
            </div>

            <div v-else>
                <div class="flex items-center mb-6">
                    <button @click="showAddForm = false" class="text-gray-400 hover:text-gray-600 transition mr-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </button>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Agregar Clínica</h2>
                        <p class="text-gray-500 text-xs mt-1">Registra una nueva sucursal de trabajo</p>
                    </div>
                </div>

                <form @submit.prevent="guardarNuevaClinica" class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">CLUES de la Clínica *</label>
                        <input
                            v-model="form.clues"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 uppercase shadow-sm"
                            placeholder="Ej. MCSSA012345"
                            pattern="^[a-zA-Z]{5}\d{6}$"
                            title="Formato CLUES: 5 letras seguidas de 6 números (Ej. MCSSA012345)"
                            maxlength="11"
                            required
                        >
                        <p v-if="form.errors.clues" class="text-red-500 text-xs mt-1">{{ form.errors.clues }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nombre de la Clínica *</label>
                        <input
                            v-model="form.clinic_name"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                            placeholder="Ej. Hospital Ángeles"
                            required
                        >
                        <p v-if="form.errors.clinic_name" class="text-red-500 text-xs mt-1">{{ form.errors.clinic_name }}</p>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="showAddForm = false" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold hover:bg-gray-200 transition">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition flex justify-center items-center disabled:opacity-50">
                            <span v-if="!form.processing">Guardar Clínica</span>
                            <svg v-else class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

defineProps({
    clinicas: Array,
});

// Controla qué vista mostrar (Lista de clínicas vs Formulario nuevo)
const showAddForm = ref(false);

const form = useForm({
    clues: '',
    clinic_name: '',
});

// Entrar a una clínica existente
const seleccionarClinica = (id) => {
    router.post(route('clinics.set'), {
        clinic_id: id
    });
};

// Crear una nueva clínica en la BD
const guardarNuevaClinica = () => {
    form.post(route('clinics.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAddForm.value = false;
            form.reset();
        }
    });
};
</script>
