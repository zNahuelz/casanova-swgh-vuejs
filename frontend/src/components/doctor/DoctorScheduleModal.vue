<script setup>
import {WEEKDAY_NAMES} from "@/utils/constants.js";
import {useRouter} from "vue-router";

const {onClose} = defineProps(['onClose','availabilities']);
const router = useRouter();
function getWeekdayName(weekday){
  const found = WEEKDAY_NAMES.find(w => w.weekday === weekday);
  return found ? found.name : `DÍA DESCONOCIDO: ${weekday}`
}
</script>

<template>
  <div id="schedule-modal" tabindex="-1"
       class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm">
    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-3xl">
      <div
          class="flex items-center justify-between p-4 md:p-5 border-b rounded-t  border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Detalle de Horario
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
            <th scope="col" class="px-6 py-3">
              DÍA
            </th>
            <th scope="col" class="px-6 py-3">
              HORA INICIO
            </th>
            <th scope="col" class="px-6 py-3">
              HORA FIN
            </th>
            <th scope="col" class="px-6 py-3">
              INICIO DESCANSO
            </th>
            <th scope="col" class="px-6 py-3">
              FIN DESCANSO
            </th>
            <th scope="col" class="px-6 py-3 text-center">
              ESTADO
            </th>
          </tr>
          </thead>
          <tbody>
          <tr
              class="bg-white border-b border-gray-200 hover:bg-green-100" v-for="a in availabilities" :key="a.id">
            <th scope="row" class="px-6 py-2 whitespace-nowrap">
              {{getWeekdayName(a.weekday)}}
            </th>
            <td class="px-6 py-2 font-medium text-gray-900">
              {{a.start_time}}
            </td>
            <td class="px-6 py-2 font-medium text-gray-900">
              {{a.end_time}}
            </td>
            <td class="px-6 py-2">
              {{a.break_start}}
            </td>
            <td class="px-6 py-2">
              {{a.break_end}}
            </td>
            <td class="px-6 py-2" :class="{'text-green-900 font-bold': a.is_active, 'text-red-900 font-bold': !a.is_active}">
              {{a.is_active ? 'HABILITADO' : 'DESHABILITADO'}}
            </td>
          </tr>

          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
