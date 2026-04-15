<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
// 1. Importamos tu Sidebar (Ajusta la ruta según tu proyecto, ej: '@/Components/Sidebar.vue' o '../Sidebar.vue')
import Sidebar from '@/Components/Sidebar.vue'; 

const props = defineProps({
    patients: Array,
});

const search = ref('');

const filteredPatients = computed(() => {
    if (!search.value) return props.patients;
    return props.patients.filter(p =>
        p.nombre.toLowerCase().includes(search.value.toLowerCase())
    );
});

const calcularEdad = (fecha) => {
    if (!fecha) return 'N/A';
    const hoy = new Date();
    const nacimiento = new Date(fecha);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }
    return edad;
};
</script>

<template>
    <div class="flex h-screen bg-gray-100 font-sans">
        
        <Sidebar />

        <main class="flex-1 overflow-y-auto">
            <div class="max-w-6xl mx-auto py-10 sm:px-6 lg:px-8">
                
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800 flex items-center">
                            <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Expedientes Clínicos
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">Selecciona un paciente para ver o actualizar sus antecedentes médicos (NOM-004).</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-6">
                    
                    <div class="mb-6 relative w-full sm:w-1/2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input v-model="search" type="text" placeholder="Buscar paciente por nombre..." 
                            class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
                    </div>

                    <div class="overflow-x-auto border border-gray-100 rounded-xl">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Paciente</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Edad</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                
                                <tr v-for="patient in filteredPatients" :key="patient.id" class="hover:bg-blue-50 transition group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center font-bold shadow-sm">
                                                {{ patient.nombre.charAt(0).toUpperCase() }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900 group-hover:text-blue-700 transition">{{ patient.nombre }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-600">{{ calcularEdad(patient.fecha_nacimiento) }} años</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('medical-records.show', patient.id)" 
                                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:text-blue-700 hover:border-blue-300 hover:bg-blue-50 rounded-lg font-semibold transition shadow-sm group-hover:shadow">
                                            Abrir Expediente
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                            </svg>
                                        </Link>
                                    </td>
                                </tr>

                                <tr v-if="filteredPatients.length === 0">
                                    <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                            <p class="text-lg font-medium">No se encontraron expedientes</p>
                                            <p class="text-sm text-gray-400 mt-1">Verifica el nombre en el buscador.</p>
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>