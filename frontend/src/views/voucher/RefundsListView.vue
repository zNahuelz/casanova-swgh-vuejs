<script setup>

import {onMounted, ref} from "vue";
import {PaymentService} from "@/services/payment-service.js";
import {reloadOnDismiss, reloadPage} from "@/utils/helpers.js";
import {useRouter} from "vue-router";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";

const isLoading = ref(false);
const loadError = ref(false);
const pendingRefunds = ref([]);
const router = useRouter();

async function loadRefunds() {
  isLoading.value = true;
  try {
    pendingRefunds.value = await PaymentService.getPendingRefunds();
    isLoading.value = false;
  } catch (err) {
    isLoading.value = false;
    loadError.value = true;
  }
}

async function markCompleted(id) {
  isLoading.value = true;
  try {
    const response = await PaymentService.deleteRefund(id);
    Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then((r) => reloadOnDismiss(r));
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, err.message, 'error').then((r) => reloadOnDismiss(r));
  }
}

function showMarkCompletedDialog(e) {
  const text = `Usted está a punto de marcar como completado el siguiente recordatorio:<br>
                ${e.notes}`;

  Swal.fire({
    title: 'Confirmación de Solicitud',
    html: text,
    icon: 'question',
    showCancelButton: true,
    cancelButtonText: 'CANCELAR',
    confirmButtonText: 'CONFIRMAR',
    confirmButtonColor: '#008236',
    cancelButtonColor: '#e7000b',
  }).then((op) => {
    if (op.isConfirmed) {
      markCompleted(e.id);
    }
  });
}

function goToAppointmentDetail(id) {
  router.push({name: 'appointment-detail', params: {id}});
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - REEMBOLSOS PENDIENTES';
  loadRefunds();
});
</script>

<template>
  <main class="flex flex-col items-center pt-5 relative">
    <div class="container px-12 mx-auto">
      <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm w-full">
        <h5 v-if="!isLoading" class="mb-2 text-2xl font-bold tracking-tight text-black text-start">LISTADO DE REEMBOLSOS
          PENDIENTES</h5>

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
          <h1 class="mt-5 text-2xl font-light">Cargando reembolsos pendientes...</h1>
        </div>

        <div v-if="!isLoading && !loadError" class="container mt-5 mb-5">
          <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500">
              <thead class="text-xs text-gray-700 uppercase bg-gray-50">
              <tr>
                <th class="px-6 py-3" scope="col">
                  ID
                </th>
                <th class="px-6 py-3" scope="col">
                  DESCRIPCIÓN
                </th>
                <th class="px-6 py-3 text-center" scope="col">
                  HERRAMIENTAS
                </th>
              </tr>
              </thead>
              <tbody>
              <tr
                  v-for="r in pendingRefunds" :key="r.id" class="bg-white border-b border-gray-200 hover:bg-gray-50">
                <th class="px-6 py-2 whitespace-nowrap" scope="row">
                  {{ r.id }}
                </th>
                <td class="px-6 py-2 font-medium text-gray-900">
                  {{ r.notes }}
                </td>
                <td class="px-6 py-3 flex justify-center items-center">
                  <div class="inline-flex rounded-md shadow-xs" role="group">
                    <button
                        class="flex items-center justify-center px-4 py-2 rounded-s-lg border-s text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-green-800 focus:z-10 focus:ring-2 focus:ring-green-800 focus:text-green-500 disabled:bg-gray-200 disabled:cursor-not-allowed"
                        title="MARCAR COMPLETADO"
                        type="button"
                        @click="showMarkCompletedDialog(r)"
                    >
                      <i class="bi bi-check2-all w-4 h-4"></i>
                    </button>
                    <button
                        class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue  -700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                        title="DETALLES DE CITA" type="button"
                        @click="goToAppointmentDetail(r.appointment_id)"
                        @disabled="!r.appointment_id"
                    >
                      <i class="bi bi-three-dots w-4 h-4"></i>
                    </button>
                  </div>
                </td>
              </tr>

              </tbody>
            </table>
          </div>

        </div>

        <div v-if="loadError" class="container mt-5 mb-5 flex flex-col items-center space-y-5">
          <span><i class="bi bi-journal-check text-9xl text-green-700"></i></span>
          <h1 class="text-2xl font-light">¡Estás al día! No se encontraron recordatorios de reembolsos pendientes.</h1>
          <h1 class="text-xl font-semibold text-green-800 underline" @click="reloadPage">Recargar</h1>
        </div>
      </div>
    </div>
  </main>
</template>
