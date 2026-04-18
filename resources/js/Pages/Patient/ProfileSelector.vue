<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    profiles: Array,
    maxProfiles: {
        type: Number,
        default: 5
    }
});

// 🌗 Estado del Tema (Oscuro por defecto, pero cambiable)
const isDarkMode = ref(true);

// 🪟 Control del Modal
const showAddModal = ref(false);

const form = useForm({
    nombre_completo: '',
    curp: '',
    fecha_nacimiento: '',
    genero: 'masculino',
    parentesco: 'hijo',
});

// 💾 Función para Guardar en la Base de Datos
const submitProfile = () => {
    form.post(route('patient.profiles.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};

// 🖱️ Función para entrar a un perfil
const selectProfile = (profileId) => {
    router.post(route('patient.profiles.select', { profile: profileId }));
};
</script>

<template>
    <Head title="¿Quién consulta hoy? | NexSalud" />

    <div :class="[isDarkMode ? 'bg-[#141414] text-white' : 'bg-gray-100 text-gray-900']"
         class="min-h-screen transition-colors duration-500 flex flex-col items-center justify-center font-sans relative">

        <button @click="isDarkMode = !isDarkMode"
                class="absolute top-8 right-8 p-2 rounded-lg border transition-all flex items-center gap-2 text-xs font-bold uppercase tracking-widest"
                :class="isDarkMode ? 'border-gray-700 bg-gray-800 text-gray-400 hover:text-white' : 'border-gray-300 bg-white text-gray-600 shadow-sm hover:bg-gray-50'">
            <span v-if="isDarkMode">☀️ Modo Claro</span>
            <span v-else>🌙 Modo Oscuro</span>
        </button>

        <div class="w-full max-w-5xl px-6 py-12 flex flex-col items-center">

            <h1 class="text-4xl md:text-5xl font-bold mb-12 text-center tracking-wide">
                ¿Quién está consultando hoy?
            </h1>

            <div class="flex flex-wrap justify-center gap-10">

                <div v-for="profile in profiles" :key="profile.id"
                     class="group flex flex-col items-center cursor-pointer transition-transform duration-300 hover:scale-105"
                     @click="selectProfile(profile.id)">

                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-xl mb-4 overflow-hidden border-4 border-transparent group-hover:border-emerald-500 transition-colors duration-300 relative bg-emerald-700 flex items-center justify-center shadow-lg">
                        <span class="text-6xl font-bold text-white uppercase">{{ profile.nombre_completo.charAt(0) }}</span>
                    </div>

                    <span class="text-lg font-medium transition-colors"
                          :class="isDarkMode ? 'text-gray-400 group-hover:text-white' : 'text-gray-600 group-hover:text-black'">
                        {{ profile.nombre_completo.split(' ')[0] }}
                    </span>
                </div>

                <div v-if="profiles.length < maxProfiles"
                     class="group flex flex-col items-center cursor-pointer transition-transform duration-300 hover:scale-105"
                     @click="showAddModal = true">

                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-xl mb-4 border-4 border-dashed transition-colors duration-300 flex items-center justify-center bg-transparent"
                         :class="isDarkMode ? 'border-gray-600 group-hover:border-white' : 'border-gray-400 group-hover:border-emerald-600'">
                        <span class="text-6xl" :class="isDarkMode ? 'text-gray-600 group-hover:text-white' : 'text-gray-400 group-hover:text-emerald-600'">+</span>
                    </div>

                    <span class="text-lg font-medium transition-colors"
                          :class="isDarkMode ? 'text-gray-400 group-hover:text-white' : 'text-gray-600 group-hover:text-black'">
                        Añadir familiar
                    </span>
                </div>

            </div>
        </div>
    </div>

    <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/80 transition-opacity" @click="showAddModal = false"></div>

        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full z-10 overflow-hidden transform transition-all animate-in fade-in zoom-in duration-300">
            <div class="p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Nuevo Perfil Familiar</h3>

                <form @submit.prevent="submitProfile" class="space-y-4">
                    <div>
                        <InputLabel for="nombre" value="Nombre Completo" />
                        <TextInput id="nombre" v-model="form.nombre_completo" type="text" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.nombre_completo" />
                    </div>

                    <div>
                        <InputLabel for="curp_perfil" value="CURP" />
                        <TextInput id="curp_perfil" v-model="form.curp" type="text" class="mt-1 block w-full uppercase" required maxlength="18" />
                        <InputError :message="form.errors.curp" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Nacimiento" />
                            <TextInput v-model="form.fecha_nacimiento" type="date" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <InputLabel value="Género" />
                            <select v-model="form.genero" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500">
                                <option value="masculino">Masculino</option>
                                <option value="femenino">Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Parentesco" />
                        <select v-model="form.parentesco" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500">
                            <option value="hijo">Hijo / Hija</option>
                            <option value="conyuge">Cónyuge</option>
                            <option value="padre">Padre / Madre</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <div class="pt-6 flex justify-end gap-3">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 text-gray-500 font-bold hover:text-gray-700">
                            Cancelar
                        </button>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="bg-emerald-600 hover:bg-emerald-700">
                            Guardar Perfil
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
