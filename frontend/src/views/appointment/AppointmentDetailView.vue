<script setup>
import {onMounted, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import {useAuthStore} from "@/stores/auth.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {AppointmentService} from "@/services/appointment-service.js";
import {formatAsDate, formatAsTime} from "@/utils/helpers.js";
import {PaymentService} from "@/services/payment-service.js";

const router = useRouter();
const route = useRoute();
const authService = useAuthStore();
const appointment = ref({});
const isLoading = ref(false);
const loadError = ref(false);
const paymentInfo = ref({});
const paymentInfoLoaded = ref(false);

async function loadAppointment(id) {
  isLoading.value = true;
  try {
    appointment.value = await AppointmentService.getById(id);
    loadError.value = false;
    isLoading.value = false;
    console.log(appointment.value);
    await loadPaymentInfo(appointment.value.id);
  } catch (err) {
    loadError.value = true;
    Swal.fire(EM.ERROR_TAG, EM.APPOINTMENT_NOT_FOUND, 'error').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        router.back();
      }
    });
  }
}

async function loadPaymentInfo(id) {
  isLoading.value = true;
  loadError.value = false;
  try {
    paymentInfo.value = await PaymentService.getByAppointmentId(id);
    loadError.value = false;
    isLoading.value = false;
    paymentInfoLoaded.value = true;
    console.log(paymentInfo.value);
  } catch (err) {
    isLoading.value = false;
    paymentInfoLoaded.value = false;
  }
}

