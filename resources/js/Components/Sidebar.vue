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

        <nav class="flex-1 px-4 py-6 space-y-2">
            
            <Link :href="route('dashboard')" class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                🏠 Inicio
            </Link>

            <hr class="border-gray-800 my-4" />

            <template v-if="isMedico">
                <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Panel Médico</p>
                <Link href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                    📅 Mi Agenda
                </Link>

                <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Pacientes</p>
                <Link :href="route('patients.index')" class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                    📋 Gestionar Pacientes
                </Link>
                <Link :href="route('medical-records.index')" class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                    🫀 Expedientes Clínicos
                </Link>
                <Link :href="route('consultations.index')" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-colors" :class="{ 'bg-gray-800 text-white font-bold': route().current('consultations.*') }">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Historial de Consultas
                </Link>
            </template>

            <template v-if="isPaciente">
                <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Mi Portal</p>
                <Link href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                    🗓️ Agendar Cita
                </Link>
                <Link href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                    📁 Mi Expediente
                </Link>
                <Link href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                    💊 Mis Recetas
                </Link>
            </template>

        </nav>
    </aside>
</template>