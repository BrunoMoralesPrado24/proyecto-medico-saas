<script setup>
import { computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    modelValue: Object
});
const emit = defineEmits(['update:modelValue']);

// Sincronización limpia con el formulario padre
const form = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

// ==========================================
// 🧮 LÓGICA MÉDICA AVANZADA (NOM-008 y OMS)
// ==========================================

// 1. Cálculo Automático del IMC
const imcCalculado = computed(() => {
    const peso = parseFloat(form.value.peso);
    const talla = parseFloat(form.value.talla); // Esperamos metros (ej. 1.70)

    // Si metieron la talla en centímetros (ej. 170), la convertimos a metros automáticamente
    const tallaEnMetros = talla > 3 ? talla / 100 : talla;

    if (peso > 0 && tallaEnMetros > 0) {
        const imc = peso / (tallaEnMetros * tallaEnMetros);
        return parseFloat(imc.toFixed(2));
    }
    return 0;
});

// 2. Clasificador Clínico de IMC
const diagnosticoIMC = computed(() => {
    const imc = imcCalculado.value;

    if (imc === 0) return { texto: 'Esperando datos...', clase: 'bg-gray-100 text-gray-500 border-gray-200' };

    // Usamos una "cascada" para atrapar todos los decimales intermedios
    if (imc < 18.5) return { texto: 'Bajo Peso', alerta: '⚠️ Posible desnutrición', clase: 'bg-blue-100 text-blue-700 border-blue-300' };
    if (imc < 25.0) return { texto: 'Normal', alerta: '✅ Rango Saludable', clase: 'bg-green-100 text-green-700 border-green-300' };
    if (imc < 30.0) return { texto: 'Sobrepeso', alerta: '⚠️ Pre-obesidad', clase: 'bg-yellow-100 text-yellow-800 border-yellow-300' };
    if (imc < 35.0) return { texto: 'Obesidad Grado I', alerta: '🚨 Riesgo Moderado', clase: 'bg-orange-100 text-orange-800 border-orange-400' };
    if (imc < 40.0) return { texto: 'Obesidad Grado II', alerta: '🚨 Riesgo Alto', clase: 'bg-red-100 text-red-700 border-red-400' };

    // Si sobrevivió hasta aquí, es 40 o más
    return { texto: 'Obesidad Grado III', alerta: '⛔ Riesgo Extremo (Mórbida)', clase: 'bg-red-900 text-white border-red-900' };
});

// 3. Semáforo: Alerta de Presión Arterial (NOM-030)
const alertaPresion = computed(() => {
    const sis = parseInt(form.value.presion_sistolica);
    const dia = parseInt(form.value.presion_diastolica);
    return sis > 130 || dia > 85;
});
</script>

<template>
    <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm space-y-6">
        <h3 class="text-lg font-bold text-gray-800 border-b pb-2">Signos Vitales y Somatometría</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="p-4 bg-gray-50 rounded-lg space-y-4 border border-gray-100 flex flex-col justify-between">
                <div>
                    <h4 class="font-semibold text-gray-600 text-sm uppercase tracking-wider mb-4">Somatometría</h4>

                    <div class="space-y-4">
                        <div>
                            <InputLabel value="Peso (kg)" />
                            <TextInput type="number" step="0.1" v-model="form.peso" class="mt-1 block w-full" placeholder="Ej. 75.5" />
                        </div>

                        <div>
                            <InputLabel value="Talla (metros)" />
                            <TextInput type="number" step="0.01" v-model="form.talla" class="mt-1 block w-full" placeholder="Ej. 1.70" />
                        </div>
                    </div>
                </div>

                <div class="pt-4 mt-2 border-t border-gray-200">
                    <p class="text-sm font-medium text-gray-700">Índice de Masa Corporal:</p>
                    <div
                        class="mt-2 px-4 py-3 rounded-lg border transition-colors duration-500 flex flex-col items-center justify-center text-center"
                        :class="diagnosticoIMC.clase"
                    >
                        <span class="text-3xl font-black mb-1">{{ imcCalculado > 0 ? imcCalculado : '--' }}</span>
                        <span class="text-sm font-bold uppercase tracking-wider">{{ diagnosticoIMC.texto }}</span>
                        <span v-if="imcCalculado > 0" class="text-xs font-medium mt-1 opacity-90">{{ diagnosticoIMC.alerta }}</span>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 p-4 bg-gray-50 rounded-lg space-y-4 border border-gray-100">
                <h4 class="font-semibold text-gray-600 text-sm uppercase tracking-wider">Signos Vitales</h4>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 grid grid-cols-2 gap-4 p-3 rounded-lg transition-colors duration-300" :class="alertaPresion ? 'bg-red-50 border border-red-200' : ''">
                        <div class="col-span-2 mb-[-10px]">
                            <p class="text-sm font-medium text-gray-700">
                                Presión Arterial (mmHg)
                                <span v-if="alertaPresion" class="text-red-600 font-bold ml-2">⚠️ Presión Elevada</span>
                            </p>
                        </div>
                        <div>
                            <InputLabel value="Sistólica (Alta)" />
                            <TextInput type="number" v-model="form.presion_sistolica" class="mt-1 block w-full" :class="alertaPresion ? 'border-red-400 focus:ring-red-500' : ''" placeholder="120" />
                        </div>
                        <div>
                            <InputLabel value="Diastólica (Baja)" />
                            <TextInput type="number" v-model="form.presion_diastolica" class="mt-1 block w-full" :class="alertaPresion ? 'border-red-400 focus:ring-red-500' : ''" placeholder="80" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Frec. Cardíaca (lpm)" />
                        <TextInput type="number" v-model="form.frecuencia_cardiaca" class="mt-1 block w-full" placeholder="Ej. 75" />
                    </div>

                    <div>
                        <InputLabel value="Frec. Respiratoria (rpm)" />
                        <TextInput type="number" v-model="form.frecuencia_respiratoria" class="mt-1 block w-full" placeholder="Ej. 16" />
                    </div>

                    <div>
                        <InputLabel value="Temperatura (°C)" />
                        <TextInput type="number" step="0.1" v-model="form.temperatura" class="mt-1 block w-full" placeholder="Ej. 36.5" />
                    </div>

                    <div>
                        <InputLabel value="Oxigenación (SpO2 %)" />
                        <TextInput type="number" v-model="form.oxigenacion" class="mt-1 block w-full" placeholder="Ej. 98" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