function cancelAppointment() {
  Swal.fire({
    title: SM.CONFIRM_OPERATION,
    html: `¿Desea cancelar la siguiente cita?
    <br>
    <div class="flex flex-col text-start mt-5">
    <span><span class="font-bold">PACIENTE:</span> ${appointment.value.patient?.name} ${appointment.value.patient?.paternal_surname} ${appointment.value.patient?.maternal_surname}</span>
    <span><span class="font-bold">DNI:</span> ${appointment.value.patient?.dni}</span>
    <span><span class="font-bold">FECHA:</span> ${formatAsDate(appointment.value.date)} </span>
    <span><span class="font-bold">HORA:</span> ${formatAsTime(appointment.value.time)}</span>
    <span class="mt-2"><span class="font-bold">DOCTOR:</span> ${appointment.value.doctor?.name} ${appointment.value.doctor?.paternal_surname} ${appointment.value.doctor?.maternal_surname}</span>
    <br>
    <p class="font-semibold text-red-600 text-center">La información de la misma será eliminada y el espacio podrá ser reservado por otro paciente de continuar disponible. Además, se generará un reembolso pendiente para el paciente.</p>
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
      console.log('SIII')
    }
  });
}

function goBack() {
  router.back();
}

function goToPatientDetail(id) {
  router.push({name: 'patient-detail', params: {id}});
}

function goToDoctorDetail(id) {
  router.push({name: 'doctor-detail', params: {id}});
}

function goToReschedule(id) {
  router.push({name: 'appointment-reschedule', params: {id}});
}

function goToPayOrDetails(type) {
  if (type === 'PENDING_PAYMENT') {
    router.push({name: 'sell-products'}); //TODO: PASAR PARAMETROS DE SER NECESARIO!! (LINKEADO CON CREACION DE CITA...)
  }
  if (type === 'PAYMENT_OK') {
    console.log('VOUCHER DETAIL....!') //TODO: Ir a voucher detail.
  }
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - DETALLES DE CITA';
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
        <h5 v-if="!isLoading" class="mb-2 text-2xl font-bold tracking-tight text-black text-center">DETALLE DE CITA
          #{{ appointment.id }}</h5>
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
          <h1 class="mt-5 text-2xl font-light">Cargando información...</h1>
        </div>

        <div v-if="!isLoading" class="flex justify-end">
          <div class="inline-flex rounded-md shadow-xs" role="group">
            <button
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                type="button"
                @click="goBack">
              <i class="bi bi-arrow-return-left w-3 h-3 me-2 flex items-center justify-center"></i>
              Atras
            </button>
            <button
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 border-e hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                type="button" @click="cancelAppointment()"
            >
              <i class="bi bi-journal-x w-3 h-3 me-2 flex items-center justify-center"></i>
              Cancelar
            </button>
            <button
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-e border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                type="button" @click="goToReschedule(appointment.id)"
            >
              <i class="bi bi-calendar2-plus w-3 h-3 me-2 flex items-center justify-center"></i>
              Reprogramar
            </button>
          </div>
        </div>

        <div v-if="!isLoading" class="grid grid-cols-3 gap-5 mt-3">
          <div>
            <h5 class="text-xl font-light mb-3">Información de Cita</h5>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Fecha</label>
              <input :value="formatAsDate(appointment.date)"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900">Hora</label>
              <input :value="formatAsTime(appointment.time)"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900">Estado</label>
              <input
                  :class="{'text-yellow-500 font-bold': appointment.status === 'PENDIENTE', 'text-green-600 font-bold': appointment.status === 'ATENDIDO', 'text-rose-600 font-bold': appointment.status === 'REPROGRAMADO'}"
                  :value="appointment.status"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                  disabled
                  type="text"/>
            </div>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900">Tipo de Atención</label>
              <input
                  :class="{'text-blue-600 font-bold': !appointment.is_remote, 'text-purple-600 font-bold': appointment.is_remote}"
                  :value="appointment.is_remote ? 'VIRTUAL' : 'PRESENCIAL'"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                  disabled
                  type="text"/>
            </div>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900">Tipo de Reserva</label>
              <input
                  :class="{'text-violet-900 font-bold': appointment.is_treatment, 'text-sky-600 font-bold': !appointment.is_treatment}"
                  :value="appointment.is_treatment ? 'TRATAMIENTO' : 'CONSULTA MÉDICA'"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                  disabled
                  type="text"/>
            </div>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Reprogramada</label>
              <input
                  :class="{'text-red-600 font-bold': appointment.rescheduling_date !== null, 'text-green-600 font-bold': appointment.rescheduling_date === null}"
                  :value="appointment.rescheduling_date ? 'SI' : 'NO'"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                  disabled
                  type="text"/>
            </div>
            <div v-if="appointment.rescheduling_date" class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Fecha de Reprogramación</label>
              <input
                  :value="appointment.rescheduling_date ? formatAsDate(appointment.rescheduling_date) : 'NO REPROGRAMADA ~'"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                  disabled
                  type="text"/>
            </div>
            <div v-if="appointment.rescheduling_time" class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Hora de Reprogramación</label>
              <input
                  :value="appointment.rescheduling_time ? formatAsTime(appointment.rescheduling_time) : 'NO REPROGRAMADA ~'"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                  disabled
                  type="text"/>
            </div>
          </div>

          <div>
            <h5 class="text-xl font-light mb-3">Información del Paciente</h5>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Nombre</label>
              <input :value="appointment.patient?.name"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Apellido Paterno</label>
              <input :value="appointment.patient?.paternal_surname"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Apellido Materno</label>
              <input :value="appointment.patient?.maternal_surname"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">DNI</label>
              <input :value="appointment.patient?.dni"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Teléfono</label>
              <input :value="appointment.patient?.phone"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Dirección</label>
              <input :value="appointment.patient?.address"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>
            <div class="mb-3">
              <button
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed w-full"
                  type="button"
                  @click="goToPatientDetail(appointment.patient?.id)">
                <i class="bi bi-person-arms-up w-3 h-3 me-2 flex items-center justify-center"></i>
                DETALLE DE PACIENTE
              </button>
            </div>
          </div>

          <div>
            <h5 class="text-xl font-light mb-3">Información del Doctor</h5>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Nombre</label>
              <input :value="appointment.doctor?.name"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Apellido Paterno</label>
              <input :value="appointment.doctor?.paternal_surname"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Apellido Materno</label>
              <input :value="appointment.doctor?.maternal_surname"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">DNI</label>
              <input :value="appointment.doctor?.dni"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">E-Mail</label>
              <input :value="appointment.doctor?.email"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Teléfono</label>
              <input :value="appointment.doctor?.phone"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>
            <div class="mb-3">
              <button
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed w-full"
                  type="button"
                  @click="goToDoctorDetail(appointment.doctor?.id)">
                <i class="bi bi-journal-medical w-3 h-3 me-2 flex items-center justify-center"></i>
                DETALLE DE DOCTOR
              </button>
            </div>
          </div>

          <div v-if="paymentInfoLoaded" class="col-span-1">
            <h5 class="text-xl font-light mb-3">Información de Pago</h5>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Estado</label>
              <input
                  :class="{'text-green-700 font-bold': paymentInfo.type !== 'PENDING_PAYMENT', 'text-red-800 font-bold': paymentInfo.type === 'PENDING_PAYMENT'}"
                  :value="paymentInfo.type === 'PENDING_PAYMENT' ? 'PAGO PENDIENTE' : 'PAGO REALIZADO'"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                  disabled
                  type="text"/>
            </div>

            <div v-if="paymentInfo.type === 'PENDING_PAYMENT'" class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Notas</label>
              <textarea :value="paymentInfo.payment?.notes"

                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                        disabled
                        rows="2"/>
            </div>
          </div>

          <div v-if="paymentInfoLoaded" class="col-span-1">
            <div class="mb-3 mt-5 pt-5">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">Valor Pago</label>
              <input
                  :value="paymentInfo.type === 'PENDING_PAYMENT' ? 'S./ '+paymentInfo.payment.value : paymentInfo.payment?.subtotal"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                  disabled
                  type="text"/>
            </div>

            <div class="mb-3 mt-5 pt-5">
              <button
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed w-full"
                  type="button"
                  @click="goToPayOrDetails(paymentInfo.type)">
                <i class="bi bi-receipt w-3 h-3 me-2 flex items-center justify-center"></i>
                {{ paymentInfo.type === 'PENDING_PAYMENT' ? 'REALIZAR PAGO' : 'DETALLE DE VOUCHER' }}
              </button>
            </div>
          </div>
        </div>
        <div v-if="!isLoading && !paymentInfoLoaded" class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50"
             role="alert">
          <span class="font-medium">INFORMACIÓN</span> No se encontro información sobre el pago de la cita de ID:
          {{ appointment.id }} Intente nuevamente o comuniquese con administración.
        </div>


      </div>
    </div>
  </main>
</template>