<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Mi Expediente | NexSalud" />

    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-6">
        <div class="bg-white p-10 rounded-2xl shadow-xl text-center max-w-lg w-full">
            <span class="text-5xl mb-4 block">🩺</span>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Portal del Paciente</h1>
            <p class="text-gray-500 mb-8">Aquí construiremos la vista de tus recetas y signos vitales.</p>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="px-6 py-3 bg-red-50 text-red-600 font-bold rounded-xl border border-red-200 hover:bg-red-100 transition-colors w-full"
            >
                Cerrar Sesión por ahora
            </Link>
        </div>
    </div>
</template>
