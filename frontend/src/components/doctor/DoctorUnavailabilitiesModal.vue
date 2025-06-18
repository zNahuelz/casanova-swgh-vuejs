<script setup>
import {formatAsDate, reloadOnDismiss} from "@/utils/helpers.js";
import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {DoctorService} from "@/services/doctor-service.js";
import {useAuthStore} from "@/stores/auth.js";

dayjs.extend(customParseFormat);

const {onClose, unavailabilities} = defineProps(['onClose', 'unavailabilities']);
const authService = useAuthStore();

function isCurrentlyActive(start, end) {
  const today = dayjs();
  const startDate = dayjs(start, 'DD/MM/YYYY');
  const endDate = dayjs(end, 'DD/MM/YYYY');

  if (today.isAfter(endDate)) {
    return "PASADO";
  } else if (today.isBefore(startDate)) {
    return "PRÓXIMAMENTE";
  } else {
    return "VIGENTE";
  }
}

function askForRemoval(unav) {
  Swal.fire({
    title: 'Confirmar operación',
    html: `Está a punto de deshabilitar la siguiente indisponibilidad.
    <br>
    <span class="font-bold">ID: </span><span>${unav.id}</span> <br>
    <span class="font-bold">FECHA DE INICIO: </span><span>${formatAsDate(unav.start_datetime)}</span> <br>
    <span class="font-bold">FECHA DE FIN: </span><span>${formatAsDate(unav.end_datetime)}</span> <br>
    <span class="font-bold">RAZÓN: </span><span>${unav.reason}</span> <br>
    El doctor volverá a estar disponible para reserva de citas.`,
    icon: 'question',
    showCancelButton: true,
    cancelButtonText: 'NO',
    confirmButtonText: 'SI',
    confirmButtonColor: '#008236',
    cancelButtonColor: '#e7000b',
  }).then(async (op) => {
    if (op.isConfirmed) {
      try {
        const response = await DoctorService.removeUnavailability(unav.id);
        Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then((r) => reloadOnDismiss(r));
      } catch (err) {
        Swal.fire(EM.ERROR_TAG, EM.SERVER_ERROR, 'error').then((r) => reloadOnDismiss(r));
      }
    }
  });
}
</script>

<template>
  <div id="unavailabilities-modal"
       class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm"
       tabindex="-1">
    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-3xl">
      <div
          class="flex items-center justify-between p-4 md:p-5 border-b rounded-t  border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Detalle de Indisponibilidades
        </h3>
        <button
            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
            type="button"
            @click="onClose">
          <svg aria-hidden="true" class="w-3 h-3" fill="none" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
            <path d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" stroke="currentColor" stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"/>
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <div class="pb-4 mt-4">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50">
          <tr>
            <th class="px-6 py-3" scope="col">
              FECHA INICIO
            </th>
            <th class="px-6 py-3" scope="col">
              FECHA FIN
            </th>
            <th class="px-6 py-3" scope="col">
              RAZÓN
            </th>
            <th class="px-6 py-3" scope="col">
              ESTADO
            </th>
            <th class="px-6 py-3" scope="col" v-if="authService.getTokenDetails().role === 'ADMINISTRADOR' || authService.getTokenDetails().role === 'SECRETARIA'">
              HERRAMIENTAS
            </th>
          </tr>
          </thead>
          <tbody>
          <tr
              v-for="u in unavailabilities" :key="u.id" class="bg-white border-b border-gray-200 hover:bg-green-100">
            <td class="px-6 py-2 font-medium text-gray-900">
              {{ formatAsDate(u.start_datetime) }}
            </td>
            <td class="px-6 py-2 font-medium text-gray-900">
              {{ formatAsDate(u.end_datetime) }}
            </td>
            <td class="px-6 py-2">
              {{ u.reason }}
            </td>
            <td :class="{'text-green-800': isCurrentlyActive(formatAsDate(u.start_datetime), formatAsDate(u.end_datetime)) === 'VIGENTE',
             'text-red-800': isCurrentlyActive(formatAsDate(u.start_datetime), formatAsDate(u.end_datetime)) === 'PASADO',
              'text-purple-900': isCurrentlyActive(formatAsDate(u.start_datetime), formatAsDate(u.end_datetime)) === 'PRÓXIMAMENTE'}"
                class="px-6 py-2 font-bold">
              {{
                isCurrentlyActive(formatAsDate(u.start_datetime), formatAsDate(u.end_datetime))
              }}
            </td>
            <td class="px-6 py-2" v-if="authService.getTokenDetails().role === 'ADMINISTRADOR' || authService.getTokenDetails().role === 'SECRETARIA'">
              <button
                  :disabled="isCurrentlyActive(formatAsDate(u.start_datetime), formatAsDate(u.end_datetime)) === 'PASADO'"
                  class="px-3 py-2 text-xs font-medium text-center text-white bg-green-700 rounded-lg hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 disabled:bg-gray-500 disabled:cursor-not-allowed"
                  type="button"
                  @click="askForRemoval(u)">
                Deshabilitar
              </button>
            </td>
          </tr>

          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
