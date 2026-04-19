<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue'; // Asumiendo que el Sidebar se adapta al rol

const props = defineProps({
    profileName: String,
    curp: String,
    historial: Object, // Trae alergias, patológicos, etc.
    recetas: Array,    // El array que procesamos en el controlador con hash_id
});

// Función para obtener el color del badge de vigencia
const getStatusClass = (isExpired) => {
    return isExpired 
        ? 'bg-red-100 text-red-700 border-red-200' 
        : 'bg-emerald-100 text-emerald-700 border-emerald-200';
};
</script>

<template>
    <Head :title="`Bóveda de ${profileName}`" />

    <div class="flex h-screen bg-gray-50 font-sans overflow-hidden">
        
        <Sidebar />

        <main class="flex-1 overflow-y-auto">
            
            <div class="bg-white border-b border-gray-200 px-8 py-6 shadow-sm">
                <div class="max-w-5xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                            <svg class="w-8 h-8 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Bóveda Digital: {{ profileName }}
                        </h1>
                        <p class="text-sm text-gray-500 mt-1 font-medium italic">CURP: {{ curp }}</p>
                    </div>
                    
                    <div class="flex gap-3">
                        <Link :href="route('patient.profiles.index')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold hover:bg-gray-200 transition text-sm flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4 4m0 0l-4-4m4 4V3"></path></svg>
                            Cambiar Perfil
                        </Link>
                    </div>
                </div>
            </div>

            <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

                <div v-if="historial" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    
                    <div class="md:col-span-1 bg-red-50 border border-red-100 rounded-2xl p-6 shadow-sm">
                        <h3 class="text-red-800 font-bold text-sm uppercase tracking-wider flex items-center mb-4">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Alergias
                        </h3>
                        <p class="text-red-900 font-bold text-lg leading-tight">
                            {{ historial.alergias || 'Sin registros' }}
                        </p>
                    </div>

                    <div class="md:col-span-2 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <h3 class="text-gray-500 font-bold text-xs uppercase tracking-wider mb-4">Antecedentes Médicos Relevantes</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <span class="block text-[10px] font-black text-gray-400 uppercase">Patológicos</span>
                                <p class="text-sm text-gray-800 mt-1 line-clamp-2" :title="historial.personales_patologicos">{{ historial.personales_patologicos || 'Negados' }}</p>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-gray-400 uppercase">Heredofamiliares</span>
                                <p class="text-sm text-gray-800 mt-1 line-clamp-2" :title="historial.antecedentes_heredofamiliares">{{ historial.antecedentes_heredofamiliares || 'Negados' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                            Mis Recetas Médicas
                        </h2>
                    </div>

                    <div class="p-0">
                        <table v-if="recetas.length > 0" class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-[11px] uppercase tracking-widest text-gray-400 font-black border-b border-gray-100">
                                    <th class="px-8 py-4">Folio</th>
                                    <th class="px-8 py-4">Fecha Emisión</th>
                                    <th class="px-8 py-4">Médico Tratante</th>
                                    <th class="px-8 py-4">Estado</th>
                                    <th class="px-8 py-4 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="receta in recetas" :key="receta.folio" class="hover:bg-indigo-50/30 transition group">
                                    <td class="px-8 py-5 font-mono text-xs font-bold text-gray-700">{{ receta.folio }}</td>
                                    <td class="px-8 py-5 text-sm text-gray-600">{{ receta.fecha }}</td>
                                    <td class="px-8 py-5 text-sm font-bold text-gray-800">Dr. {{ receta.doctor }}</td>
                                    <td class="px-8 py-5">
                                        <span :class="getStatusClass(receta.is_expired)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase border shadow-sm">
                                            {{ receta.is_expired ? 'Vencida' : 'Vigente' }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <a :href="route('patient.prescriptions.pdf', receta.hash_id)"
                                           target="_blank"
                                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 transition shadow-md transform group-hover:-translate-y-0.5">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            Descargar PDF
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-else class="py-20 text-center flex flex-col items-center">
                            <div class="bg-gray-100 p-6 rounded-full mb-4">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Aún no tienes recetas emitidas</h3>
                            <p class="text-sm text-gray-500 mt-2 max-w-xs mx-auto">Cuando tu médico genere una receta en NexSalud bajo tu CURP, aparecerá automáticamente aquí.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-12 bg-indigo-900 rounded-2xl p-8 text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl relative overflow-hidden">
                    <div class="relative z-10">
                        <h4 class="text-xl font-bold">¿Tienes dudas sobre tu tratamiento?</h4>
                        <p class="text-indigo-200 mt-2 text-sm max-w-md">Recuerda que este portal es de consulta. Si presentas efectos secundarios o los síntomas persisten, contacta a tu médico de inmediato.</p>
                    </div>
                    <button class="relative z-10 px-6 py-3 bg-white text-indigo-900 font-black rounded-xl hover:bg-indigo-50 transition shadow-lg text-sm">
                        Ayuda y Soporte
                    </button>
                    <svg class="absolute -right-10 -bottom-10 w-64 h-64 text-indigo-800 opacity-50" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                </div>

            </div>
        </main>
    </div>
</template>