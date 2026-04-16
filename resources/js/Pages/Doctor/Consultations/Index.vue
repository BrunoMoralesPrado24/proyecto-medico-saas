<script setup>
import { Link } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';

defineProps({
    consultations: Object, // Viene paginado
});
</script>

<template>
    <div class="flex h-screen bg-gray-100 font-sans">
        
        <Sidebar />

        <main class="flex-1 overflow-y-auto">
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800 flex items-center">
                            <svg class="w-8 h-8 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            Historial de Consultas
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">Registro inmutable de todas las atenciones médicas brindadas en la clínica.</p>
                    </div>
                    
                    <Link :href="route('medical-records.index')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl transition shadow-md flex items-center transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        + Consulta Rápida
                    </Link>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Paciente</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Motivo (Resumen)</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">Médico</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                
                                <tr v-for="consulta in consultations.data" :key="consulta.id" class="hover:bg-indigo-50 transition group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">
                                            {{ new Date(consulta.created_at).toLocaleDateString('es-MX', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ new Date(consulta.created_at).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }) }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-bold text-xs">
                                                {{ consulta.patient?.nombre.charAt(0).toUpperCase() }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-bold text-gray-900 group-hover:text-indigo-700 transition">{{ consulta.patient?.nombre }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600 line-clamp-1 max-w-xs" :title="consulta.motivo_consulta">
                                            {{ consulta.motivo_consulta }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                            Dr. {{ consulta.doctor?.name }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('consultations.show', { patient: consulta.patient_id, consultation: consulta.id })" 
                                            class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 text-gray-700 hover:text-indigo-700 hover:border-indigo-300 hover:bg-indigo-50 rounded-lg font-semibold transition shadow-sm">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Ver Nota
                                        </Link>
                                    </td>
                                </tr>

                                <tr v-if="consultations.data.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-lg font-medium text-gray-900">Aún no hay consultas en esta clínica</p>
                                        <p class="text-sm mt-1">Da clic en "+ Consulta Rápida" para registrar la primera.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="consultations.links.length > 3" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                            <nav class="flex items-center justify-end">
                                <template v-for="(link, index) in consultations.links" :key="index">
                                    <Link v-if="link.url" :href="link.url" v-html="link.label"
                                        class="px-3 py-1 border text-sm font-medium rounded-md mx-1 transition"
                                        :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'" />
                                    <span v-else v-html="link.label" class="px-3 py-1 border border-gray-200 text-gray-400 text-sm font-medium rounded-md mx-1 cursor-not-allowed bg-gray-50"></span>
                                </template>
                            </nav>
                        </div>
                        
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>