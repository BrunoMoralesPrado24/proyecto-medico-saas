<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import VitalSignsForm from '@/Components/VitalSignsForm.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    patient: Object,
});

const form = useForm({
    // Signos Vitales y Somatometría
    peso: '',
    talla: '',
    presion_sistolica: '',
    presion_diastolica: '',
    frecuencia_cardiaca: '',
    frecuencia_respiratoria: '',
    temperatura: '',
    oxigenacion: '',

    // Formato SOAP
    motivo_consulta: '',
    exploracion_fisica: '',
    diagnostico: '',
    tratamiento: '', // Lo usaremos para indicaciones generales no farmacológicas
    
    // 💊 NUEVO: Array dinámico para el Recetario Electrónico
    medicamentos: [],
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

// Funciones para manipular la receta
const agregarMedicamento = () => {
    form.medicamentos.push({
        medicamento: '',
        dosis: '',
        frecuencia: '',
        duracion: '',
        indicaciones_extra: ''
    });
};

const eliminarMedicamento = (index) => {
    form.medicamentos.splice(index, 1);
};

const submit = () => {
    // Interceptor: Normalización de Talla
    if (form.talla && parseFloat(form.talla) > 3) {
        form.talla = (parseFloat(form.talla) / 100).toFixed(2);
    }

    form.post(route('consultations.store', props.patient.id), {
        preserveScroll: true, // Evita que la pantalla brinque hacia arriba
        /*onError: (errors) => {
            console.error("ERRORES DEL BACKEND:", errors); // Ahora sí lo verás en F12
        }*/
    });
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

                            <div class="bg-indigo-50 -mx-6 px-6 py-6 border-t border-indigo-100">
                                <label class="block text-sm font-bold text-indigo-900">Plan / Indicaciones Generales (P)</label>
                                <p class="text-xs text-indigo-700 mb-2 font-medium">Reposo, dieta, estudios solicitados y próxima cita (No incluir fármacos aquí).</p>
                                <textarea v-model="form.tratamiento" rows="3" placeholder="1. Dieta blanda. 2. Reposo por 3 días..." class="w-full rounded-xl border-indigo-300 bg-white focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition"></textarea>
                            </div>
                            
                            <div class="bg-emerald-50 -mx-6 px-6 py-6 border-t border-emerald-100 rounded-b-xl">
                                <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-3">
                                    <div>
                                        <label class="block text-sm font-bold text-emerald-900 flex items-center">
                                            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                            Receta Médica (Fármacos)
                                        </label>
                                        <p class="text-xs text-emerald-700 font-medium">Estos medicamentos conformarán el PDF oficial de la receta.</p>
                                    </div>
                                    <button type="button" @click="agregarMedicamento" class="px-4 py-2 bg-emerald-600 text-white text-sm font-bold rounded-lg hover:bg-emerald-700 transition shadow-sm flex items-center transform hover:-translate-y-0.5">
                                        + Agregar Fármaco
                                    </button>
                                </div>

                                <div v-if="form.medicamentos.length > 0" class="space-y-4">
                                    <div v-for="(med, index) in form.medicamentos" :key="index" class="bg-white p-5 rounded-xl border border-emerald-200 shadow-sm relative group transition hover:border-emerald-400">
                                        
                                        <button type="button" @click="eliminarMedicamento(index)" class="absolute top-3 right-3 text-gray-400 hover:text-red-500 transition p-1 bg-gray-50 hover:bg-red-50 rounded-md" title="Eliminar medicamento">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>

                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pr-6">
                                            <div class="md:col-span-12">
                                                <label class="block text-xs font-bold text-gray-700 mb-1">Medicamento (Sustancia y Presentación) *</label>
                                                <input type="text" v-model="med.medicamento" placeholder="Ej. Paracetamol 500 mg Tabletas" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm text-sm" required>
                                            </div>
                                            
                                            <div class="md:col-span-3">
                                                <label class="block text-xs font-bold text-gray-700 mb-1">Dosis *</label>
                                                <input type="text" v-model="med.dosis" placeholder="Ej. 1 tableta" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm text-sm" required>
                                            </div>
                                            
                                            <div class="md:col-span-4">
                                                <label class="block text-xs font-bold text-gray-700 mb-1">Frecuencia *</label>
                                                <input type="text" v-model="med.frecuencia" placeholder="Ej. Cada 8 horas" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm text-sm" required>
                                            </div>
                                            
                                            <div class="md:col-span-5">
                                                <label class="block text-xs font-bold text-gray-700 mb-1">Duración *</label>
                                                <input type="text" v-model="med.duracion" placeholder="Ej. Por 5 días" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm text-sm" required>
                                            </div>

                                            <div class="md:col-span-12">
                                                <label class="block text-xs font-bold text-gray-700 mb-1">Indicaciones Adicionales (Opcional)</label>
                                                <input type="text" v-model="med.indicaciones_extra" placeholder="Ej. Tomar junto con los alimentos para evitar irritación gástrica" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm text-sm">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-else class="text-center py-8 bg-white bg-opacity-60 rounded-xl border-2 border-dashed border-emerald-200">
                                    <p class="text-sm text-emerald-600 font-semibold mb-1">No hay medicamentos en la receta</p>
                                    <p class="text-xs text-gray-500">Haz clic en el botón superior para agregar el primer fármaco.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-xl shadow-sm">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-bold text-red-800">Faltan datos o hay errores en el formato:</h3>
                                <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                                    <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end pb-12">
                        <button type="submit" :disabled="form.processing" class="px-8 py-4 bg-gray-900 text-white rounded-xl font-bold text-lg hover:bg-gray-800 transition transform hover:-translate-y-1 shadow-lg flex items-center disabled:opacity-50 disabled:transform-none">
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            🔒 Firmar Nota y Generar Receta
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