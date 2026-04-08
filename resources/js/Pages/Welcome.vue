<script setup>
import { Head, Link } from '@inertiajs/vue3';

// Recibimos props básicas que Laravel suele mandar a la vista Welcome
defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Bienvenido a NexSalud" />

    <div class="min-h-screen bg-gray-50 text-gray-900">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center">
            <div class="flex items-center">
                <div class="text-3xl font-extrabold text-blue-600 tracking-tighter">
                    NexSalud<span class="text-blue-400">.</span>
                </div>
            </div>
            
            <div v-if="canLogin" class="flex space-x-4">
                <Link 
                    v-if="$page.props.auth.user" 
                    :href="route('dashboard')" 
                    class="font-semibold text-gray-600 hover:text-blue-600 transition"
                >
                    Ir al Dashboard
                </Link>

                <template v-else>
                    <Link 
                        :href="route('login')" 
                        class="font-semibold text-gray-600 hover:text-blue-600 px-4 py-2 transition"
                    >
                        Iniciar Sesión
                    </Link>

                    <Link 
                        v-if="canRegister" 
                        :href="route('register')" 
                        class="font-semibold bg-blue-600 text-white px-5 py-2 rounded-lg shadow-md hover:bg-blue-700 transition"
                    >
                        Registrar Clínica
                    </Link>
                </template>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 md:mt-24 text-center">
            <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 mb-6 tracking-tight">
                El control total de tu clínica, <br class="hidden md:block">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">
                    en la palma de tu mano.
                </span>
            </h1>
            <p class="mt-4 text-xl text-gray-600 max-w-3xl mx-auto">
                Expedientes clínicos inteligentes, gestión multi-sucursal y control de roles diseñado específicamente para cumplir con la normativa médica en México (NOM-004 y NOM-024).
            </p>
            
            <div class="mt-10 flex justify-center gap-4">
                <Link 
                    v-if="canRegister && !$page.props.auth.user" 
                    :href="route('register')" 
                    class="bg-blue-600 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg hover:bg-blue-700 hover:shadow-xl transition transform hover:-translate-y-1"
                >
                    Comienza tu prueba gratuita
                </Link>
            </div>
        </main>

        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-32 pb-20 grid grid-cols-1 md:grid-cols-3 gap-12">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center mb-6 text-2xl">
                    🏥
                </div>
                <h3 class="text-xl font-bold mb-3">Gestión Multi-Sucursal</h3>
                <p class="text-gray-600">
                    ¿Trabajas en diferentes clínicas? Mantén tus precios, agendas y pacientes perfectamente aislados y organizados según la sucursal activa.
                </p>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="w-14 h-14 bg-cyan-100 text-cyan-600 rounded-xl flex items-center justify-center mb-6 text-2xl">
                    📁
                </div>
                <h3 class="text-xl font-bold mb-3">Expedientes Blindados</h3>
                <p class="text-gray-600">
                    Seguridad total. Soporte para pacientes titulares, dependientes y huérfanos de cuenta, manteniendo la trazabilidad legal en todo momento.
                </p>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center mb-6 text-2xl">
                    🔐
                </div>
                <h3 class="text-xl font-bold mb-3">Control de Accesos</h3>
                <p class="text-gray-600">
                    Delega sin perder el control. Permisos avanzados para médicos, administradores y secretariado, protegiendo la información sensible.
                </p>
            </div>
        </section>
    </div>
</template>