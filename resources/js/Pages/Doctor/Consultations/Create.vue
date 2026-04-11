<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import VitalSignsForm from '@/Components/VitalSignsForm.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    patient: Object,
});

const form = useForm({
    // Signos Vitales y Somatometría (Alineado con tu T9 y la NOM)
    peso: '',
    talla: '',
    presion_sistolica: '',
    presion_diastolica: '',
    frecuencia_cardiaca: '',
    frecuencia_respiratoria: '',
    temperatura: '',
    oxigenacion: '',

    // Formato SOAP (La parte de Paco - T8)
    motivo_consulta: '',
    exploracion_fisica: '',
    diagnostico: '',
    tratamiento: '',
});

// Control del Panel Lateral (Quick View)
const showHistoryPanel = ref(false);

const edad = computed(() => {
    if (!props.patient.fecha_nacimiento) return 'N/A';
    const hoy = new Date();
    const nacimiento = new Date(props.patient.fecha_nacimiento);
    let e = hoy.getFullYear() - nacimiento.getFullYear();
    if (hoy.getMonth() < nacimiento.getMonth() || (hoy.getMonth() === nacimiento.getMonth() && hoy.getDate() < nacimiento.getDate())) e--;
    return e;
});

const submit = () => {
    // 🛑 INTERCEPTOR: Normalización de Talla
    // Si la talla es mayor a 3, es lógicamente imposible que sean metros.
    // Significa que escribieron centímetros (ej. 170). Lo dividimos entre 100.
    if (form.talla && parseFloat(form.talla) > 3) {
        form.talla = (parseFloat(form.talla) / 100).toFixed(2);
    }

    // Ahora sí, mandamos la información limpia al servidor
    form.post(route('consultations.store', props.patient.id));
};
</script>

