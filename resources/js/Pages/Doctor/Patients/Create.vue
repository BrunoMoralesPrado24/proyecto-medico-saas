<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';

// Estado Reactivo del Formulario
const form = useForm({
    nombre: '',
    curp: '',
    fecha_nacimiento: '',
    telefono: '',
    mode: 'huerfano', // huerfano, vincular, crear
    email: '',
    nombre_titular: '',
    privacy_notice: false,
    // Nuevos campos NOM-004
    sexo: '',
    estado_civil: '',
    ocupacion: '',
    religion: '',
});

// Variables para la verificación en vivo
const emailStatus = ref('idle'); // idle, checking, exists, not_found, error
let typingTimer;

// Observamos si cambia el nombre del paciente para autocopiarlo al titular (por comodidad)
const isTitularModified = ref(false);
watch(() => form.nombre, (newVal) => {
    if (form.mode === 'crear' && !isTitularModified.value) {
        form.nombre_titular = newVal;
    }
});

// Observador para verificar el correo en vivo cuando está en modo "Vincular"
watch(() => form.email, (newEmail) => {
    if (form.mode !== 'vincular') return;
    
    clearTimeout(typingTimer);
    emailStatus.value = 'idle';

    if (newEmail.length > 5 && newEmail.includes('@')) {
        emailStatus.value = 'checking';
        
        // Debouncer: Esperamos 500ms después de que deje de escribir para no saturar el servidor
        typingTimer = setTimeout(async () => {
            try {
                // Hacemos la consulta al API de Laravel
                const response = await axios.post('/check-email', { email: newEmail });
                emailStatus.value = response.data.exists ? 'exists' : 'not_found';
            } catch (error) {
                emailStatus.value = 'error';
            }
        }, 500);
    }
});

// Función de guardado con bloqueo de seguridad
const submit = () => {
    if (form.mode === 'vincular' && emailStatus.value !== 'exists') {
        alert("Debes ingresar un correo vinculado a una cuenta existente.");
        return;
    }
    form.post(route('patients.store'));
};
</script>

