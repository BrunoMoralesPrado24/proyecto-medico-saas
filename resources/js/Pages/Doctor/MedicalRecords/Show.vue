<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
// Importamos el Sidebar para mantener el diseño consistente
import Sidebar from '@/Components/Sidebar.vue';

const props = defineProps({
    patient: Object,
});

// Extraemos el historial si existe, si no, inicializamos un objeto vacío
const history = props.patient.medical_history || {};

const form = useForm({
    alergias: history.alergias || '',
    antecedentes_gineco_obstetricos: history.antecedentes_gineco_obstetricos || '',
    personales_patologicos: history.personales_patologicos || '',
    antecedentes_heredofamiliares: history.antecedentes_heredofamiliares || '',
    personales_no_patologicos: history.personales_no_patologicos || '',
});

// Lógica inteligente para el Checkbox del AGO
const agoNoAplica = ref(history.antecedentes_gineco_obstetricos === 'No aplica.');

watch(agoNoAplica, (isNotApplicable) => {
    if (isNotApplicable) {
        form.antecedentes_gineco_obstetricos = 'No aplica.';
    } else {
        if (form.antecedentes_gineco_obstetricos === 'No aplica.') {
            form.antecedentes_gineco_obstetricos = '';
        }
    }
});

// Calculamos la edad rápido para la cabecera
const edad = computed(() => {
    if (!props.patient.fecha_nacimiento) return 'N/A';
    const hoy = new Date();
    const nacimiento = new Date(props.patient.fecha_nacimiento);
    let e = hoy.getFullYear() - nacimiento.getFullYear();
    const m = hoy.getMonth() - nacimiento.getMonth();
    if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) e--;
    return e;
});

const isSaving = ref(false);

const submit = () => {
    isSaving.value = true;
    form.put(route('medical-records.update', props.patient.id), {
        preserveScroll: true,
        onFinish: () => isSaving.value = false,
    });
};
</script>

<template>
    <div class="flex h-screen bg-gray-100 font-sans">
        
        <Sidebar />

        <main class="flex-1 overflow-y-auto">
            <div class="max-w-5xl mx-auto py-8 sm:px-6 lg:px-8">
                
                <div class="bg-white shadow-sm rounded-2xl p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between border-l-4 border-blue-600">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-2xl font-bold text-gray-900">{{ patient.nombre }}</h1>
                        <p class="text-sm text-gray-500 mt-1 flex gap-3">
                            <span>Edad: <strong class="text-gray-700">{{ edad }} años</strong></span>
                            <span>Sexo: <strong class="text-gray-700">{{ patient.sexo || 'No especificado' }}</strong></span>
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <Link :href="route('medical-records.index')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition text-sm">
                            Volver a la Lista
                        </Link>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold shadow-md hover:bg-blue-700 transition text-sm">
                            + Iniciar Consulta
                        </button>
                    </div>
                </div>

                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-800">Antecedentes Clínicos Permanentes (NOM-004)</h2>
                        <transition name="fade">
                            <span v-if="form.recentlySuccessful" class="text-sm text-green-600 font-bold bg-green-100 px-3 py-1 rounded-full">
                                ✅ Guardado correctamente
                            </span>
                        </transition>
                    </div>

                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            
                            <div class="col-span-1 md:col-span-2">
                                <label class="flex items-center text-sm font-bold text-red-600 mb-2">
                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    Alergias Conocidas
                                </label>
                                <textarea v-model="form.alergias" rows="2" 
                                    placeholder="Penicilina, polen, látex. Si no hay, escribir 'Negadas'." 
                                    class="w-full rounded-xl border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-500 shadow-sm"></textarea>
                            </div>

                            <div v-if="['Femenino', 'Otro'].includes(patient.sexo)" class="col-span-1 md:col-span-2 bg-pink-50 p-4 rounded-xl border border-pink-200 transition-all">
                                <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-3">
                                    <label class="block text-sm font-bold text-pink-800 mb-2 sm:mb-0">Antecedentes Gineco-Obstétricos (AGO)</label>
                                    
                                    <label class="flex items-center space-x-2 text-sm text-pink-700 cursor-pointer hover:text-pink-900 transition bg-white px-3 py-1.5 rounded-lg border border-pink-200 shadow-sm">
                                        <input type="checkbox" v-model="agoNoAplica" class="rounded border-pink-400 text-pink-600 focus:ring-pink-500 cursor-pointer">
                                        <span class="font-semibold select-none">No aplica / Sin matriz</span>
                                    </label>
                                </div>

                                <textarea v-model="form.antecedentes_gineco_obstetricos" 
                                    :disabled="agoNoAplica"
                                    rows="3" 
                                    placeholder="Menarca, ritmo, FUM, gestaciones, partos, cesáreas, abortos, métodos anticonceptivos..." 
                                    class="w-full rounded-lg border-pink-300 focus:border-pink-500 focus:ring-pink-500 shadow-sm transition-colors"
                                    :class="agoNoAplica ? 'bg-gray-100 text-gray-500 cursor-not-allowed border-gray-300' : 'bg-white'">
                                </textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Personales Patológicos</label>
                                <textarea v-model="form.personales_patologicos" rows="4" 
                                    placeholder="Enfermedades crónicas, cirugías previas, hospitalizaciones..." 
                                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Antecedentes Heredofamiliares</label>
                                <textarea v-model="form.antecedentes_heredofamiliares" rows="4" 
                                    placeholder="Diabetes en padres, hipertensión, cáncer en línea directa..." 
                                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm"></textarea>
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Personales No Patológicos</label>
                                <textarea v-model="form.personales_no_patologicos" rows="3" 
                                    placeholder="Tabaquismo, alcoholismo, toxicomanías, tipo de alimentación, actividad física..." 
                                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm"></textarea>
                            </div>

                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                            <button type="submit" :disabled="form.processing"
                                class="px-8 py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition transform hover:-translate-y-0.5 shadow-lg flex items-center disabled:opacity-50">
                                <svg v-if="isSaving" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Guardar Historial Clínico
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Transición suave para el mensaje de guardado */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>