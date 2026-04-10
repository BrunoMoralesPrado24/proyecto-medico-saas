<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';

// Importaciones de FullCalendar
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

const props = defineProps({
    appointments: Array,
    patients: Array,
});

// 1. Transformamos los datos de Laravel al formato que entiende FullCalendar
const calendarEvents = computed(() => {
    return props.appointments.map(app => ({
        id: app.id,
        title: app.patient ? app.patient.nombre : 'Paciente eliminado',
        // Cortamos los milisegundos y la 'Z' para evitar desfases de zona horaria
        start: app.start_time.split('.')[0],
        end: app.end_time.split('.')[0],
        color: app.status === 'confirmed' ? '#3b82f6' : '#9ca3af',
        extendedProps: {
            reason: app.reason_for_visit,
            patient_id: app.patient_id
        }
    }));
});

// 2. Control del Modal y el Formulario
const showModal = ref(false);
const form = useForm({
    patient_id: '',
    start_time: '',
    end_time: '',
    reason_for_visit: '',
});

// 4. Control del Modal de Detalles (Quick Action)
const showDetailsModal = ref(false);
const selectedAppointment = ref(null);

// 3. Configuración Principal del Calendario (AHORA ES REACTIVO)
const calendarOptions = computed(() => ({
    plugins: [ dayGridPlugin, timeGridPlugin, interactionPlugin ],
    initialView: 'timeGridWeek',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    // Le inyectamos los eventos con .value para que Vue los vigile
    events: calendarEvents.value,
    selectable: true,
    slotMinTime: '07:00:00',
    slotMaxTime: '21:00:00',
    allDaySlot: false,

    select: (selectInfo) => {
        form.start_time = selectInfo.startStr.slice(0, 16);
        form.end_time = selectInfo.endStr.slice(0, 16);
        showModal.value = true;
    },

    // Al hacer clic en una cita ya agendada (Abre el Modal de Detalles)
    eventClick: (clickInfo) => {
        selectedAppointment.value = {
            patient_name: clickInfo.event.title,
            reason: clickInfo.event.extendedProps.reason || 'Sin motivo registrado',
            patient_id: clickInfo.event.extendedProps.patient_id,
            // Formateamos la hora para que se vea bonita
            start_time: clickInfo.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
        };
        showDetailsModal.value = true;
    }
}));

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};

const submitAppointment = () => {
    form.post(route('appointments.store'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};
</script>

<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar />

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-16 bg-white border-b flex items-center justify-between px-6">
                <h2 class="font-semibold text-xl text-gray-800">Mi Agenda</h2>
                <div class="text-sm text-gray-600">
                    Conectado como: <span class="font-bold">{{ $page.props.auth.user.name }}</span>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">

                <div v-if="form.errors.start_time" class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
                    <p class="font-bold">⚠️ Conflicto de Horario</p>
                    <p>{{ form.errors.start_time }}</p>
                </div>

                <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
                    {{ $page.props.flash.success }}
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <FullCalendar :options="calendarOptions" />
                </div>
            </main>
        </div>

        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Agendar Nueva Cita
                </h2>

                <form @submit.prevent="submitAppointment" class="space-y-4">
                    <div>
                        <InputLabel for="patient_id" value="Paciente" />
                        <select
                            id="patient_id"
                            v-model="form.patient_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="" disabled>Seleccione un paciente...</option>
                            <option v-for="patient in patients" :key="patient.id" :value="patient.id">
                                {{ patient.nombre }}
                            </option>
                        </select>
                        <InputError :message="form.errors.patient_id" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="start_time" value="Inicio" />
                            <TextInput id="start_time" v-model="form.start_time" type="datetime-local" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <InputLabel for="end_time" value="Fin" />
                            <TextInput id="end_time" v-model="form.end_time" type="datetime-local" class="mt-1 block w-full" required />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="reason_for_visit" value="Motivo de la consulta (Opcional)" />
                        <TextInput id="reason_for_visit" v-model="form.reason_for_visit" type="text" class="mt-1 block w-full" placeholder="Ej. Revisión general" />
                        <InputError :message="form.errors.reason_for_visit" class="mt-2" />
                    </div>

                    <div class="flex justify-end pt-4 space-x-3">
                        <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Agendar Cita
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="showDetailsModal" @close="showDetailsModal = false">
            <div class="p-6" v-if="selectedAppointment">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Detalles de la Cita</h2>
                    <button @click="showDetailsModal = false" class="text-gray-400 hover:text-gray-600 transition text-2xl">&times;</button>
                </div>

                <div class="space-y-5">
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wider font-semibold">Paciente</p>
                        <p class="text-xl font-bold text-blue-700">{{ selectedAppointment.patient_name }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wider font-semibold">Horario de Inicio</p>
                        <p class="text-lg text-gray-800">🕒 {{ selectedAppointment.start_time }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wider font-semibold">Motivo de la Visita</p>
                        <p class="text-md text-gray-700 bg-gray-50 p-4 rounded-xl border border-gray-200 mt-1">
                            {{ selectedAppointment.reason }}
                        </p>
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-3">
                    <SecondaryButton @click="showDetailsModal = false">Cerrar</SecondaryButton>

                    <Link
                        :href="route('medical-records.show', selectedAppointment.patient_id)"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Abrir Expediente 🫀
                    </Link>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style>
/* Pequeño ajuste para que el calendario respire bien */
.fc .fc-toolbar-title {
    font-size: 1.25rem !important;
    font-weight: 600;
}
.fc-event {
    cursor: pointer;
}
</style>
