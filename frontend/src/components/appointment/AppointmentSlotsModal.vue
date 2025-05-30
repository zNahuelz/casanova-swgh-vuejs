<script setup>
import Swal from "sweetalert2";
import {APPOINTMENT_STATUS, ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {ref} from "vue";
import {AppointmentService} from "@/services/appointment-service.js";
import {useAuthStore} from "@/stores/auth.js";
import {reloadOnDismiss} from "@/utils/helpers.js";
import {useRouter} from "vue-router";

const authService = useAuthStore();

const {
  onClose,
  selectedDay,
  doctorInfo,
  patientInfo
} = defineProps(['onClose', 'selectedDay', 'doctorInfo', 'patientInfo']);
const onCloseEnabled = ref(true);
const submitting = ref(false);

const isFirstApp = ref(false);
const isRemote = ref(false);
const router = useRouter();

async function makeAppointment(selectedDay, s) {
  Swal.fire({
    title: SM.CONFIRM_OPERATION,
    html: `¿Desea registrar la siguiente cita? <br>
    <div class="flex flex-col text-start mt-5">
    <span><span class="font-bold">PACIENTE:</span> ${patientInfo.name} ${patientInfo.paternal_surname} ${patientInfo.maternal_surname}</span>
    <span><span class="font-bold">DNI:</span> ${patientInfo.dni}</span>
    <span><span class="font-bold">DÍA:</span> ${selectedDay?.weekdayName?.toUpperCase()}</span>
    <span><span class="font-bold">FECHA:</span> ${selectedDay.dateKey} </span>
    <span><span class="font-bold">HORA:</span> ${s.start_time}</span>

    <span class="mt-2"><span class="font-bold">DOCTOR:</span> ${doctorInfo.name} ${doctorInfo.paternal_surname} ${doctorInfo.maternal_surname}</span>
</div>
    `,
    icon: 'question',
    showCancelButton: true,
    cancelButtonText: 'NO',
    confirmButtonText: 'SI',
    confirmButtonColor: '#008236',
    cancelButtonColor: '#e7000b',
  }).then(async (op) => {
    if (op.isConfirmed) {
      onCloseEnabled.value = false;
      await onSubmit(selectedDay, s);
    }
  });
}

async function onSubmit(selectedDay, s) {
  submitting.value = true;
  try {
    const payload = {
      date: selectedDay.dateKey,
      time: s.start_time,
      status: APPOINTMENT_STATUS.PENDING,
      is_remote: isRemote.value,
      duration: s.duration,
      is_treatment: false, //Aca va ID de tratamiento a futuro!
      patient_id: patientInfo.id,
      doctor_id: doctorInfo.id,
      created_by: authService.getUserId(),
    };
    const response = await AppointmentService.create(payload);
    Swal.fire(SM.SUCCESS_TAG, `${response.message} <br> Será redirigido a la sección de ventas para proceder con el pago.`, 'success').then((r) => {
      if (r.isConfirmed || r.dismiss || r.isDismissed) {
        router.push({name: 'sell-products'}); //TODO: Pasar como parametro de ruta el "servicio" de cita,
        //TODO para que este agregado en pendiente de pago. Maybe DNI paciente tmb. --> ID CITA TAMBIEN?
      }
    });
  } catch (err) {
    console.log(err);
    if (err?.status === 'UNAVAILABLE_DAY') {
      Swal.fire(EM.ERROR_TAG, err.message, 'warning');
    } else if (err?.status === 'TIME_OUTSIDE_WORK_HOURS') {
      Swal.fire(EM.ERROR_TAG, err.message, 'warning');
    } else if (err?.status === 'TIME_WITHIN_BREAK') {
      Swal.fire(EM.ERROR_TAG, err.message, 'warning');
    } else if (err?.status === 'DUPLICATED_APPOINTMENT_CURRENT_PATIENT') {
      Swal.fire(EM.ERROR_TAG, err.message, 'warning');
    } else if (err?.status === 'DUPLICATED_APPOINTMENT_DIFF_PATIENT') {
      Swal.fire(EM.ERROR_TAG, err.message, 'warning');
    } else {
      Swal.fire(EM.ERROR_TAG, EM.APPOINTMENT_CREATION_ERROR, 'error').then((r) => reloadOnDismiss(r));
    }
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <div id="slots-modal"
       class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm"
       tabindex="-1">
    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-3xl">
      <!-- Modal Header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Citas Disponibles
        </h3>
        <button
            :disabled="!onCloseEnabled"
            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
            type="button" @click="onClose">
          <svg aria-hidden="true" class="w-3 h-3" fill="none" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
            <path d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" stroke="currentColor" stroke-linecap="round"
                  stroke-linejoin="round" stroke-width="2"/>
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="pb-4 mt-4">
        <div class="container">
          <div class="flex items-center mb-4 me-5">
            <input id="default-checkbox" v-model="isRemote" :disabled="!onCloseEnabled"
                   class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-green-500  focus:ring-2 ms-5"
                   type="checkbox">
            <label class="ms-2 text-sm font-medium text-gray-900 me-5" for="default-checkbox">¿Teleconsulta?</label>
          </div>
        </div>
        <!-- Table Header -->
        <table v-if="!submitting" class="w-full text-sm text-left rtl:text-right text-gray-500">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50">
          <tr>
            <th class="px-6 py-3" scope="col">HORA</th>
            <th class="px-6 py-3 text-center" scope="col">DURACIÓN APROX.</th>
            <th class="px-6 py-3 text-center" scope="col">CONFLICTIVO</th>
            <th class="px-6 py-3" scope="col">HERRAMIENTAS</th>
          </tr>
          </thead>
        </table>

        <!-- Scrollable Body -->
        <div v-if="!submitting" class="max-h-64 overflow-y-auto">
          <table class="w-full text-sm text-left rtl:text-right text-gray-500">
            <tbody>
            <tr
                v-for="s in selectedDay.slots"
                :key="s.start_time"
                class="bg-white border-b border-gray-200 hover:bg-green-100"
            >
              <td class="px-6 py-2 font-medium text-gray-900">
                {{ s.start_time }}
              </td>
              <td class="px-6 py-2 font-medium text-gray-900 text-center">
                {{ `${s.duration} Min.` }}
              </td>
              <td :class="{'text-red-700 font-bold': s.is_conflicting_with_patient, 'text-green-700 font-bold': !s.is_conflicting_with_patient}"
                  class="px-6 py-2 text-center">
                {{ s.is_conflicting_with_patient ? 'SI' : 'NO' }}
              </td>
              <td class="px-6 py-2">
                <button
                    :disabled="!onCloseEnabled || s.is_conflicting_with_patient"
                    class="px-3 py-2 text-xs font-medium text-center text-white bg-green-700 rounded-lg hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 disabled:bg-gray-500 disabled:cursor-not-allowed"
                    type="button"
                    @click="makeAppointment(selectedDay,s)">
                  Reservar
                </button>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <div v-if="submitting" class="container mt-5 mb-5 flex flex-col items-center">
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
          <h1 class="mt-5 text-2xl font-light">Reservando cita...</h1>
        </div>
      </div>
    </div>
  </div>
</template>