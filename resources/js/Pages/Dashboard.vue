<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Sidebar from '@/Components/Sidebar.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const userRoles = computed(() => page.props.user_roles || []);
const isMedico = computed(() => userRoles.value.includes('medico'));
</script>

<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar />

        <div class="flex-1 flex flex-col overflow-hidden">

            <header class="h-16 bg-white border-b flex items-center justify-between px-6">
                <h2 class="font-semibold text-xl text-gray-800">
                    Dashboard
                </h2>
                <div class="text-sm text-gray-600">
                    Conectado como: <span class="font-bold">{{ $page.props.auth.user.name }}</span>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">

                    <div v-if="isMedico">
                        <h1 class="text-3xl font-bold text-blue-600 mb-4">¡Bienvenido, Dr. {{ $page.props.auth.user.name }}!</h1>
                        <p class="text-gray-600">Aquí podrás ver un resumen de tus citas de hoy, ingresos y pacientes críticos.</p>
                        </div>

                    <div v-else>
                        <h1 class="text-3xl font-bold text-cyan-600 mb-4">Hola, {{ $page.props.auth.user.name }}</h1>
                        <p class="text-gray-600">Bienvenido a tu portal de salud. No tienes citas próximas agendadas.</p>
                        </div>

                </div>

            </main>
        </div>
    </div>
</template>
