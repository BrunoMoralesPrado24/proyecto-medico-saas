<template>
    <div class="max-w-3xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Editar Expediente: {{ patient.nombre }}</h2>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-6 mt-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-gray-700">Nombre Completo</label>
                        <input v-model="form.nombre" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>

                    <div>
                        <label class="block text-gray-700">Fecha de Nacimiento</label>
                        <input v-model="form.fecha_nacimiento" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
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

                <div class="flex justify-end mt-6">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" :disabled="form.processing">
                        Actualizar Datos
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    patient: Object,
});

// Precargamos el formulario con los datos que nos mandó el controlador
const form = useForm({
    nombre: props.patient.nombre,
    // Extraemos solo la parte de la fecha "YYYY-MM-DD" cortando la hora
    fecha_nacimiento: props.patient.fecha_nacimiento ? props.patient.fecha_nacimiento.split('T')[0] : '',
    telefono: props.patient.telefono,
    email: props.patient.email,
});

const submit = () => {
    form.put(route('patients.update', props.patient.id));
};
</script>
