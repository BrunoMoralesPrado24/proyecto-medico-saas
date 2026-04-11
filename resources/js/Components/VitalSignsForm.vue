<script setup>
import { computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

// Recibimos el modelo desde el formulario padre (la consulta)
const props = defineProps({
    modelValue: Object
});
const emit = defineEmits(['update:modelValue']);

// Sincronización limpia con el componente padre
const form = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

// ==========================================
// 🧮 LÓGICA MÉDICA (Checklist 3 y 4)
// ==========================================

// 1. Cálculo Automático del IMC
const imcCalculado = computed(() => {
    const peso = parseFloat(form.value.peso);
    const talla = parseFloat(form.value.talla);

    if (peso > 0 && talla > 0) {
        const imc = peso / (talla * talla);
        return imc.toFixed(2);
    }
    return '0.00';
});

// 2. Semáforo: Alerta de Obesidad (NOM-008)
const alertaObesidad = computed(() => {
    return parseFloat(imcCalculado.value) >= 30.0;
});

// 3. Semáforo: Alerta de Presión Arterial (NOM-030)
const alertaPresion = computed(() => {
    const sis = parseInt(form.value.presion_sistolica);
    const dia = parseInt(form.value.presion_diastolica);
    // Presión normal es < 120/80. Alerta si supera 130 o 85.
    return sis > 130 || dia > 85;
});
</script>

<template>
    <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm space-y-6">
        <h3 class="text-lg font-bold text-gray-800 border-b pb-2">Signos Vitales y Somatometría</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="p-4 bg-gray-50 rounded-lg space-y-4 border border-gray-100">
                <h4 class="font-semibold text-gray-600 text-sm uppercase tracking-wider">Somatometría</h4>

                <div>
                    <InputLabel value="Peso (kg)" />
                    <TextInput type="number" step="0.1" v-model="form.peso" class="mt-1 block w-full" placeholder="Ej. 75.5" />
                </div>

                <div>
                    <InputLabel value="Talla (metros)" />
                    <TextInput type="number" step="0.01" v-model="form.talla" class="mt-1 block w-full" placeholder="Ej. 1.75" />
                </div>

                <div class="pt-2">
                    <p class="text-sm font-medium text-gray-700">IMC Calculado:</p>
                    <div
                        class="mt-1 px-4 py-3 rounded-lg text-2xl font-bold transition-colors duration-300"
                        :class="alertaObesidad ? 'bg-red-100 text-red-700 border border-red-300' : 'bg-green-100 text-green-700 border border-green-300'"
                    >
                        {{ imcCalculado }}
                        <span v-if="alertaObesidad" class="text-sm block font-normal mt-1">⚠️ Riesgo de Obesidad</span>
                        <span v-else-if="imcCalculado > 0" class="text-sm block font-normal mt-1">✅ Rango Normal/Aceptable</span>
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