<template>
    <div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">
        
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Registrar Nuevo Paciente</h2>
            <Link :href="route('patients.index')" class="text-gray-500 hover:text-gray-700 font-medium px-4 py-2 border border-gray-300 rounded-lg bg-white shadow-sm transition">
                Cancelar
            </Link>
        </div>

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8">
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

                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">2. Acceso Digital</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
                    
                    <label class="relative flex flex-col p-4 cursor-pointer border-2 rounded-xl transition-all"
                           :class="form.mode === 'huerfano' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                        <input type="radio" v-model="form.mode" value="huerfano" class="sr-only">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400 mb-2" :class="{'text-blue-600': form.mode === 'huerfano'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        <span class="font-bold text-gray-900">Sin Acceso a App</span>
                        <span class="text-xs text-gray-500 mt-1">Paciente tradicional. Expediente solo local.</span>
                    </label>

                    <label class="relative flex flex-col p-4 cursor-pointer border-2 rounded-xl transition-all"
                           :class="form.mode === 'vincular' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                        <input type="radio" v-model="form.mode" value="vincular" class="sr-only">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400 mb-2" :class="{'text-blue-600': form.mode === 'vincular'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        <span class="font-bold text-gray-900">Vincular a Cuenta</span>
                        <span class="text-xs text-gray-500 mt-1">Ya tiene cuenta de NexSalud.</span>
                    </label>

                    <label class="relative flex flex-col p-4 cursor-pointer border-2 rounded-xl transition-all"
                           :class="form.mode === 'crear' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                        <input type="radio" v-model="form.mode" value="crear" class="sr-only">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400 mb-2" :class="{'text-blue-600': form.mode === 'crear'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                        </svg>
                        <span class="font-bold text-gray-900">Crear Cuenta</span>
                        <span class="text-xs text-gray-500 mt-1">Generar nuevo acceso para paciente o familiar.</span>
                    </label>
                </div>

                <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 mb-8 min-h-[120px]">
                    
                    <div v-if="form.mode === 'huerfano'">
                        <label class="block text-sm font-medium text-gray-700">Email de Contacto (Opcional)</label>
                        <input v-model="form.email" type="email" placeholder="ejemplo@correo.com" class="mt-1 block w-full sm:w-1/2 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                        <p class="text-xs text-yellow-600 mt-2 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Este correo no se creará como usuario, solo se usará para enviar recetas o avisos.
                        </p>
                    </div>

                    <div v-if="form.mode === 'vincular'">
                        <label class="block text-sm font-medium text-gray-700">Buscar por Correo Electrónico del Titular</label>
                        <div class="relative mt-1 sm:w-1/2">
                            <input v-model="form.email" type="email" placeholder="ejemplo@correo.com" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm pr-10">
                            
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg v-if="emailStatus === 'checking'" class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-if="emailStatus === 'exists'" class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <svg v-if="emailStatus === 'not_found'" class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <p v-if="emailStatus === 'not_found'" class="text-sm text-red-500 mt-2 font-medium">❌ El correo no existe. Verifícalo o crea una cuenta nueva.</p>
                        <p v-if="emailStatus === 'exists'" class="text-sm text-green-600 mt-2 font-medium">✅ Cuenta encontrada. Listo para vincular.</p>
                    </div>

                    <div v-if="form.mode === 'crear'" class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre del Titular de la Cuenta</label>
                            <input v-model="form.nombre_titular" @input="isTitularModified = true" type="text" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm" required>
                            <p class="text-xs text-gray-500 mt-1">Quien tendrá acceso a la aplicación (ej. Madre/Padre).</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Correo Electrónico (Para Iniciar Sesión)</label>
                            <input v-model="form.email" type="email" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm" required>
                            <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-blue-700 bg-blue-100 p-3 rounded-lg border border-blue-200">
                                🔐 Se enviará un correo a esta dirección con una contraseña temporal. El titular deberá cambiarla en su primer inicio de sesión.
                            </p>
                        </div>
                    </div>

                </div>

                <div class="mt-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <label class="flex items-start cursor-pointer">
                        <div class="flex items-center h-5">
                            <input v-model="form.privacy_notice" type="checkbox" class="w-5 h-5 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" required>
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="font-medium text-gray-900">Aceptación de Aviso de Privacidad</span>
                            <p class="text-gray-500 mt-1">Declaro que el paciente (o su tutor legal) ha leído y aceptado el Aviso de Privacidad de la clínica según la normativa LFPDPPP.</p>
                        </div>
                    </label>
                    <div v-if="form.errors.privacy_notice" class="text-red-500 text-sm mt-1 ml-8">{{ form.errors.privacy_notice }}</div>
                </div>

                <div v-if="Object.keys(form.errors).length > 0" class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                    <p class="text-sm text-red-700 font-bold">Por favor corrige los siguientes errores:</p>
                    <ul class="mt-1 text-sm text-red-600 list-disc list-inside">
                        <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                    </ul>
                </div>

                <div class="flex justify-end mt-8 pt-6 border-t border-gray-200">
                    <button type="submit" 
                            class="px-8 py-3 text-white transition-all transform rounded-xl font-bold text-lg shadow-lg"
                            :class="{'bg-gray-400 cursor-not-allowed': form.processing || (form.mode === 'vincular' && emailStatus !== 'exists'), 'bg-blue-600 hover:bg-blue-700 hover:-translate-y-1': !(form.processing || (form.mode === 'vincular' && emailStatus !== 'exists'))}"
                            :disabled="form.processing || (form.mode === 'vincular' && emailStatus !== 'exists')">
                        
                        <span v-if="form.processing" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Guardando Expediente...
                        </span>
                        <span v-else>Guardar Expediente</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>