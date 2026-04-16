<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Extraemos los roles que mandamos desde el backend
const page = usePage();
const userRoles = computed(() => page.props.user_roles || []);

// Funciones ayudantes para mantener el HTML limpio
const isMedico = computed(() => userRoles.value.includes('medico'));
const isPaciente = computed(() => userRoles.value.includes('paciente_titular'));
</script>

<template>
    <aside class="w-64 bg-gray-900 text-white min-h-screen flex flex-col shadow-xl">
        <div class="h-16 flex items-center justify-center border-b border-gray-800">
            <span class="text-xl font-bold text-blue-400">Nex<span class="text-white">Salud</span></span>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">

            <Link :href="route('dashboard')" class="flex items-center px-4 py-2 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Inicio
            </Link>

            <hr class="border-gray-800 my-4" />

            <template v-if="isMedico">
                <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Panel Médico</p>
                <Link :href="route('appointments.index')" class="flex items-center px-4 py-2 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Mi Agenda
                </Link>

                <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Pacientes</p>
                <Link :href="route('patients.index')" class="flex items-center px-4 py-2 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Gestionar Pacientes
                </Link>
                <Link :href="route('medical-records.index')" class="flex items-center px-4 py-2 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Expedientes Clínicos
                </Link>
                
                <Link :href="route('consultations.index')" class="flex items-center px-4 py-2 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition" :class="{ 'bg-gray-800 text-white font-bold': route().current('consultations.*') }">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Historial de Consultas
                </Link>
            </template>

            <template v-if="isPaciente">
                <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Mi Portal</p>
                <Link href="#" class="flex items-center px-4 py-2 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zM9 15l2 2 4-4"></path></svg>
                    Agendar Cita
                </Link>
                <Link href="#" class="flex items-center px-4 py-2 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Mi Expediente
                </Link>
                <Link href="#" class="flex items-center px-4 py-2 text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    Mis Recetas
                </Link>
            </template>
        </nav>

        <div class="p-4 border-t border-gray-800">
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="w-full text-left px-4 py-2 text-red-400 hover:text-red-300 hover:bg-red-900/20 rounded-lg transition font-medium flex items-center"
            >
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Cerrar Sesión
            </Link>
        </div>
    </aside>
</template>