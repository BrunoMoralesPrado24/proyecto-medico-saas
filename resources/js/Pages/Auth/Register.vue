<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
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
    // Si es paciente, limpiamos los datos médicos antes de enviar por si acaso
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
    <Head title="Registro" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <form @submit.prevent="submit">

            <div class="mb-6">
                <InputLabel value="¿Qué tipo de cuenta deseas crear?"
                    class="mb-3 text-base font-semibold text-gray-700 text-center" />
                <div class="flex space-x-4">
                    <label
                        class="flex flex-col items-center justify-center p-4 border-2 rounded-xl cursor-pointer flex-1 transition-all duration-200"
                        :class="form.role_type === 'medico' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-200 hover:bg-gray-50 text-gray-600'">
                        <input type="radio" v-model="form.role_type" value="medico" class="hidden">
                        <span class="text-3xl mb-2">👨‍⚕️</span>
                        <span class="text-sm font-bold">Soy Médico</span>
                    </label>

                    <label
                        class="flex flex-col items-center justify-center p-4 border-2 rounded-xl cursor-pointer flex-1 transition-all duration-200"
                        :class="form.role_type === 'paciente_titular' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 hover:bg-gray-50 text-gray-600'">
                        <input type="radio" v-model="form.role_type" value="paciente_titular" class="hidden">
                        <span class="text-3xl mb-2">👨‍👩‍👧‍👦</span>
                        <span class="text-sm font-bold text-center">Cuenta Familiar</span>
                    </label>
                </div>
                <InputError class="mt-2" :message="form.errors.role_type" />
            </div>

            <div>
                <InputLabel for="name" value="Nombre Completo (Titular de la cuenta)" />
                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required autofocus
                    pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$" title="Solo se permiten letras y espacios" maxlength="100"
                    autocomplete="name" />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Correo Electrónico" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required
                    autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />
                <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required
                    autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                    class="mt-1 block w-full" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div v-if="form.role_type === 'medico'"
                class="mt-6 p-4 border border-blue-200 bg-blue-50 rounded-xl space-y-4">
                <h3 class="font-bold text-blue-800 text-sm uppercase tracking-wider text-center mb-2">Datos Profesionales y de Clínica</h3>

                <div>
                    <InputLabel for="curp" value="CURP" />
                    <TextInput id="curp" v-model="form.curp" type="text" class="mt-1 block w-full border-blue-300 focus:border-blue-500 focus:ring-blue-500 uppercase"
                        :required="form.role_type === 'medico'"
                        pattern="^[a-zA-Z]{4}\d{6}[hHmM][a-zA-Z]{5}[a-zA-Z\d]\d$" title="Debe ser un CURP válido de 18 caracteres"
                        maxlength="18" />
                    <InputError class="mt-2" :message="form.errors.curp" />
                </div>

                <div>
                    <InputLabel for="clues" value="CLUES de la Clínica (11 caracteres)" />
                    <TextInput id="clues" v-model="form.clues" type="text"
                        class="mt-1 block w-full border-blue-300 focus:border-blue-500 focus:ring-blue-500 uppercase"
                        placeholder="Ej. MCSSA012345" pattern="^[a-zA-Z]{5}\d{6}$"
                        title="Formato CLUES: 5 letras seguidas de 6 números (Ej. MCSSA012345)" maxlength="11"
                        :required="form.role_type === 'medico'" />
                    <InputError class="mt-2" :message="form.errors.clues" />
                </div>

                <div>
                    <InputLabel for="clinic_name" value="Nombre de la Clínica" />
                    <TextInput id="clinic_name" v-model="form.clinic_name" type="text"
                        class="mt-1 block w-full border-blue-300 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Solo se usará si la CLUES es nueva"
                        :required="form.role_type === 'medico'" />
                    <InputError class="mt-2" :message="form.errors.clinic_name" />
                </div>

                <div>
                    <InputLabel for="cedula_profesional" value="Cédula Profesional" />
                    <TextInput id="cedula_profesional" v-model="form.cedula_profesional" type="text"
                        class="mt-1 block w-full border-blue-300 focus:border-blue-500 focus:ring-blue-500"
                        :required="form.role_type === 'medico'" />
                    <InputError class="mt-2" :message="form.errors.cedula_profesional" />
                </div>

                <div>
                    <InputLabel for="universidad_egreso" value="Universidad de Egreso" />
                    <TextInput id="universidad_egreso" v-model="form.universidad_egreso" type="text"
                        class="mt-1 block w-full border-blue-300 focus:border-blue-500 focus:ring-blue-500"
                        :required="form.role_type === 'medico'" />
                    <InputError class="mt-2" :message="form.errors.universidad_egreso" />
                </div>
            </div>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

                        <div class="ms-2">
                            Acepto los <a target="_blank" :href="route('terms.show')"
                                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md">Términos
                                y Condiciones</a> y la <a target="_blank" :href="route('policy.show')"
                                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md">Política
                                de Privacidad</a>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="flex items-center justify-end mt-6">
                <Link :href="route('login')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none">
                    ¿Ya tienes cuenta?
                </Link>

                <PrimaryButton class="ms-4 px-6 py-2 text-base" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Crear Cuenta
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
