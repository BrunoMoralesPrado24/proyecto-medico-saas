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

const showAddModal = ref(false);

const form = useForm({
    nombre_completo: '',
    curp: '',
    fecha_nacimiento: '',
    genero: 'masculino',
    parentesco: 'hijo',
});

const submitProfile = () => {
    form.post(route('patient.profiles.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};

const selectProfile = (profileId) => {
    router.post(route('patient.profiles.select', { profile: profileId }));
};

// 🗑️ FUNCIÓN DE ELIMINACIÓN SEGURA
const deleteProfile = (profileId, nombre) => {
    if (confirm(`¿Estás seguro de que deseas eliminar a ${nombre} de tu cuenta familiar? Su historial clínico se archivará.`)) {
        router.delete(route('patient.profiles.destroy', { profile: profileId }), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="¿Quién consulta hoy? | NexSalud" />

    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center font-sans">
        <div class="w-full max-w-5xl px-6 py-12 flex flex-col items-center">

            <h1 class="text-4xl md:text-5xl font-bold mb-12 text-center text-gray-900 tracking-wide">
                ¿Quién está consultando hoy?
            </h1>

            <div class="flex flex-wrap justify-center gap-10">

                <div v-for="profile in profiles" :key="profile.id"
                     class="group relative flex flex-col items-center cursor-pointer transition-transform duration-300 hover:scale-105">

                    <!-- 🛡️ BOTÓN DE BORRAR (Oculto para el titular) -->
                    <button v-if="profile.parentesco !== 'titular'"
                            @click.stop="deleteProfile(profile.id, profile.nombre_completo)"
                            class="absolute -top-2 -right-2 z-10 bg-red-500 hover:bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                            title="Eliminar perfil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <div class="flex flex-col items-center" @click="selectProfile(profile.id)">
                        <div class="w-32 h-32 md:w-40 md:h-40 rounded-xl mb-4 overflow-hidden border-4 border-transparent group-hover:border-emerald-500 transition-colors duration-300 relative bg-emerald-700 flex items-center justify-center shadow-lg">
                            <span class="text-6xl font-bold text-white uppercase">{{ profile.nombre_completo.charAt(0) }}</span>
                        </div>

                        <span class="text-lg font-medium text-gray-600 group-hover:text-black">
                            {{ profile.nombre_completo.split(' ')[0] }}
                        </span>
                        <span v-if="profile.parentesco === 'titular'" class="text-xs font-bold text-emerald-500 mt-1 uppercase">Titular</span>
                    </div>
                </div>

                <div v-if="profiles.length < maxProfiles"
                     class="group flex flex-col items-center cursor-pointer transition-transform duration-300 hover:scale-105"
                     @click="showAddModal = true">

                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-xl mb-4 border-4 border-dashed border-gray-300 group-hover:border-emerald-500 transition-colors duration-300 flex items-center justify-center bg-transparent">
                        <span class="text-6xl text-gray-300 group-hover:text-emerald-500">+</span>
                    </div>

                    <span class="text-lg font-medium text-gray-600 group-hover:text-black">
                        Añadir familiar
                    </span>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL DE AÑADIR -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 transition-opacity" @click="showAddModal = false"></div>

        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full z-10 overflow-hidden transform transition-all animate-in fade-in zoom-in duration-300">
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
                        <!-- 🛡️ MUTACIÓN UX: Forzamos mayúsculas visualmente -->
                        <TextInput id="curp_perfil" v-model="form.curp" @input="form.curp = form.curp.toUpperCase()" type="text" class="mt-1 block w-full uppercase" required maxlength="18" />
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
