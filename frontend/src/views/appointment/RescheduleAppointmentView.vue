<script setup>
import {onMounted, ref} from "vue";
import {AppointmentService} from "@/services/appointment-service.js";
import Swal from "sweetalert2";
import {APPOINTMENT_SEARCH_LENGTH as ASL, APPOINTMENT_TYPE as AT, ERROR_MESSAGES as EM} from "@/utils/constants.js";
import {useRoute, useRouter} from "vue-router";
import {useAuthStore} from "@/stores/auth.js";
import {DoctorService} from "@/services/doctor-service.js";
import {formatAsDate, formatAsTime, reloadOnDismiss, reloadPage} from "@/utils/helpers.js";
import dayjs from "dayjs";
import AppointmentCard from "@/components/appointment/AppointmentCard.vue";
import ReschedulingAppSlotsModal from "@/components/appointment/ReschedulingAppSlotsModal.vue";

const router = useRouter();
const route = useRoute();
const authService = useAuthStore();
const appointment = ref({});
const isLoading = ref(false);
const loadError = ref(false);

const doctors = ref({});
const doctorsLoaded = ref(false);
const doctorId = ref();
const appointmentsLoaded = ref(false);
const availableAppointments = ref([]);
const daysAhead = ref(7);
const slotLength = ref(30);
const showUnavailabilities = ref(false);
const today = new Date().toISOString().split('T')[0];
const onDate = ref(today);
const loadingMessage = ref('Cargando información...');

const selectedDay = ref({});
const showSlotSelectionModal = ref(false);

async function loadAppointment(id) {
  isLoading.value = true;
  try {
    appointment.value = await AppointmentService.getById(id);
    await loadAvailableDoctors();
  } catch (err) {
    loadError.value = true;
    Swal.fire(EM.ERROR_TAG, EM.APPOINTMENT_NOT_FOUND, 'error').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        router.back();
      }
    });
  }
}

async function loadAvailableDoctors() {
  isLoading.value = true;
  try {
    const response = await DoctorService.getAvailables();
    if (response.length <= 0) {
      await loadDoctors();
    } else {
      doctors.value = response;
      doctorsLoaded.value = true;
      isLoading.value = false;
      doctorId.value = doctors.value[0]?.id;
      findOldDoctor(appointment.value.doctor?.id);
    }
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, EM.DOCTORS_NOT_LOADED, 'error').then((r) => reloadOnDismiss(r));
  }
}

async function loadDoctors() {
  isLoading.value = true;
  try {
    doctors.value = await DoctorService.getAll();
    if (doctors.value.length <= 0) {
      Swal.fire(EM.ERROR_TAG, EM.EMPTY_DOCTORS_TABLE, 'error').then((r) => {
        if (r.isConfirmed || r.isDismissed || r.dismiss) {
          router.push({name: 'new-doctor'});
        }
      });
    } else {
      doctorId.value = doctors.value[0]?.id;
      findOldDoctor(appointment.value.doctor?.id);
    }
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, EM.DOCTORS_NOT_LOADED, 'error').then((r) => reloadOnDismiss(r));
  } finally {
    isLoading.value = false;
    doctorsLoaded.value = true;
  }
}

async function onPrepareAppointments() {
  if (verifySearchValues()) {
    if (isCurrentDate(onDate.value)) {
      //Proceder con busqueda por RANGO.
      loadingMessage.value = 'Buscando citas disponibles...';
      isLoading.value = true;
      const query = {
        doctor_id: doctorId.value,
        days_ahead: daysAhead.value,
        slot_length: slotLength.value,
        patient_dni: appointment.value.patient?.dni,
        show_unavailabilities: showUnavailabilities.value,
      };
      await prepareAppointments(query);

    } else {
      //Proceder con busqueda por FECHA.
      loadingMessage.value = 'Buscando citas en fecha especifica...';
      isLoading.value = true;
      const query = {
        doctor_id: doctorId.value,
        slot_length: slotLength.value,
        patient_dni: appointment.value.patient?.dni,
        show_unavailabilities: showUnavailabilities.value,
        on_date: onDate.value,
      };
      await prepareAppointments(query);
    }
  } else {
    Swal.fire(EM.ERROR_TAG, EM.INVALID_APPOINTMENT_PREPARATION, 'error').then((r) => reloadOnDismiss(r));
  }
}

