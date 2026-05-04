<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    email: '',
    curp: '',
    role_type: 'medico', // Valor por defecto
    password: '',
    password_confirmation: '',
    terms: false,
    clinic_name: '',
    clues: '',
    cedula_profesional: '',
    universidad_egreso: '',
});

const submit = () => {
    // 🛡️ LIMPIEZA DE DATOS: Si es paciente, sanitizamos los campos médicos antes del POST
    if (form.role_type === 'paciente_titular') {
        form.curp = '';
        form.clinic_name = '';
        form.clues = '';
        form.cedula_profesional = '';
        form.universidad_egreso = '';
    }

    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Registro | NexSalud" />

    <div class="min-h-screen bg-gray-50 flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">

        <div class="mb-6">
            <AuthenticationCardLogo />
        </div>

        <div class="w-full max-w-2xl px-8 py-10 bg-white shadow-xl overflow-hidden rounded-2xl border border-gray-100">
            <form @submit.prevent="submit">

                <!-- 🎛️ SELECTOR DE ROL DUAL -->
                <div class="mb-8">
                    <InputLabel value="¿Qué tipo de cuenta deseas crear?" class="mb-4 text-base font-bold text-gray-700 text-center uppercase tracking-wide" />
                    <div class="flex space-x-4">
                        <!-- Tarjeta Médico -->
                        <label class="flex flex-col items-center justify-center p-4 border-2 rounded-xl cursor-pointer flex-1 transition-all duration-300 transform hover:-translate-y-1"
                            :class="form.role_type === 'medico' ? 'border-blue-600 bg-blue-50 text-blue-700 shadow-md' : 'border-gray-200 hover:bg-gray-50 text-gray-600'">
                            <input type="radio" v-model="form.role_type" value="medico" class="hidden">
                            <span class="text-4xl mb-3">👨‍⚕️</span>
                            <span class="text-sm font-black uppercase tracking-wider">Soy Médico</span>
                        </label>

                        <!-- Tarjeta Paciente -->
                        <label class="flex flex-col items-center justify-center p-4 border-2 rounded-xl cursor-pointer flex-1 transition-all duration-300 transform hover:-translate-y-1"
                            :class="form.role_type === 'paciente_titular' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 shadow-md' : 'border-gray-200 hover:bg-gray-50 text-gray-600'">
                            <input type="radio" v-model="form.role_type" value="paciente_titular" class="hidden">
                            <span class="text-4xl mb-3">👨‍👩‍👧‍👦</span>
                            <span class="text-sm font-black text-center uppercase tracking-wider">Cuenta Familiar</span>
                        </label>
                    </div>
                    <InputError class="mt-2" :message="form.errors.role_type" />
                </div>

                <!-- 👤 DATOS GENERALES -->
                <div>
                    <InputLabel for="name" value="Nombre Completo (Titular de la cuenta)" />
                    <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required autofocus
                        pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$" title="Solo se permiten letras y espacios" maxlength="100" autocomplete="name" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="mt-4">
                    <InputLabel for="email" value="Correo Electrónico" />
                    <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autocomplete="username" />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <InputLabel for="password" value="Contraseña" />
                        <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                        <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>
                </div>

                <!-- 🏥 ZONA EXCLUSIVA MÉDICO -->
                <div v-if="form.role_type === 'medico'" class="mt-8 p-6 border border-blue-200 bg-blue-50/50 rounded-xl space-y-4">
                    <h3 class="font-black text-blue-800 text-sm uppercase tracking-widest text-center mb-4 flex items-center justify-center">
                        Datos Profesionales
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <InputLabel for="curp" value="CURP" />
                            <TextInput id="curp" v-model="form.curp" type="text" class="mt-1 block w-full border-blue-300 focus:border-blue-500 uppercase font-mono"
                                :required="form.role_type === 'medico'" pattern="^[a-zA-Z]{4}\d{6}[hHmM][a-zA-Z]{5}[a-zA-Z\d]\d$" title="Debe ser un CURP válido" maxlength="18" />
                            <InputError class="mt-2" :message="form.errors.curp" />
                        </div>

                        <div>
                            <InputLabel for="clues" value="CLUES (11 caracteres)" />
                            <TextInput id="clues" v-model="form.clues" type="text" class="mt-1 block w-full border-blue-300 focus:border-blue-500 uppercase font-mono"
                                placeholder="Ej. MCSSA012345" pattern="^[a-zA-Z]{5}\d{6}$" maxlength="11" :required="form.role_type === 'medico'" />
                            <InputError class="mt-2" :message="form.errors.clues" />
                        </div>

                        <div>
                            <InputLabel for="clinic_name" value="Nombre de la Clínica" />
                            <TextInput id="clinic_name" v-model="form.clinic_name" type="text" class="mt-1 block w-full border-blue-300 focus:border-blue-500"
                                placeholder="Si la CLUES es nueva" :required="form.role_type === 'medico'" />
                            <InputError class="mt-2" :message="form.errors.clinic_name" />
                        </div>

                        <div>
                            <InputLabel for="cedula_profesional" value="Cédula Profesional" />
                            <TextInput id="cedula_profesional" v-model="form.cedula_profesional" type="text" class="mt-1 block w-full border-blue-300 focus:border-blue-500"
                                :required="form.role_type === 'medico'" />
                            <InputError class="mt-2" :message="form.errors.cedula_profesional" />
                        </div>

                        <div>
                            <InputLabel for="universidad_egreso" value="Universidad de Egreso" />
                            <TextInput id="universidad_egreso" v-model="form.universidad_egreso" type="text" class="mt-1 block w-full border-blue-300 focus:border-blue-500"
                                :required="form.role_type === 'medico'" />
                            <InputError class="mt-2" :message="form.errors.universidad_egreso" />
                        </div>
                    </div>
                </div>

                <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-6">
                    <InputLabel for="terms">
                        <div class="flex items-center">
                            <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />
                            <div class="ms-2">
                                Acepto los <a target="_blank" :href="route('terms.show')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md">Términos y Condiciones</a> y la <a target="_blank" :href="route('policy.show')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md">Política de Privacidad</a>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.terms" />
                    </InputLabel>
                </div>

                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-100">
                    <Link :href="route('login')" class="font-bold text-sm text-blue-600 hover:text-blue-800 rounded-md focus:outline-none transition-colors">
                        &larr; Ya tengo cuenta
                    </Link>

                    <PrimaryButton class="px-8 py-3 text-base font-bold tracking-widest bg-blue-600 hover:bg-blue-700 shadow-lg transform hover:-translate-y-0.5 transition" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Crear Cuenta
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>
