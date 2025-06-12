<script setup>
import {onMounted, ref} from "vue";
import {APPOINTMENTS_REPORT_TYPES as ART, SALES_REPORT_TYPES as SRT} from "@/utils/constants.js";
import dayjs from "dayjs";

const submitting = ref(false);
const salesReportType = ref();
const salesReportDate = ref();
const appointmentsReportType = ref();
const appointmentsReportDate = ref();

function resetReportDate(control) {
  if (control === 'sales') {
    salesReportDate.value = dayjs().format('YYYY-MM-DD');
  }
  if (control === 'appointments') {
    appointmentsReportDate.value = dayjs().format('YYYY-MM-DD');
  }
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - MÓDULO DE REPORTES'
  salesReportType.value = SRT[0].value;
  appointmentsReportType.value = ART[0].value;
  salesReportDate.value = dayjs().format('YYYY-MM-DD');
  appointmentsReportDate.value = dayjs().format('YYYY-MM-DD');
});
</script>

<template>
  <main>
    <div class="flex flex-col items-center">
      <div class="p-6 m-5 bg-white border border-gray-200 rounded-lg shadow-sm">
        <h5 v-if="!submitting" class="mb-2 text-2xl font-bold text-gray-900 text-center">Área de Reportes</h5>
        <div v-if="!submitting"
             class="flex items-center p-4 mb-4 text-sm text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50"
             role="alert">
          <svg aria-hidden="true" class="shrink-0 inline w-4 h-4 me-3" fill="currentColor"
               viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
          </svg>
          <span class="sr-only">Info</span>
          <div>
            <span class="font-medium">Recuerde!</span> En el caso de reportes por mes o año el día seleccionado no es
            relevante.
          </div>
        </div>
        <div v-if="!submitting" class="grid grid-cols-2 space-y-4 space-x-4">
          <div class="col">
            <h1 class="text-xl font-light">Reporte de Citas</h1>
            <div class="mt-3 mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">
                Tipo de Reporte
              </label>
              <select id="salesReportType" v-model="appointmentsReportType" :disabled="submitting"
                      class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                      name="salesReportType"
                      @change="resetReportDate('appointments')"
              >
                <option v-for="e in ART" :key="e.value" :value="e.value">{{ e.label }}</option>
              </select>
            </div>
            <div class="mb-3">
              <label
                  class="block mb-1 text-sm font-medium text-gray-900 ">
                {{
                  appointmentsReportType === 'by_month'
                      ? 'Seleccionar mes para reporte'
                      : 'Seleccionar año para reporte'
                }}
              </label>
              <input id="appointmentsReportDate" v-model="appointmentsReportDate" :disabled="submitting"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                     name="appointmentsReportDate"
                     type="date"/>
            </div>
            <div class="flex flex-col items-center">
              <button :disabled="submitting"
                      class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                      type="button">
                <i class="bi bi-file-ruled w-3 h-3 me-2 flex items-center justify-center"></i>
                {{ submitting ? 'Generando reporte...' : 'Generar' }}
              </button>
            </div>

          </div>
          <div class="col">
            <h1 class="text-xl font-light">Reporte de Ventas</h1>
            <div class="mt-3 mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">
                Tipo de Reporte
              </label>
              <select id="salesReportType" v-model="salesReportType" :disabled="submitting"
                      class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                      name="salesReportType"
                      @change="resetReportDate('sales')"
              >
                <option v-for="e in SRT" :key="e.value" :value="e.value">{{ e.label }}</option>
              </select>
            </div>
            <div class="mb-3">
              <label
                  class="block mb-1 text-sm font-medium text-gray-900 ">
                {{
                  salesReportType === 'by_month'
                      ? 'Seleccionar mes para reporte'
                      : 'Seleccionar año para reporte'
                }}
              </label>
              <input id="salesReportDate" v-model="salesReportDate" :disabled="submitting"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                     name="salesReportDate"
                     type="date"/>
            </div>
            <div class="flex flex-col items-center">
              <button :disabled="submitting"
                      class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                      type="button">
                <i class="bi bi-file-ruled w-3 h-3 me-2 flex items-center justify-center"></i>
                {{ submitting ? 'Generando reporte...' : 'Generar' }}
              </button>
            </div>
          </div>
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
          <h1 class="mt-5 text-2xl font-light">Generando reporte...</h1>
        </div>

      </div>
    </div>
  </main>
</template>