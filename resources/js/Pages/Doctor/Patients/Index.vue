<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';

// 1. Recibimos los filters (el texto de búsqueda) desde el controlador
const props = defineProps({
    patients: Object, 
    filters: Object,
});

const page = usePage();
const showSuccessModal = ref(false);

// 2. Lógica del Buscador en tiempo real
const search = ref(props.filters?.search || '');
let typingTimer;

watch(search, (value) => {
    clearTimeout(typingTimer);
    
    // Esperamos 300ms antes de consultar a la base de datos
    typingTimer = setTimeout(() => {
        router.get(route('patients.index'), { search: value }, {
            preserveState: true, // No recarga la página
            replace: true        // No ensucia el historial del navegador
        });
    }, 300);
});

// Detectamos si el controlador mandó un ID nuevo a través de la sesión flash
const newPatientId = computed(() => page.props.flash?.new_patient_id);

onMounted(() => {
    if (newPatientId.value) {
        showSuccessModal.value = true;
    }
});
</script>

<template>
    <div class="flex h-screen bg-gray-100 font-sans">
        
        <Sidebar />

        <div class="flex-1 overflow-y-auto">
            
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-gray-800">Directorio de Pacientes</h2>
                    <Link :href="route('patients.create')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition shadow-sm">
                        + Nuevo Paciente
                    </Link>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200">
                    
                    <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <div class="relative w-full sm:w-1/3">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input v-model="search" type="text" placeholder="Buscar por nombre o teléfono..." 
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                        </div>
                        
                        <div v-if="$inertia?.processing" class="text-sm text-gray-400 flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Buscando...
                        </div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Expediente</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Teléfono</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="patient in patients.data" :key="patient.id" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600">
                                    {{ patient.pivot.expediente_fisico }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ patient.nombre }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ patient.telefono || 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="route('patients.edit', patient.id)" class="text-indigo-600 hover:text-indigo-900 font-bold bg-indigo-50 px-3 py-1 rounded-md transition">Editar</Link>
                                </td>
                            </tr>
                            
                            <tr v-if="patients.data.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    <span v-if="search" class="font-medium text-gray-600">No se encontró a nadie llamado "{{ search }}"</span>
                                    <span v-else>No hay pacientes registrados en esta clínica.</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="patients.links.length > 3" class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Mostrando <span class="font-medium">{{ patients.from || 0 }}</span> a <span class="font-medium">{{ patients.to || 0 }}</span> de <span class="font-medium">{{ patients.total }}</span> resultados
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <template v-for="(link, index) in patients.links" :key="index">
                                        <Link v-if="link.url" :href="link.url" v-html="link.label"
                                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium transition"
                                            :class="link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'" />
                                        
                                        <span v-else v-html="link.label" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-gray-100 text-gray-400 text-sm font-medium cursor-not-allowed"></span>
                                    </template>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <transition name="modal-fade">
            <div v-if="showSuccessModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
                <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-all">
                    
                    <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">¡Paciente Creado!</h2>
                    <p class="text-gray-500 mb-8 text-sm">El expediente administrativo está listo. ¿Deseas completar la información médica (NOM-004) en este momento?</p>

                    <div class="flex flex-col gap-3">
                        <Link :href="route('medical-records.show', newPatientId)" class="w-full py-3 bg-blue-600 text-white rounded-xl font-bold shadow-lg hover:bg-blue-700 transition transform hover:-translate-y-0.5 flex justify-center items-center">
                            🩺 Llenar Expediente Clínico
                        </Link>
                        <button @click="showSuccessModal = false" class="w-full py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition">
                            Volver al listado
                        </button>
                    </div>
                </div>
            </div>
        </transition>

    </div>
</template>

<style scoped>
.modal-fade-enter-active, .modal-fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.modal-fade-enter-from, .modal-fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>