<template>
    <div class="flex h-screen bg-gray-50 font-sans overflow-hidden">

        <Sidebar />

        <main class="flex-1 overflow-y-auto relative">

            <div class="sticky top-0 z-10 bg-white border-b border-gray-200 shadow-sm px-8 py-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 flex items-center">
                        <span class="bg-blue-100 text-blue-700 p-1.5 rounded-lg mr-3">🩺</span>
                        Consulta: {{ patient.nombre }}
                    </h1>
                    <p class="text-sm text-gray-500 font-medium mt-1 flex items-center flex-wrap gap-2">
                        <span>Edad: {{ edad }} años</span> |
                        <span>Sexo: {{ patient.sexo || 'N/A' }}</span>
                        <span v-if="patient.medical_history?.alergias && patient.medical_history.alergias !== 'Negadas'" class="ml-2 text-red-700 font-bold bg-red-100 px-2.5 py-0.5 rounded-md border border-red-200 shadow-sm">
                            ⚠️ Alergias: {{ patient.medical_history.alergias }}
                        </span>
                    </p>
                </div>

                <div class="flex gap-3">
                    <button @click="showHistoryPanel = !showHistoryPanel" type="button" class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg font-bold hover:bg-indigo-100 transition shadow-sm border border-indigo-200 flex items-center text-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        {{ showHistoryPanel ? 'Cerrar Historial' : 'Ver Historial Completo' }}
                    </button>
                    <Link :href="route('medical-records.show', patient.id)" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition text-sm flex items-center">
                        Cancelar
                    </Link>
                </div>
            </div>

            <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit">

                    <VitalSignsForm v-model="form" class="mb-8" />

                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden mb-8">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white">
                            <h2 class="text-lg font-bold text-blue-900 flex items-center">
                                Nota Evolutiva (SOAP)
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">

                            <div>
                                <label class="block text-sm font-bold text-gray-800">Subjetivo (S) <span class="text-red-500">*</span></label>
                                <p class="text-xs text-gray-500 mb-2 font-medium">Motivo de consulta y síntomas relatados por el paciente.</p>
                                <textarea v-model="form.motivo_consulta" rows="3" placeholder="El paciente refiere dolor en..." class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition" required></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-800">Objetivo (O)</label>
                                <p class="text-xs text-gray-500 mb-2 font-medium">Hallazgos de la exploración física o resultados de estudios.</p>
                                <textarea v-model="form.exploracion_fisica" rows="4" placeholder="A la exploración física se encuentra..." class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-800">Análisis / Diagnóstico (A)</label>
                                <p class="text-xs text-gray-500 mb-2 font-medium">Impresión diagnóstica y razonamiento médico.</p>
                                <textarea v-model="form.diagnostico" rows="3" placeholder="Probable cuadro de..." class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition"></textarea>
                            </div>

                            <div class="bg-indigo-50 -mx-6 px-6 py-6 border-t border-indigo-100 rounded-b-xl">
                                <label class="block text-sm font-bold text-indigo-900">Plan / Tratamiento (P)</label>
                                <p class="text-xs text-indigo-700 mb-2 font-medium">Medicamentos, indicaciones, estudios solicitados y próxima cita.</p>
                                <textarea v-model="form.tratamiento" rows="6" placeholder="1. Paracetamol 500mg..." class="w-full rounded-xl border-indigo-300 bg-white focus:border-indigo-500 focus:ring-indigo-500 shadow-md transition"></textarea>
                            </div>

                        </div>
                    </div>

                    <div class="flex justify-end pb-12">
                        <button type="submit" :disabled="form.processing" class="px-8 py-4 bg-gray-900 text-white rounded-xl font-bold text-lg hover:bg-gray-800 transition transform hover:-translate-y-1 shadow-lg flex items-center disabled:opacity-50 disabled:transform-none">
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            🔒 Firmar y Guardar Nota
                        </button>
                    </div>

                </form>
            </div>
        </main>

        <transition
            enter-active-class="transform transition ease-in-out duration-300"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transform transition ease-in-out duration-300"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <div v-if="showHistoryPanel" class="fixed inset-y-0 right-0 z-40 w-full max-w-sm bg-white shadow-[-10px_0_30px_rgba(0,0,0,0.1)] flex flex-col border-l border-gray-200">

                <div class="flex items-center justify-between px-4 py-6 bg-indigo-700 sm:px-6 shadow-md z-10">
                    <h2 class="text-lg font-bold text-white flex items-center" id="slide-over-title">
                        <svg class="w-5 h-5 mr-2 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Expediente
                    </h2>
                    <button @click="showHistoryPanel = false" type="button" class="rounded-md text-indigo-200 hover:text-white focus:outline-none transition">
                        <span class="sr-only">Cerrar panel</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="relative flex-1 px-4 py-6 sm:px-6 overflow-y-auto bg-gray-50">

                    <div v-if="patient.medical_history">
                        <div class="mb-6 bg-red-50 p-5 rounded-xl border-l-4 border-red-500 shadow-sm">
                            <h3 class="text-xs font-extrabold text-red-800 uppercase tracking-wider mb-2">Alergias Conocidas</h3>
                            <p class="text-sm text-red-700 whitespace-pre-line font-medium">{{ patient.medical_history.alergias || 'Negadas' }}</p>
                        </div>

                        <div class="space-y-6 bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                            <div>
                                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Patológicos (Enfermedades/Cirugías)</h3>
                                <p class="mt-2 text-sm text-gray-900 whitespace-pre-line leading-relaxed">{{ patient.medical_history.personales_patologicos || 'No registrados' }}</p>
                            </div>
                            <hr class="border-gray-100">

                            <div>
                                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Heredofamiliares</h3>
                                <p class="mt-2 text-sm text-gray-900 whitespace-pre-line leading-relaxed">{{ patient.medical_history.antecedentes_heredofamiliares || 'No registrados' }}</p>
                            </div>
                            <hr class="border-gray-100">

                            <div v-if="patient.medical_history.antecedentes_gineco_obstetricos">
                                <h3 class="text-xs font-bold text-pink-600 uppercase tracking-wider">Gineco-Obstétricos (AGO)</h3>
                                <p class="mt-2 text-sm text-gray-900 whitespace-pre-line leading-relaxed">{{ patient.medical_history.antecedentes_gineco_obstetricos }}</p>
                            </div>
                            <hr class="border-gray-100">

                            <div>
                                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">No Patológicos (Hábitos)</h3>
                                <p class="mt-2 text-sm text-gray-900 whitespace-pre-line leading-relaxed">{{ patient.medical_history.personales_no_patologicos || 'No registrados' }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-12 px-4 bg-white rounded-xl shadow-sm border border-gray-200">
                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <h3 class="text-sm font-medium text-gray-900">Historial Vacío</h3>
                        <p class="text-sm text-gray-500 mt-1">Este paciente no tiene antecedentes permanentes registrados en la NOM-004.</p>
                    </div>

                </div>
            </div>
        </transition>

    </div>
</template>
