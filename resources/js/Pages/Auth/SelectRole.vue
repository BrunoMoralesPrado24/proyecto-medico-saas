<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';

const selectRole = (selectedRole) => {
    router.post(route('role.select'), { role: selectedRole });
};
</script>

<template>
    <Head title="Elegir Perfil | NexSalud" />

    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4">

        <div class="mb-8">
            <AuthenticationCardLogo />
        </div>

        <div class="max-w-2xl w-full">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">¡Hola de nuevo!</h1>
                <p class="text-gray-500 mt-2 text-lg">¿Cómo deseas usar la plataforma el día de hoy?</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <button
                    @click="selectRole('medico')"
                    class="group relative bg-white p-8 rounded-2xl shadow-sm border-2 border-transparent hover:border-blue-500 hover:shadow-xl transition-all duration-300 text-left overflow-hidden"
                >
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-in-out -z-10"></div>

                    <span class="text-5xl block mb-4">👨‍⚕️</span>
                    <h2 class="text-2xl font-bold text-gray-900 group-hover:text-blue-700 transition-colors">Portal Médico</h2>
                    <p class="text-gray-500 mt-2 text-sm leading-relaxed">
                        Entrar a mis clínicas, gestionar agenda, ver pacientes y emitir recetas.
                    </p>
                </button>

                <button
                    @click="selectRole('paciente_titular')"
                    class="group relative bg-white p-8 rounded-2xl shadow-sm border-2 border-transparent hover:border-emerald-500 hover:shadow-xl transition-all duration-300 text-left overflow-hidden"
                >
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-in-out -z-10"></div>

                    <span class="text-5xl block mb-4">👤</span>
                    <h2 class="text-2xl font-bold text-gray-900 group-hover:text-emerald-700 transition-colors">Mi Salud</h2>
                    <p class="text-gray-500 mt-2 text-sm leading-relaxed">
                        Entrar como paciente para revisar mis propias recetas, signos vitales y perfil familiar.
                    </p>
                </button>
            </div>
        </div>
    </div>
</template>
