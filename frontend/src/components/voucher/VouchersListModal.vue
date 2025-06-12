<script setup>
import {useRouter} from "vue-router";
import {formatAsDate} from "@/utils/helpers.js";

const {
  onClose,
  vouchers,
} = defineProps(['onClose', 'vouchers']);

const router = useRouter();

function goToDetails(id) {
  router.push({name: 'voucher-detail', params: {id}});
}
</script>

<template>
  <div id="vouchers-list-modal"
       class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm"
       tabindex="-1">
    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-3xl">
      <!-- Modal Header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Historial de Compras
        </h3>
        <button
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
        <!-- Table Header -->
        <table class="w-full text-sm text-left rtl:text-right text-gray-500">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50">
          <tr>
            <th class="px-6 py-3" scope="col">ID</th>
            <th class="px-6 py-3" scope="col">FECHA</th>
            <th class="px-6 py-3" scope="col">MONTO COMPRA</th>
            <th class="px-6 py-3" scope="col">ESTADO</th>
            <th class="px-6 py-3" scope="col">SERIE-CORRELATIVO</th>
            <th class="px-6 py-3" scope="col">HERRAMIENTAS</th>
          </tr>
          </thead>
        </table>

        <!-- Scrollable Body -->
        <div class="max-h-60 overflow-y-auto">
          <table class="w-full text-sm text-left rtl:text-right text-gray-500">
            <tbody>
            <tr
                v-for="v in vouchers"
                :key="v.id"
                class="bg-white border-b border-gray-200 hover:bg-green-100"
            >
              <td class="px-6 py-2 font-medium text-gray-900">
                {{ v.id }}
              </td>
              <td class="px-6 py-2 font-medium text-gray-900">
                {{ formatAsDate(v.created_at) }}
              </td>
              <td class="px-6 py-2 font-medium text-gray-900">
                {{ 'S./ '+ v.total }}
              </td>
              <td class="px-6 py-2">
                {{ v.paid ? 'PAGADO' : 'PAGO PENDIENTE' }}
              </td>
              <td class="px-6 py-2">
                {{ `${v.set}-${v.correlative}` }}
              </td>
              <td class="px-6 py-2">
                <button
                    class="px-3 py-2 text-xs font-medium text-center text-white bg-green-700 rounded-lg hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 disabled:bg-gray-500 disabled:cursor-not-allowed"
                    type="button"
                    @click="goToDetails(v.id)">
                  Detalles
                </button>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>