async function prepareAppointments(query) {
  try {
    availableAppointments.value = await AppointmentService.prepare(query);
    appointmentsLoaded.value = true;
  } catch (err) {
    appointmentsLoaded.value = false;
    Swal.fire(EM.ERROR_TAG, EM.SERVER_ERROR, 'error').then((r) => reloadOnDismiss(r));
  } finally {
    isLoading.value = false;
  }
}

function handleSelectedDay(dayInfo) {
  selectedDay.value = dayInfo;
  handleSlotSelectionStatus();
}

function handleSlotSelectionStatus() {
  showSlotSelectionModal.value = !showSlotSelectionModal.value;
}

function closeSlotSelectionModal() {
  selectedDay.value = {};
  handleSlotSelectionStatus();
}

function isCurrentDate(date) {
  const date1 = dayjs(date);
  return date1.isSame(dayjs(), 'day');
}


function verifySearchValues() {
  return doctorId.value >= 1 && daysAhead.value >= 1 && slotLength.value >= 1;
}

function onReset() {
  if (appointmentsLoaded.value === true) {
    appointmentsLoaded.value = false;
    availableAppointments.value = [];
    onDate.value = today;
    selectedDay.value = {};
  } else {
    reloadPage();
  }
  //reset values to search appointments again.
}

function findOldDoctor(oldId) {
  doctors.value.forEach((d) => {
    if (d.id === oldId) {
      doctorId.value = oldId;
    }
  });
}

function getSelectedDoctor(doctorId) {
  return doctors.value.find((d) => d.id === doctorId);
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - REPROGRAMACIÓN DE CITA';
  const id = route.params.id;
  if (isNaN(id)) {
    router.back();
  }
  loadAppointment(id);
});
</script>

