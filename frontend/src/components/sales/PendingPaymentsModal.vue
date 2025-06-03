<script setup>
import {useRouter} from "vue-router";
import {formatAsDate, formatAsTime} from "@/utils/helpers.js";

const {onClose,pendingPayments} = defineProps(['onClose','pendingPayments']);
const emit = defineEmits(['addToList']);
const router = useRouter();
</script>

<template>
  <div id="ppayments-modal" tabindex="-1"
       class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm">
    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-3xl">
      <div
          class="flex items-center justify-between p-4 md:p-5 border-b rounded-t  border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Pagos Pendientes
        </h3>
        <button type="button"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                @click="onClose">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <div class="pb-4 mb-4">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-center">
              #
            </th>
            <th scope="col" class="px-6 py-3 text-center">
              ID CITA
            </th>
            <th scope="col" class="px-6 py-3">
              FECHA CITA
            </th>
            <th scope="col" class="px-6 py-3">
              HORA CITA
            </th>
            <th scope="col" class="px-6 py-3">
              VALOR
            </th>
            <th scope="col" class="px-6 py-3 text-center">
              ACCIÓN
            </th>
          </tr>
          </thead>
          <tbody>
          <tr
              class="bg-white border-b border-gray-200 hover:bg-green-100" v-for="pp in pendingPayments" :key="pp.id">
            <th scope="row" class="px-6 py-2 whitespace-nowrap">
              {{pp.id}}
            </th>
            <td class="px-6 py-2 font-medium text-gray-900 text-center">
              {{pp.appointment_id}}
            </td>
            <td class="px-6 py-2 font-medium text-gray-900">
              {{pp.appointment.rescheduling_date ? formatAsDate(pp.appointment.rescheduling_date) : formatAsDate(pp.appointment.date)}}
            </td>
            <td class="px-6 py-2">
              {{pp.appointment.rescheduling_time ? formatAsTime(pp.appointment.rescheduling_time) : formatAsTime(pp.appointment.time)}}
            </td>
            <td class="px-6 py-2 text-green-700 font-bold">
              S./ {{pp.value}}
            </td>
            <td class="px-6 py-2 text-center">
              <button
                  class="px-3 py-2 text-xs font-medium text-center text-white bg-green-700 rounded-lg hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 disabled:bg-gray-500 disabled:cursor-not-allowed"
                  type="button" title="AÑADIR AL CARRITO"
                  @click="() => {emit('addToList',pp)}"
                  >
                <i class="bi bi-plus-lg"></i>
              </button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
