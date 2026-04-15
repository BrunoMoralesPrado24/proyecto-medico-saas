<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    proximosPacientes: Array
});

// Calculador rápido de edad
const calcularEdad = (fecha) => {
    if (!fecha) return 'N/A';
    const hoy = new Date();
    const nacimiento = new Date(fecha);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) edad--;
    return edad;
};
</script>

<template>
    <div class="flex h-screen bg-gray-50 font-sans">

        <Sidebar />

        <main class="flex-1 overflow-y-auto">
            <div class="max-w-7xl mx-auto py-8 sm:px-6 lg:px-8">

                <div class="mb-10 flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4 border-b border-gray-200 pb-6">
                    <div>
                        <p class="text-sm font-bold text-blue-600 uppercase tracking-widest mb-1">¡Hola de nuevo!</p>
                        <h1 class="text-3xl font-black text-gray-900 tracking-tight">
                            Dr(a). {{ $page.props.auth.user.name }}
                        </h1>
                        <p class="text-gray-500 mt-2 font-medium">Aquí tienes el resumen de tu clínica y la agenda para hoy.</p>
                    </div>
                    <div class="bg-white px-4 py-2 rounded-xl border border-gray-200 shadow-sm text-center sm:text-right">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Fecha de Operación</p>
                        <p class="text-sm font-bold text-gray-800 capitalize">
                            {{ new Date().toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center hover:shadow-md transition">
                        <div class="p-4 bg-emerald-100 text-emerald-600 rounded-xl mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">Citas para Hoy</p>
                            <p class="text-3xl font-black text-gray-900">{{ stats.citasHoyTotal }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center hover:shadow-md transition">
                        <div class="p-4 bg-blue-100 text-blue-600 rounded-xl mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">Pacientes (Clínica)</p>
                            <p class="text-3xl font-black text-gray-900">{{ stats.totalPacientes }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center hover:shadow-md transition">
                        <div class="p-4 bg-indigo-100 text-indigo-600 rounded-xl mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">Consultas (Este Mes)</p>
                            <p class="text-3xl font-black text-gray-900">{{ stats.consultasMes }}</p>
                        </div>
                    </div>

                </div>

                <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
                    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></span>
                            Sala de Espera (Próximos Pacientes)
                        </h2>
                        <Link :href="route('appointments.index')" class="text-sm font-bold text-blue-600 hover:text-blue-800 transition">Ver Agenda Completa &rarr;</Link>
                    </div>

                    <div v-if="proximosPacientes && proximosPacientes.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Hora</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Paciente</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Motivo Inicial</th>
                                    <th class="px-6 py-4 text-right text-xs font-black text-gray-400 uppercase tracking-wider">Acción Rápida</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-for="cita in proximosPacientes" :key="cita.id" class="hover:bg-blue-50 transition group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800 border border-gray-200">
                                            {{ cita.hora_formateada }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-gradient-to-br from-blue-500 to-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-xs shadow-sm">
                                                {{ cita.patient.nombre.charAt(0).toUpperCase() }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-bold text-gray-900 group-hover:text-blue-700 transition">{{ cita.patient.nombre }}</div>
                                                <div class="text-xs text-gray-500">{{ calcularEdad(cita.patient.fecha_nacimiento) }} años</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600 line-clamp-1 max-w-xs">{{ cita.motivo || 'Consulta General' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('consultations.create', cita.patient.id)"
                                            class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition shadow-sm transform hover:-translate-y-0.5">
                                            🩺 Iniciar Consulta
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="text-center py-16 px-4">
                        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">La sala de espera está vacía</h3>
                        <p class="text-gray-500 mt-1 max-w-sm mx-auto text-sm">No tienes pacientes programados para el resto del día. Puedes buscar pacientes en tu base de datos para consultas espontáneas.</p>
                        <div class="mt-6">
                            <Link :href="route('medical-records.index')" class="text-blue-600 font-bold hover:text-blue-800 bg-blue-50 px-4 py-2 rounded-lg transition">
                                Buscar en Expedientes
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</template>
