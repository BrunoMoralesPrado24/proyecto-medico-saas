<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    patient: Object,
});

// 1. Detectamos si el paciente YA tiene cuenta (Es decir, ya no es huérfano)
const isLinked = computed(() => props.patient.user_id !== null);

// 2. Estado del Formulario
const form = useForm({
    nombre: props.patient.nombre,
    curp: props.patient.curp || '',
    fecha_nacimiento: props.patient.fecha_nacimiento ? props.patient.fecha_nacimiento.split('T')[0] : '',
    telefono: props.patient.telefono,
    email: props.patient.email || '',
    mode: 'editar', // Por defecto, solo editamos datos base (huerfano)
    nombre_titular: '', 
    // Nuevos campos pre-llenados (si existen en BD)
    sexo: props.patient.sexo || '',
    estado_civil: props.patient.estado_civil || '',
    ocupacion: props.patient.ocupacion || '',
    religion: props.patient.religion || '',
});

// 3. Variables para la validación en vivo (Solo si deciden vincular)
const emailStatus = ref('idle'); 
let typingTimer;

// Autocompletado del nombre del titular por UX
const isTitularModified = ref(false);
watch(() => form.nombre, (newVal) => {
    if (form.mode === 'crear' && !isTitularModified.value) {
        form.nombre_titular = newVal;
    }
});

// Buscador de correos en vivo (Solo se activa si el modo es 'vincular')
watch(() => form.email, (newEmail) => {
    if (form.mode !== 'vincular') return;
    
    clearTimeout(typingTimer);
    emailStatus.value = 'idle';

    if (newEmail && newEmail.length > 5 && newEmail.includes('@')) {
        emailStatus.value = 'checking';
        
        typingTimer = setTimeout(async () => {
            try {
                const response = await axios.post('/check-email', { email: newEmail });
                emailStatus.value = response.data.exists ? 'exists' : 'not_found';
            } catch (error) {
                emailStatus.value = 'error';
            }
        }, 500);
    }
});

// Resetear estados al cambiar de modo
watch(() => form.mode, () => {
    emailStatus.value = 'idle';
    form.clearErrors();
});

const submit = () => {
    // Seguro anti-errores antes de mandar al backend
    if (form.mode === 'vincular' && emailStatus.value !== 'exists') {
        alert("Para vincular, debes ingresar un correo de una cuenta existente.");
        return;
    }
    form.put(route('patients.update', props.patient.id));
};
</script>

