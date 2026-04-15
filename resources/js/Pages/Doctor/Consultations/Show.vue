<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';

const props = defineProps({
    patient: Object,
    consultation: Object,
    vitalSign: Object,
});

const edad = computed(() => {
    if (!props.patient.fecha_nacimiento) return 'N/A';
    const hoy = new Date();
    const nacimiento = new Date(props.patient.fecha_nacimiento);
    let e = hoy.getFullYear() - nacimiento.getFullYear();
    if (hoy.getMonth() < nacimiento.getMonth() || (hoy.getMonth() === nacimiento.getMonth() && hoy.getDate() < nacimiento.getDate())) e--;
    return e;
});

const fechaFormateada = computed(() => {
    return new Date(props.consultation.created_at).toLocaleDateString('es-MX', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
});
</script>

<template>
    <div class="flex h-screen bg-gray-100 font-sans">

        <Sidebar />

        <main class="flex-1 overflow-y-auto">

            <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

                <div class="flex justify-between items-center mb-6">
                    <Link :href="route('medical-records.show', patient.id)"
                        class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center transition bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver al Expediente
                    </Link>

                    <a :href="route('consultations.prescription.pdf', { patient: patient.id, consultation: consultation.id })"
                        target="_blank"
                        class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-gray-700 transition flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Imprimir Receta (PDF)
                    </a>
                </div>

                <div
                    class="bg-white shadow-2xl rounded-sm border border-gray-200 overflow-hidden print:shadow-none print:border-none">

                    <div
                        class="border-b-4 border-indigo-900 bg-gray-50 px-8 py-6 flex flex-col sm:flex-row justify-between items-start sm:items-center">
                        <div>
                            <h1 class="text-2xl font-black text-gray-900 uppercase tracking-tight">Nota Evolutiva</h1>
                            <p class="text-sm text-gray-500 font-medium mt-1">Expediente: {{
                                patient.clinics?.[0]?.pivot?.expediente_fisico || 'N/A' }}</p>
                        </div>
                        <div class="text-left sm:text-right mt-4 sm:mt-0">
                            <p class="text-sm font-bold text-gray-800">{{ fechaFormateada }}</p>
                            <p class="text-sm text-gray-600 mt-1 flex items-center sm:justify-end">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Dr. {{ consultation.doctor?.name || 'Médico Titular' }}
                            </p>
                        </div>
                    </div>

                    <div class="px-8 py-5 border-b border-gray-100 bg-white grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <span
                                class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Paciente</span>
                            <span class="block text-sm font-bold text-gray-900">{{ patient.nombre }}</span>
                        </div>
                        <div>
                            <span
                                class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Edad</span>
                            <span class="block text-sm font-medium text-gray-800">{{ edad }} años</span>
                        </div>
                        <div>
                            <span
                                class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Sexo</span>
                            <span class="block text-sm font-medium text-gray-800">{{ patient.sexo || 'N/A' }}</span>
                        </div>
                        <div>
                            <span
                                class="block text-xs font-bold text-red-400 uppercase tracking-wider mb-1">Alergias</span>
                            <span class="block text-sm font-bold text-red-600 truncate"
                                :title="patient.medical_history?.alergias || 'Negadas'">
                                {{ patient.medical_history?.alergias || 'Negadas' }}
                            </span>
                        </div>
                    </div>

                    <div v-if="vitalSign"
                        class="px-8 py-5 border-b border-gray-100 bg-gray-50 flex flex-wrap gap-x-8 gap-y-4">
                        <div v-if="vitalSign.peso" class="flex flex-col"><span
                                class="text-xs text-gray-500 uppercase">Peso</span><span
                                class="text-sm font-bold text-gray-800">{{ vitalSign.peso }} kg</span></div>
                        <div v-if="vitalSign.talla" class="flex flex-col"><span
                                class="text-xs text-gray-500 uppercase">Talla</span><span
                                class="text-sm font-bold text-gray-800">{{ vitalSign.talla }} m</span></div>
                        <div v-if="vitalSign.temperatura" class="flex flex-col"><span
                                class="text-xs text-gray-500 uppercase">Temp</span><span
                                class="text-sm font-bold text-gray-800">{{ vitalSign.temperatura }} °C</span></div>
                        <div v-if="vitalSign.presion_sistolica && vitalSign.presion_diastolica" class="flex flex-col">
                            <span class="text-xs text-gray-500 uppercase">TA</span><span
                                class="text-sm font-bold text-gray-800">{{ vitalSign.presion_sistolica }}/{{
                                vitalSign.presion_diastolica }}</span></div>
                        <div v-if="vitalSign.frecuencia_cardiaca" class="flex flex-col"><span
                                class="text-xs text-gray-500 uppercase">FC</span><span
                                class="text-sm font-bold text-gray-800">{{ vitalSign.frecuencia_cardiaca }} lpm</span>
                        </div>
                        <div v-if="vitalSign.frecuencia_respiratoria" class="flex flex-col"><span
                                class="text-xs text-gray-500 uppercase">FR</span><span
                                class="text-sm font-bold text-gray-800">{{ vitalSign.frecuencia_respiratoria }}
                                rpm</span></div>
                        <div v-if="vitalSign.oxigenacion" class="flex flex-col"><span
                                class="text-xs text-gray-500 uppercase">SpO2</span><span
                                class="text-sm font-bold text-gray-800">{{ vitalSign.oxigenacion }} %</span></div>
                    </div>

                    <div class="px-8 py-8 space-y-8 bg-white">

                        <div>
                            <h3
                                class="text-sm font-bold text-indigo-900 border-b border-gray-200 pb-2 mb-3 uppercase tracking-widest">
                                S. Motivo de Consulta (Subjetivo)</h3>
                            <p class="text-gray-800 text-sm leading-relaxed whitespace-pre-line">{{
                                consultation.motivo_consulta }}</p>
                        </div>

                        <div v-if="consultation.exploracion_fisica">
                            <h3
                                class="text-sm font-bold text-indigo-900 border-b border-gray-200 pb-2 mb-3 uppercase tracking-widest">
                                O. Exploración Física (Objetivo)</h3>
                            <p class="text-gray-800 text-sm leading-relaxed whitespace-pre-line">{{
                                consultation.exploracion_fisica }}</p>
                        </div>

                        <div v-if="consultation.diagnostico">
                            <h3
                                class="text-sm font-bold text-indigo-900 border-b border-gray-200 pb-2 mb-3 uppercase tracking-widest">
                                A. Análisis / Diagnóstico</h3>
                            <p class="text-gray-800 text-sm leading-relaxed whitespace-pre-line">{{
                                consultation.diagnostico }}</p>
                        </div>

                        <div v-if="consultation.tratamiento"
                            class="bg-indigo-50 p-6 rounded-xl border border-indigo-100">
                            <h3
                                class="text-sm font-bold text-indigo-900 mb-3 uppercase tracking-widest flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                                    </path>
                                </svg>
                                P. Plan y Tratamiento Médico
                            </h3>
                            <p class="text-indigo-900 text-sm leading-relaxed whitespace-pre-line font-medium">{{
                                consultation.tratamiento }}</p>
                        </div>

                    </div>

                    <div class="px-8 py-8 bg-gray-50 border-t border-gray-200 text-center mt-12">
                        <div class="w-48 mx-auto border-b-2 border-gray-400 mb-2 mt-8"></div>
                        <p class="text-sm font-bold text-gray-800">Firma Electrónica / Sello Digital</p>
                        <p class="text-xs text-gray-500 mt-1">Documento inmutable generado bajo la NOM-024 del
                            Expediente Clínico Electrónico.</p>
                        <p class="text-xs text-gray-400 mt-1 font-mono">ID: {{ consultation.id }}-{{ new
                            Date(consultation.created_at).getTime() }}</p>
                    </div>

                </div>
            </div>
        </main>
    </div>
</template>
