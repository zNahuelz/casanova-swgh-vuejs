<script setup>
import {formatAsDate} from "@/utils/helpers.js";
import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";

dayjs.extend(customParseFormat);

const {onClose, unavailabilities} = defineProps(['onClose', 'unavailabilities']);

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
          </tr>

          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
