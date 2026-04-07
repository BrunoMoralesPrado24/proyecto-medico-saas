<template>
    <div class="max-w-3xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Registrar Nuevo Paciente</h2>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-6 mt-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-gray-700">Nombre Completo</label>
                        <input v-model="form.nombre" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <div v-if="form.errors.nombre" class="text-red-500 text-sm mt-1">{{ form.errors.nombre }}</div>
                    </div>

                    <div>
                        <label class="block text-gray-700">Fecha de Nacimiento</label>
                        <input v-model="form.fecha_nacimiento" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <div v-if="form.errors.fecha_nacimiento" class="text-red-500 text-sm mt-1">{{ form.errors.fecha_nacimiento }}</div>
                    </div>

                    <div>
                        <label class="block text-gray-700">Teléfono</label>
                        <input v-model="form.telefono" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <div>
                        <label class="block text-gray-700">Correo Electrónico</label>
                        <input v-model="form.email" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>

                <div class="mt-8 bg-blue-50 p-4 rounded-md border border-blue-100">
                    <label class="flex items-center">
                        <input v-model="form.privacy_notice" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                        <span class="ml-2 text-sm text-gray-700">
                            El paciente ha leído y aceptado el <strong>Aviso de Privacidad</strong> de esta clínica (LFPDPPP).
                        </span>
                    </label>
                    <div v-if="form.errors.privacy_notice" class="text-red-500 text-sm mt-1">{{ form.errors.privacy_notice }}</div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" class="px-6 py-2 leading-5 text-white transition-colors duration-200 transform bg-gray-800 rounded-md hover:bg-gray-700 focus:outline-none focus:bg-gray-600" :disabled="form.processing">
                        Guardar Expediente
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    nombre: '',
    fecha_nacimiento: '',
    telefono: '',
    email: '',
    privacy_notice: false, // Inicia desmarcado por seguridad
});

const submit = () => {
    form.post(route('patients.store'));
};
</script>
