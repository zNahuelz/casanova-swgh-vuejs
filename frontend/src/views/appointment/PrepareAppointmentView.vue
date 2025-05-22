<script setup>
import {onMounted, ref} from "vue";
import {ErrorMessage, Field, Form} from "vee-validate";
import {DoctorService} from "@/services/doctor-service.js";
import Swal from "sweetalert2";
import {APPOINTMENT_SEARCH_LENGTH as ASL, APPOINTMENT_TYPE as AT, ERROR_MESSAGES as EM} from "@/utils/constants.js";
import {reloadOnDismiss, reloadPage} from "@/utils/helpers.js";
import {useRoute, useRouter} from "vue-router";
import * as yup from "yup";
import {PatientService} from "@/services/patient-service.js";
import dayjs from "dayjs";
import {AppointmentService} from "@/services/appointment-service.js";
import AppointmentCard from "@/components/appointment/AppointmentCard.vue";
import AppointmentSlotsModal from "@/components/appointment/AppointmentSlotsModal.vue";

const isLoading = ref(false);
const loadingMessage = ref('Cargando recursos...');
const doctors = ref({});
const patient = ref({});
const lockedPatient = ref(false);
const doctorsLoaded = ref(false);
const router = useRouter();
const route = useRoute();

const daysAhead = ref(7);
const slotLength = ref(30);
const doctorId = ref();
const showUnavailabilities = ref(false);
const today = new Date().toISOString().split('T')[0];
const onDate = ref(today);
const appointmentsLoaded = ref(false);
const availableAppointments = ref([]);

const selectedDay = ref({});
const showSlotSelectionModal = ref(false);


const schema = yup.object().shape({
  dni: yup
      .string()
      .required('Debe ingresar un DNI.')
      .matches(/^[0-9]{8,15}$/, 'El DNI solo debe contener números.')
      .min(8, 'El DNI debe tener entre 8 y 15 cifras.')
      .max(15, 'El DNI debe tener entre 8 y 15 cifras.'),
});

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
    }
    //console.log(doctors.value);
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
    }
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, EM.DOCTORS_NOT_LOADED, 'error').then((r) => reloadOnDismiss(r));
  } finally {
    isLoading.value = false;
    doctorsLoaded.value = true;
  }
}

async function searchPatient(values) {
  loadingMessage.value = 'Buscando paciente...';
  isLoading.value = true;
  try {
    patient.value = await PatientService.getByDni(values.dni.trim()); //values.dni.trim() --- 95677094
    lockedPatient.value = true;
    if (patient.value.dni === '00000000') {
      Swal.fire(EM.ERROR_TAG, EM.DEFAULT_PATIENT_NOT_AVAILABLE, 'warning').then((r) => reloadOnDismiss(r));
      lockedPatient.value = false;
    }
  } catch (err) {
    Swal.fire({
      title: 'Consulta de DNI',
      html: `${EM.DNI_PATIENT_NOT_FOUND} <br> ¿Desea registrar al paciente?`,
      icon: 'question',
      showCancelButton: true,
      cancelButtonText: 'NO',
      confirmButtonText: 'SI',
      confirmButtonColor: '#008236',
      cancelButtonColor: '#e7000b',
    }).then((op) => {
      if (op.isConfirmed) {
        router.push({name: 'new-patient'});
      } else {
        reloadPage();
      }
    });
  } finally {
    isLoading.value = false;
  }
}

async function onPrepareAppointments() {
  console.log(showUnavailabilities.value);
  if (verifySearchValues()) {
    if (isCurrentDate(onDate.value)) {
      //Proceder con busqueda por RANGO.
      loadingMessage.value = 'Buscando citas disponibles...';
      isLoading.value = true;
      const query = {
        doctor_id: doctorId.value,
        days_ahead: daysAhead.value,
        slot_length: slotLength.value,
        patient_dni: patient.value.dni,
        show_unavailabilities: showUnavailabilities.value,
      };
      console.log(query);
      await prepareAppointments(query);

    } else {
      //Proceder con busqueda por FECHA.
      loadingMessage.value = 'Buscando citas en fecha especifica...';
      isLoading.value = true;
      const query = {
        doctor_id: doctorId.value,
        slot_length: slotLength.value,
        patient_dni: patient.value.dni,
        show_unavailabilities: showUnavailabilities.value,
        on_date: onDate.value,
      };
      console.log(query);
      await prepareAppointments(query);
    }
  } else {
    console.error('error pibe');
  }
}

async function prepareAppointments(query) {
  try {
    availableAppointments.value = await AppointmentService.prepare(query);
    appointmentsLoaded.value = true;
    console.log(availableAppointments.value);
  } catch (err) {
    console.log(err);
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

function getSelectedDoctor(doctorId) {
  return doctors.value.find((d) => d.id === doctorId);
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

function verifySearchValues() {
  return doctorId.value >= 1 && daysAhead.value >= 1 && slotLength.value >= 1;
}

function isCurrentDate(date) {
  const date1 = dayjs(date);
  return date1.isSame(dayjs(), 'day');
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - RESERVA DE CITAS'
  loadAvailableDoctors(); //.then(() => searchPatient(null)) //Remove on deployment.
});
</script>

<template>
  <main class="flex flex-col items-center pt-5 relative">
    <div class="container px-12 mx-auto">
      <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm w-full">
        <h5 v-if="!isLoading" class="mb-5 text-2xl font-bold tracking-tight text-black text-center">RESERVA DE CITA</h5>
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
        <div class="container">
          <Form v-if="!isLoading && !lockedPatient" :validation-schema="schema" class="max-w-md mx-auto mt-4"
                @submit="searchPatient">
            <div class="relative">
              <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <svg aria-hidden="true" class="w-4 h-4 text-gray-500"
                     fill="none"
                     viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" stroke="currentColor" stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"/>
                </svg>
              </div>
              <Field id="dni"
                     :validate-on-input="true"
                     class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-green-500 focus:border-green-500"
                     name="dni"
                     placeholder="Ingrese DNI del paciente"
                     type="text"
              />
              <div class="absolute flex gap-2 end-2.5 bottom-2.5">
                <button
                    :disabled="isLoading"
                    class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2"
                    type="submit"
                >
                  Buscar
                </button>
              </div>
            </div>
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium" name="dni"></ErrorMessage>
          </Form>
        </div>
        <div v-if="!isLoading && lockedPatient && doctorsLoaded">
          <div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow-md">
            <div class="grid grid-cols-4 gap-5">

              <div class="col">
                <h1 class="font-lighter text-2xl">Datos del Paciente</h1>
                <div class="mt-3">
                  <span class="mt-5 font-bold">Nombre: <span class="font-light">{{ patient?.name }}</span></span>
                  <br>
                  <span class="mt-5 font-bold">Apellidos: <span
                      class="font-light">{{ `${patient?.paternal_surname} ${patient?.maternal_surname}` }}</span></span>
                  <br>
                  <span class="mt-5 font-bold">DNI: <span class="font-light">{{ patient?.dni }}</span></span>
                  <br>
                  <span class="mt-5 font-bold">E-Mail: <span class="font-light">{{ patient?.email }}</span></span>
                </div>
              </div>
              <div class="col">
                <h1 class="font-lighter text-2xl">Datos de la Cita</h1>
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
      <AppointmentSlotsModal v-if="showSlotSelectionModal" :doctorInfo="getSelectedDoctor(doctorId)"
                             :onClose="closeSlotSelectionModal"
                             :patientInfo="patient" :selectedDay="selectedDay"></AppointmentSlotsModal>
    </div>
  </main>
</template>