<template>
  <main class="flex flex-col items-center pt-5 relative">
    <div class="container px-12 mx-auto">
      <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm w-full">
        <h5 v-if="!isLoading" class="mb-5 text-2xl font-bold tracking-tight text-black text-center">REPROGRAMACIÓN DE
          CITA #{{ appointment.id }}</h5>
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
          <h1 class="mt-5 text-2xl font-light">{{ loadingMessage }}</h1>
        </div>

        <div v-if="!isLoading && doctorsLoaded">
          <div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow-md">
            <div class="grid grid-cols-4 gap-5">

              <div class="col">
                <h1 class="font-lighter text-2xl">Datos del Paciente</h1>
                <div class="mt-3">
                  <span class="mt-5 font-bold">Nombre: <span class="font-light">{{
                      appointment.patient?.name
                    }}</span></span>
                  <br>
                  <span class="mt-5 font-bold">Apellidos: <span
                      class="font-light">{{
                      `${appointment.patient?.paternal_surname} ${appointment.patient?.maternal_surname}`
                    }}</span></span>
                  <br>
                  <span class="mt-5 font-bold">DNI: <span class="font-light">{{
                      appointment.patient?.dni
                    }}</span></span>
                  <br>
                  <span class="mt-5 font-bold">E-Mail: <span class="font-light">{{ appointment.patient?.email }}</span></span>
                </div>
              </div>
              <div class="col">
                <h1 class="font-lighter text-2xl">Rango de Busqueda</h1>
                <div :hidden="true" class="mt-3">
                  <select id="selectedAppointmentT"
                          v-model="slotLength" :disabled="appointmentsLoaded"
                          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-700 focus:border-green-700 block w-full p-2.5"
                          name="selectedAppointmentT">
                    <option v-for="at in AT" :key="at.value" :value="at.value">
                      {{ at.label }}
                    </option>
                  </select>
                </div>

                <div class="mt-3">
                  <select id="daysAheadControl"
                          v-model="daysAhead" :disabled="appointmentsLoaded"
                          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-700 focus:border-green-700 block w-full p-2.5"
                          name="daysAheadControl">
                    <option v-for="al in ASL" :key="al.value" :value="al.value">
                      {{ al.label }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col">
                <h1 class="font-lighter text-2xl">Avanzado</h1>
                <div class="mt-3">
                  <label class="inline-flex items-center me-5 cursor-pointer">
                    <input v-model="showUnavailabilities" :checked="showUnavailabilities" :disabled="appointmentsLoaded"
                           class="sr-only peer" type="checkbox">
                    <div
                        class="relative w-11 h-6 bg-gray-200 rounded-full peer  peer-focus:ring-4 peer-focus:ring-green-300 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                    <span class="ms-3 text-sm font-medium text-gray-900">Indisponibilidades</span>
                  </label>

                  <label class="block mb-1 text-sm font-medium text-gray-900">Fecha</label>
                  <input id="onDate"
                         v-model="onDate"
                         :min="today"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                         name="onDate" type="date"/>
                </div>
              </div>
              <div class="col">
                <h1 class="font-lighter text-2xl">Doctor</h1>
                <div class="mt-3 flex flex-col mt-3 justify-center items-center">
                  <select id="selectedDoctor"
                          v-model="doctorId" :disabled="appointmentsLoaded"
                          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-700 focus:border-green-700 block w-full p-2.5"
                          name="selectedDoctor">
                    <option v-for="d in doctors" :key="d.id" :value="d.id">
                      {{
                        `${d.name} ${d.paternal_surname !== '' ? d.paternal_surname : d.maternal_surname} - ${d.current_week_availabilities}`
                      }}
                    </option>
                  </select>
                  <div class="flex justify-center mt-5">
                    <div class="inline-flex rounded-md shadow-xs" role="group">
                      <button
                          class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-s rounded-s-lg border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                          type="button"
                          @click="onReset">
                        <i class="bi bi-arrow-clockwise w-3 h-3 me-2 flex items-center justify-center"></i>
                        Reset
                      </button>
                      <button :disabled="!verifySearchValues || appointmentsLoaded"
                              class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                              type="button"
                              @click="onPrepareAppointments">
                        <i class="bi bi-search w-3 h-3 me-2 flex items-center justify-center"></i>
                        Buscar
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 mt-3" role="alert">
              <svg aria-hidden="true" class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" fill="currentColor"
                   viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
              </svg>
              <span class="sr-only">Info</span>
              <div>
                <span class="font-medium">Ústed esta reprogramando la siguiente cita:</span>
                <ul class="mt-1.5 list-disc list-inside">
                  <li class="text-black"><span class="font-bold text-yellow-800">FECHA:</span>
                    {{
                      appointment.rescheduling_date ? formatAsDate(appointment.rescheduling_date) : formatAsDate(appointment.date)
                    }}
                  </li>
                  <li class="text-black"><span class="font-bold text-yellow-800">HORA:</span>
                    {{
                      appointment.rescheduling_time ? formatAsTime(appointment.rescheduling_time) : formatAsTime(appointment.time)
                    }}
                  </li>
                  <li class="text-black"><span class="font-bold text-yellow-800">TIPO DE ATENCIÓN:</span>
                    {{ appointment.is_remote ? 'VIRTUAL' : 'PRESENCIAL' }}
                  </li>
                  <li class="text-black"><span class="font-bold text-yellow-800">DOCTOR:</span>
                    {{
                      `${appointment.doctor?.name} ${appointment.doctor?.paternal_surname} ${appointment.doctor?.maternal_surname}`
                    }}
                  </li>
                </ul>
                <span class="font-medium">Recuerde que debe reprogramar la cita con el mismo doctor; en caso no se encuentre disponible, puede asignarla a otro doctor.</span>
              </div>
            </div>

          </div>
          <div v-if="appointmentsLoaded" class="mt-5 pt-3 ms-5">
            <div class="grid grid-cols-5 gap-2">
              <AppointmentCard v-for="ap in availableAppointments" :key="ap.dateKey" :dayInfo="ap"
                               @selectedDay="handleSelectedDay"></AppointmentCard>
            </div>
          </div>
          <div v-if="appointmentsLoaded && availableAppointments.length <= 0"
               class="container mt-5 mb-5 flex flex-col items-center space-y-5">
            <span><i class="bi bi-exclamation-triangle-fill text-9xl text-red-700"></i></span>
            <h1 class="text-2xl font-light">Oops! No se encontraron citas disponibles para el periodo ingresado. Intente
              nuevamente con diferentes parámetros.</h1>
          </div>
        </div>
      </div>
      <ReschedulingAppSlotsModal v-if="showSlotSelectionModal" :doctorInfo="getSelectedDoctor(doctorId)"
                                 :oldAppointment="appointment"
                                 :onClose="closeSlotSelectionModal" :patientInfo="appointment.patient"
                                 :selectedDay="selectedDay"></ReschedulingAppSlotsModal>
    </div>
  </main>
</template>
