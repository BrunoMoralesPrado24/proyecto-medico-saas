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

            <Link :href="route('dashboard')" class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                🏠 Inicio
            </Link>

            <hr class="border-gray-800 my-4" />

            <template v-if="isMedico">
                <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Panel Médico</p>
                <Link :href="route('appointments.index')" class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                    📅 Mi Agenda
                </Link>

                <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-2">Pacientes</p>
                <Link :href="route('patients.index')" class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                    📋 Gestionar Pacientes
                </Link>
                <Link :href="route('medical-records.index')" class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                    🫀 Expedientes Clínicos
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

        <div class="p-4 border-t border-gray-800">
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="w-full text-left px-4 py-2 text-red-400 hover:text-red-300 hover:bg-red-900/20 rounded-lg transition font-medium flex items-center"
            >
                <span class="mr-2">🚪</span> Cerrar Sesión
            </Link>
        </div>
    </aside>
</template>
