<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import { Link } from '@inertiajs/vue3'; // Es mejor usar Link de Inertia que etiquetas <a> normales

defineProps({
    patients: Array,
});
</script>

<template>
    <div class="flex h-screen bg-gray-100">
        
        <Sidebar />

        <div class="flex-1 overflow-y-auto">
            
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-gray-800">Directorio de Pacientes</h2>
                    <Link :href="route('patients.create')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                        + Nuevo Paciente
                    </Link>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expediente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="patient in patients" :key="patient.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                    {{ patient.pivot.expediente_fisico }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ patient.nombre }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ patient.telefono || 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="route('patients.edit', patient.id)" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                </td>
                            </tr>
                            <tr v-if="patients.length === 0">
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No hay pacientes registrados en esta clínica.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</template>