<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Iniciar Sesión | NexSalud" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-8 text-center">
            <h2 class="text-2xl font-extrabold text-gray-900">Bienvenido de vuelta</h2>
            <p class="text-sm text-gray-500 mt-1">Ingresa a tu portal médico o cuenta familiar</p>
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-emerald-600 bg-emerald-50 p-3 rounded-lg text-center">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="email" value="Correo Electrónico" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full focus:ring-blue-500 focus:border-blue-500"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="tu@correo.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex justify-between items-center mb-1">
                    <InputLabel for="password" value="Contraseña" />
                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                        ¿Olvidaste tu contraseña?
                    </Link>
                </div>
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full focus:ring-blue-500 focus:border-blue-500"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" class="text-blue-600 focus:ring-blue-500" />
                    <span class="ms-2 text-sm text-gray-600 font-medium">Mantener sesión iniciada</span>
                </label>
            </div>

            <div class="pt-2">
                <PrimaryButton class="w-full justify-center py-3 text-base bg-slate-800 hover:bg-slate-900" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Iniciar Sesión
                </PrimaryButton>
            </div>

            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    ¿Aún no tienes cuenta?
                    <Link :href="route('register')" class="font-bold text-blue-600 hover:text-blue-800 transition">
                        Regístrate aquí
                    </Link>
                </p>
            </div>
        </form>
    </AuthenticationCard>
</template>
