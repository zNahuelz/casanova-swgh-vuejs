<script setup>
import {onMounted, ref} from "vue";
import {AppointmentService} from "@/services/appointment-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM} from "@/utils/constants.js";
import {useRoute, useRouter} from "vue-router";
import {formatAsDate, formatAsDatetime, formatAsTime} from "@/utils/helpers.js";
import {useAuthStore} from "@/stores/auth.js";
import EditAppointmentNotesModal from "@/components/appointment/EditAppointmentNotesModal.vue";

const notes = ref([]);
const isLoading = ref(false);
const loadError = ref(false);
const route = useRoute();
const router = useRouter();
const authService = useAuthStore();
const showEditNotes = ref(false);
const selectedApp = ref({});

async function loadAppointments(dni) {
  isLoading.value = true;
  try {
    let counter = 0;
    notes.value = await AppointmentService.getNotesByDni(dni);
    loadError.value = false;
    isLoading.value = false;
    notes.value.forEach((e) => {
      if (e.notes?.length >= 4) {
        counter += 1;
      }
    });
    if (counter <= 0) {
      loadError.value = true;
    }
  } catch (err) {
    loadError.value = true;
    Swal.fire(EM.ERROR_TAG, EM.NOTES_NOT_FOUND, 'error').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        router.back();
      }
    });
  }
}

function goBack() {
  router.back();
}

function goToDetails(id) {
  router.push({name: 'appointment-detail', params: {id}});
}

function handleEditNotes(app) {
  selectedApp.value = app;
  showEditNotes.value = !showEditNotes.value;
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - HISTORIAL MÉDICO';
  const dni = route.params.dni;
  loadAppointments(dni);
});
</script>

<template>
  <main class="flex flex-col items-center pt-5">
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm min-w-150">
      <h5 v-if="!isLoading" class="mb-2 text-2xl font-bold tracking-tight text-black text-center">HISTORIAL MÉDICO</h5>

      <div v-if="isLoading" class="container mt-5 mb-5 flex flex-col items-center">
        <div role="status">
          <svg aria-hidden="true" class="inline w-30 h-30 text-gray-200 animate-spin  fill-green-600"
               fill="none" viewBox="0 0 100 101" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                fill="currentColor"/>
            <path
                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                fill="currentFill"/>
          </svg>
          <span class="sr-only">Loading...</span>
        </div>
        <h1 class="mt-5 text-2xl font-light">Cargando notas del paciente...</h1>
      </div>

      <div v-if="!isLoading && !loadError" class="container flex flex-col items-center">
        <div v-for="n in notes" :key="n.id"
             :class="{'hidden':n.notes?.length <= 3}"
             class="w-lg max-h-[300px] p-6 bg-white border border-gray-200 rounded-lg shadow-sm mb-3">

          <h5 class="mb-2 text-lg font-semibold tracking-tight text-gray-900 text-center">CITA:
            {{ n.rescheduling_date ? formatAsDate(n.rescheduling_date) : formatAsDate(n.date) }} - HORA:
            {{ n.rescheduling_time ? formatAsTime(n.rescheduling_time) : formatAsTime(n.time) }}</h5>

          <h5 class="mb-2 text-md font-semibold tracking-tight text-gray-900">ULT. CAMBIO:
            {{ formatAsDatetime(n.updated_at) }}</h5>


          <h5 class="mb-2 text-md font-semibold tracking-tight text-gray-900">DOCTOR:
            {{ `${n.doctor?.name} ${n.doctor?.paternal_surname}` }}</h5>

          <p class="mb-3 font-normal text-gray-500">{{ n.notes }}</p>
          <div class="flex flex-inline items-start">
            <button
                class="p-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-500 me-3"
                type="button"
                @click="goToDetails(n.id)"
            >
              <i class="bi bi-three-dots-vertical"></i> Detalle
            </button>
            <button
                :disabled="(!(authService.getTokenDetails().role === 'ADMINISTRADOR' || authService.getUserData()?.id === n.doctor?.id)
                        || n.status === 'CANCELADO'
                        || n.status === 'PENDIENTE'
                        || n.status === 'NO_ASISTIO')"
                class="p-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-500 disabled:cursor-not-allowed disabled:bg-gray-500"
                type="button"
                @click="handleEditNotes(n)"
            >
              <i class="bi bi-pencil-square"></i> Editar
            </button>
          </div>
        </div>
      </div>
      <div v-if="!isLoading && loadError"
           class="container mt-5  flex flex-col items-center">
        <span><i class="bi bi-exclamation-triangle-fill text-8xl text-red-700"></i></span>
        <h1 class="mt-5 text-2xl font-light text-center">El paciente no cuenta con notas asociadas.</h1>
        <span class="text-lg text-blue-800 font-bold hover:underline" @click="goBack">Regresar</span>
      </div>
    </div>
    <EditAppointmentNotesModal v-if="showEditNotes" :appointment="selectedApp"
                               :onClose="() => {showEditNotes = false;}"></EditAppointmentNotesModal>
  </main>
</template>