<template>
    <div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">
        
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800 flex items-center">
                Editar Expediente
                <span v-if="isLinked" class="ml-4 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-200 shadow-sm">
                    <svg class="mr-1.5 h-4 w-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Cuenta Digital Vinculada
                </span>
            </h2>
            <Link :href="route('patients.index')" class="text-gray-500 hover:text-gray-700 font-medium px-4 py-2 border border-gray-300 rounded-lg bg-white shadow-sm transition">
                Regresar
            </Link>
        </div>

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8">
            
            <div v-if="Object.keys(form.errors).length > 0" class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                <p class="text-sm text-red-700 font-bold">Por favor corrige los siguientes errores:</p>
                <ul class="mt-1 text-sm text-red-600 list-disc list-inside">
                    <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                </ul>
            </div>

            <form @submit.prevent="submit">
                
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">1. Datos Personales</h3>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-4 mb-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">CURP *</label>
                        <input v-model="form.curp" type="text" maxlength="18" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm uppercase" required>
                        <div v-if="form.errors.curp" class="text-red-500 text-xs mt-1">{{ form.errors.curp }}</div>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">Nombre Completo *</label>
                        <input v-model="form.nombre" type="text" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm" required autofocus>
                        <div v-if="form.errors.nombre" class="text-red-500 text-xs mt-1">{{ form.errors.nombre }}</div>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">Nacimiento *</label>
                        <input v-model="form.fecha_nacimiento" type="date" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm" required>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">Teléfono (Opcional)</label>
                        <input v-model="form.telefono" type="text" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-4 mb-8 bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sexo (Biológico)</label>
                        <select v-model="form.sexo" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm bg-white">
                            <option value="">Seleccionar...</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Otro">Otro / Intersex</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado Civil</label>
                        <input v-model="form.estado_civil" type="text" placeholder="Ej. Soltero(a)" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ocupación</label>
                        <input v-model="form.ocupacion" type="text" placeholder="Ej. Estudiante" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Religión</label>
                        <input v-model="form.religion" type="text" placeholder="Opcional" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                    </div>
                </div>

                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">2. Configuración de Cuenta</h3>
                
                <div v-if="isLinked" class="bg-gray-50 p-6 rounded-xl border border-gray-200 mb-8">
                    <label class="block text-sm font-medium text-gray-700">Correo de la Cuenta Digital</label>
                    <div class="mt-1 relative rounded-md shadow-sm sm:w-1/2">
                        <input :value="form.email" type="email" disabled class="block w-full bg-gray-200 text-gray-500 border-gray-300 cursor-not-allowed rounded-lg shadow-sm">
                    </div>
                    <p class="mt-3 text-sm text-blue-700 flex items-start bg-blue-50 p-3 rounded-lg border border-blue-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Por seguridad, el correo electrónico está bloqueado porque pertenece a una cuenta activa de NexSalud. El paciente puede cambiarlo desde su propio portal.
                    </p>
                </div>

                <div v-else>
                    
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
                        <label class="relative flex flex-col p-4 cursor-pointer border-2 rounded-xl transition-all"
                               :class="form.mode === 'editar' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                            <input type="radio" v-model="form.mode" value="editar" class="sr-only">
                            <span class="font-bold text-gray-900">Mantener Huérfano</span>
                            <span class="text-xs text-gray-500 mt-1">Solo actualizar correo de contacto.</span>
                        </label>

                        <label class="relative flex flex-col p-4 cursor-pointer border-2 rounded-xl transition-all"
                               :class="form.mode === 'vincular' ? 'border-green-600 bg-green-50' : 'border-gray-200 hover:bg-gray-50'">
                            <input type="radio" v-model="form.mode" value="vincular" class="sr-only">
                            <span class="font-bold text-gray-900">Vincular a Cuenta</span>
                            <span class="text-xs text-gray-500 mt-1">Conectar con usuario existente.</span>
                        </label>

                        <label class="relative flex flex-col p-4 cursor-pointer border-2 rounded-xl transition-all"
                               :class="form.mode === 'crear' ? 'border-purple-600 bg-purple-50' : 'border-gray-200 hover:bg-gray-50'">
                            <input type="radio" v-model="form.mode" value="crear" class="sr-only">
                            <span class="font-bold text-gray-900">Crear Cuenta</span>
                            <span class="text-xs text-gray-500 mt-1">Generar nuevo usuario titular.</span>
                        </label>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 mb-8 min-h-[120px]">
                        
                        <div v-if="form.mode === 'editar'">
                            <label class="block text-sm font-medium text-gray-700">Email de Contacto</label>
                            <input v-model="form.email" type="email" class="mt-1 block w-full sm:w-1/2 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                            <p class="text-xs text-gray-500 mt-2">Puedes cambiar este correo, solo se usará para envíos locales.</p>
                        </div>

                        <div v-if="form.mode === 'vincular'">
                            <label class="block text-sm font-medium text-gray-700">Buscar Cuenta por Correo</label>
                            <div class="relative mt-1 sm:w-1/2">
                                <input v-model="form.email" type="email" class="block w-full border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-lg shadow-sm pr-10">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg v-if="emailStatus === 'checking'" class="animate-spin h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <svg v-if="emailStatus === 'exists'" class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                    <svg v-if="emailStatus === 'not_found'" class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                </div>
                            </div>
                            <p v-if="emailStatus === 'not_found'" class="text-sm text-red-500 mt-2 font-medium">❌ Cuenta no encontrada.</p>
                            <p v-if="emailStatus === 'exists'" class="text-sm text-green-600 mt-2 font-medium">✅ Cuenta lista para vincular.</p>
                        </div>

                        <div v-if="form.mode === 'crear'" class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre del Titular</label>
                                <input v-model="form.nombre_titular" @input="isTitularModified = true" type="text" class="mt-1 block w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nuevo Correo Electrónico</label>
                                <input v-model="form.email" type="email" class="mt-1 block w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm" required>
                            </div>
                            <div class="col-span-2">
                                <p class="text-sm text-purple-700 bg-purple-100 p-3 rounded-lg border border-purple-200">
                                    🔐 Al guardar, la cuenta se creará y se vinculará a este expediente automáticamente.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-gray-200">
                    <button type="submit" 
                            class="px-8 py-3 text-white transition-all transform rounded-xl font-bold text-lg shadow-md flex items-center"
                            :class="{'bg-gray-400 cursor-not-allowed': form.processing || (form.mode === 'vincular' && emailStatus !== 'exists'), 'bg-blue-600 hover:bg-blue-700 hover:-translate-y-1': !(form.processing || (form.mode === 'vincular' && emailStatus !== 'exists'))}"
                            :disabled="form.processing || (form.mode === 'vincular' && emailStatus !== 'exists')">
                        
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.mode === 'editar' ? 'Actualizar Datos' : form.mode === 'vincular' ? 'Vincular y Actualizar' : 'Crear Cuenta y Actualizar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>