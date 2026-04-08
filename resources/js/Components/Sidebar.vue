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
                <Link :href="route('patients.index')" class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                    👥 Mis Pacientes
                </Link>
                <Link href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                    ✍️ Recetas Emitidas
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