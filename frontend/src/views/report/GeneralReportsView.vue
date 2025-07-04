<script setup>
import {onMounted, ref} from "vue";
import {APPOINTMENTS_REPORT_TYPES as ART, ERROR_MESSAGES as EM, SALES_REPORT_TYPES as SRT} from "@/utils/constants.js";
import dayjs from "dayjs";
import {ReportService} from "@/services/report-service.js";
import Swal from "sweetalert2";
import {reloadOnDismiss, reloadPage} from "@/utils/helpers.js";
import {Bar, Pie} from 'vue-chartjs';
import {ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip} from 'chart.js';
import {useRouter} from "vue-router";

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const submitting = ref(false);
const salesReportType = ref(SRT[0].value);
const salesReportDate = ref(dayjs().format('YYYY-MM-DD'));
const appointmentsReportType = ref(ART[0].value);
const appointmentsReportDate = ref(dayjs().format('YYYY-MM-DD'));

const appointmentsReport = ref({});
const salesReport = ref({});
const reportText = ref('');

const chartOptions = {responsive: true};
const attendedPendingChart = ref(null);
const appointmentTypeChart = ref(null);
const appointmentStatusChart = ref(null);
const showAppointmentsReport = ref(false);

const voucherTypeChart = ref(null);
const paymentTypeChart = ref(null);
const highestLowestChart = ref(null);
const showSalesReport = ref(false);

const router = useRouter();
dayjs.locale('es');

function createPieConfig(labels, data, backgroundColors, label = '') {
  return {
    labels,
    datasets: [
      {label, data, backgroundColor: backgroundColors}
    ]
  };
}

function createBarConfig(labels, data, backgroundColors, label = '') {
  return {
    labels,
    datasets: [
      {label, data, backgroundColor: backgroundColors}
    ]
  };
}

function resetReportDate(type) {
  const today = dayjs().format('YYYY-MM-DD');
  if (type === 'sales') salesReportDate.value = today;
  if (type === 'appointments') appointmentsReportDate.value = today;
}

async function loadAppointmentsReport() {
  submitting.value = true;
  try {
    const date = dayjs(appointmentsReportDate.value).format('YYYY-MM-DD');
    const report = await ReportService.getAppointmentsReport(appointmentsReportType.value, date);
    appointmentsReport.value = report;

    reportText.value = appointmentsReportType.value === 'by_month'
        ? dayjs(appointmentsReportDate.value).format('MMMM [de] YYYY').toUpperCase()
        : dayjs(appointmentsReportDate.value).year().toString();

    attendedPendingChart.value = createPieConfig(
        ['Atendidas', 'Pendientes'],
        [parseInt(report.attended_appointments), parseInt(report.pending_appointments)],
        ['rgba(72,138,22,0.7)', 'rgba(255,26,26,0.7)'],
        'Citas'
    );

    appointmentTypeChart.value = createPieConfig(
        ['Presencial', 'Remoto'],
        [100 - parseFloat(report.remote_percentage), parseFloat(report.remote_percentage)],
        ['rgba(203,73,148,0.93)', 'rgb(98,201,139)'],
        '%'
    );

    appointmentStatusChart.value = createBarConfig(
        ['Reservadas', 'Canceladas', 'Reprogramadas', 'Atendidas', 'Pendientes'],
        [
          parseInt(report.total_reservations),
          parseInt(report.total_canceled),
          parseInt(report.total_rescheduled),
          parseInt(report.attended_appointments),
          parseInt(report.pending_appointments)
        ],
        ['rgba(19,41,217,0.7)', 'rgba(169,26,218,0.7)', 'rgba(7,131,54,0.7)', 'rgba(72,138,22,0.7)', 'rgba(255,26,26,0.7)'],
        'Citas'
    );

    showAppointmentsReport.value = true;
  } catch (error) {
    Swal.fire(EM.ERROR_TAG, error.message ? error.message : EM.SERVER_ERROR, 'error').then(reloadOnDismiss);
  } finally {
    submitting.value = false;
  }
}

async function loadSalesReport() {
  submitting.value = true;
  try {
    const date = dayjs(salesReportDate.value).format('YYYY-MM-DD');
    const report = await ReportService.getSalesReport(salesReportType.value, date);
    salesReport.value = report;

    reportText.value = salesReportType.value === 'by_month'
        ? dayjs(appointmentsReportDate.value).format('MMMM [de] YYYY').toUpperCase()
        : dayjs(appointmentsReportDate.value).year().toString();

    voucherTypeChart.value = createBarConfig(
        ['Boleta', 'Factura'],
        [parseInt(report.boleta), parseInt(report.factura)],
        ['rgba(20,110,22,0.93)', 'rgba(61,159,134,0.7)'],
        'Comprobante'
    );

    paymentTypeChart.value = createPieConfig(
        ['Efectivo', 'Tarjeta-Depósito', 'Yape', 'Plin'],
        [parseInt(report.paid_cash), parseInt(report.paid_card), parseInt(report.paid_yape), parseInt(report.paid_plin)],
        ['rgba(20,110,22,0.93)', 'rgba(255,223,0,0.93)', 'rgba(169,26,218,0.7)', 'rgb(98,201,139)'],
        'Uso'
    );

    highestLowestChart.value = createBarConfig(
        ['Mayor Venta', 'Menor Venta'],
        [parseFloat(report.highest_sale.total), parseFloat(report.lowest_sale.total)],
        ['rgba(19,41,217,0.7)', 'rgba(255,26,26,0.7)'],
        'Monto (S./)'
    );

    showSalesReport.value = true;
  } catch (error) {
    Swal.fire(EM.ERROR_TAG, error.message ? error.message : EM.SERVER_ERROR, 'error').then(reloadOnDismiss);
  } finally {
    submitting.value = false;
  }
}

function goToVoucherDetail(id) {
  router.push({name: 'voucher-detail', params: {id}});
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - ÁREA DE REPORTES';
});
</script>

<template>
  <main>
    <div class="flex flex-col items-center">
      <div class="p-6 m-5 bg-white border border-gray-200 rounded-lg shadow-sm">
        <h5 v-if="!submitting" class="mb-2 text-2xl font-bold text-gray-900 text-center">Área de Reportes</h5>
        <div v-if="!submitting && !showAppointmentsReport && !showSalesReport"
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
        <div v-if="!submitting && !showAppointmentsReport && !showSalesReport"
             class="grid grid-cols-2 space-y-4 space-x-4">
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
                      type="button"
                      @click="loadAppointmentsReport"
              >
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
                      type="button"
                      @click="loadSalesReport"
              >
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

        <div v-if="!submitting && showAppointmentsReport" class="container mt-3">
          <h1 class="text-xl font-semibold text-center">
            {{
              appointmentsReport.report_type === 'by_month' ? `REPORTE DE CITAS POR MES: ${reportText}` : `REPORTE DE CITAS POR AÑO: ${reportText}`
            }}</h1>
          <div class="grid grid-cols-2 space-x-5">
            <div class="col text-end">
              <h1 class="text-lg font-light">Citas reservadas: {{ appointmentsReport.total_reservations }}</h1>
              <h1 class="text-lg font-light">Citas reprogramadas: {{ appointmentsReport.total_rescheduled }}</h1>
              <h1 class="text-lg font-light">Citas canceladas: {{ appointmentsReport.total_canceled }}</h1>
              <h1 class="text-lg font-light">Citas atendidas: {{ appointmentsReport.attended_appointments }}</h1>
              <h1 class="text-lg font-light">Citas pendientes: {{ appointmentsReport.pending_appointments }}</h1>
              <h1 class="text-lg font-light">Según modalidad (Presencial)
                {{ appointmentsReport.total_reservations - appointmentsReport.total_remote }}</h1>
              <h1 class="text-lg font-light">Según modalidad (Remoto): {{ appointmentsReport.total_remote }}</h1>
              <h1 class="text-lg font-light">Doctor más popular:
                {{
                  `${appointmentsReport.most_popular_doctor?.name} ${appointmentsReport.most_popular_doctor?.paternal_surname !== '' ? appointmentsReport.most_popular_doctor?.paternal_surname : appointmentsReport.most_popular_doctor?.maternal_surname}`
                }}</h1>
            </div>
            <div class="col text-end">
              <h1 class="text-lg font-light">Reprogramaciones (%):
                {{ appointmentsReport.rescheduling_percentage }}%</h1>
              <h1 class="text-lg font-light">Cancelaciones (%): {{ appointmentsReport.canceled_percentage }}%</h1>
              <h1 class="text-lg font-light">Atendidas (%): {{ appointmentsReport.attending_percentage }}%</h1>
              <h1 class="text-lg font-light">Pendientes (%): {{ appointmentsReport.pending_percentage }}%</h1>
              <h1 class="text-lg font-light">Total (%):
                {{
                  appointmentsReport.attending_percentage + appointmentsReport.pending_percentage + appointmentsReport.canceled_percentage
                }}%</h1>
              <h1 class="text-lg font-light">Modalidad - Presencial (%):
                {{ 100 - appointmentsReport.remote_percentage }}%</h1>
              <h1 class="text-lg font-light">Modalidad - Remoto (%): {{ appointmentsReport.remote_percentage }}%</h1>
              <h1 class="text-lg font-light">Total (%):
                {{ (100 - appointmentsReport.remote_percentage) + appointmentsReport.remote_percentage }}%</h1>
            </div>

          </div>
          <div class="grid grid-cols-3">
            <div class="col max-w-md max-h-md">
              <h1 class="font-bold text-center text-md">Citas: Atendidas vs. Pendientes</h1>
              <Pie :data="attendedPendingChart" :options="chartOptions"></Pie>
            </div>
            <div class="col max-w-md max-h-md">
              <h1 class="font-bold text-center text-md">Citas: Presenciales vs. Remotas</h1>
              <Pie :data="appointmentTypeChart" :options="chartOptions"></Pie>
            </div>
            <div class="col">
              <h1 class="font-bold text-center text-md">Citas: Estado</h1>
              <Bar :data="appointmentStatusChart" :options="chartOptions"></Bar>
            </div>
          </div>
          <div class="flex flex-col items-center mt-3">
            <button
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                type="submit"
                @click="reloadPage()">
              <i class="bi bi-arrow-return-left w-3 h-3 me-2 flex items-center justify-center"></i>
              Atrás
            </button>
          </div>
        </div>

        <div v-if="!submitting && showSalesReport" class="container mt-3">
          <h1 class="text-xl font-semibold text-center">
            {{
              salesReport.report_type === 'by_month' ? `REPORTE DE VENTAS POR MES: ${reportText}` : `REPORTE DE VENTAS POR AÑO: ${reportText}`
            }}</h1>
          <div class="grid grid-cols-2 space-x-5">
            <div class="col text-end">
              <h1 class="text-lg font-light">Ventas realizadas: {{ salesReport.total_sales }}</h1>
              <h1 class="text-lg font-light">Mayor venta:
                S./{{ salesReport.highest_sale ? salesReport.highest_sale.total : '-----' }}</h1>
              <h1 class="text-lg font-light">Menor venta:
                S./{{ salesReport.lowest_sale ? salesReport.lowest_sale.total : '-----' }}</h1>
              <h1 class="text-lg font-light">Tipo de comprobante (Boleta): {{ salesReport.boleta }}</h1>
              <h1 class="text-lg font-light">Tipo de comprobante (Factura): {{ salesReport.factura }}</h1>
              <h1 class="text-lg font-light">Venta promedio (Total): {{ salesReport.average_sale }}</h1>
              <div class="flex flex-col items-end mt-2">
                <div class="inline-flex rounded-md shadow-xs" role="group">
                  <button :disabled="submitting || !salesReport.highest_sale"
                          class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                          title="VER MAYOR VENTA"
                          type="button"
                          @click="goToVoucherDetail(salesReport.highest_sale?.id)"
                  >
                    <i class="bi bi-graph-up-arrow w-3 h-3 flex items-center justify-center"></i>
                  </button>
                  <button :disabled="submitting || !salesReport.lowest_sale"
                          class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-e border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                          title="VER MENOR VENTA"
                          type="button"
                          @click="goToVoucherDetail(salesReport.lowest_sale?.id)"
                  >
                    <i class="bi bi-graph-down-arrow w-3 h-3 flex items-center justify-center"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="col text-end">
              <h1 class="text-lg font-light">Tipo de pago (Efectivo): {{ salesReport.paid_cash }}</h1>
              <h1 class="text-lg font-light">Tipo de pago (Tarjeta-Depósito): {{ salesReport.paid_card }}</h1>
              <h1 class="text-lg font-light">Tipo de pago (Yape): {{ salesReport.paid_yape }}</h1>
              <h1 class="text-lg font-light">Tipo de pago (Plin): {{ salesReport.paid_plin }}</h1>
              <h1 class="text-lg font-light">IGV promedio: {{ salesReport.average_igv }}</h1>
              <h1 class="text-lg font-light">Subtotal promedio: {{ salesReport.average_subtotal }}</h1>
              <h1 class="text-lg font-light">Cambio promedio: {{ salesReport.average_change }}</h1>
            </div>
          </div>

          <div class="grid grid-cols-3 mt-2">
            <div class="col max-w-md max-h-md">
              <h1 class="font-bold text-center text-md">Comprobante: Boleta vs. Factura</h1>
              <Bar :data="voucherTypeChart" :options="chartOptions"></Bar>
            </div>
            <div class="col max-w-md max-h-md">
              <h1 class="font-bold text-center text-md">Venta: Medio de pago</h1>
              <Pie :data="paymentTypeChart" :options="chartOptions"></Pie>
            </div>
            <div class="col">
              <h1 class="font-bold text-center text-md">Venta: Mayor vs. Menor</h1>
              <Bar :data="highestLowestChart" :options="chartOptions"></Bar>
            </div>
          </div>
          <div class="flex flex-col items-center mt-3">
            <button
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                type="submit"
                @click="reloadPage()">
              <i class="bi bi-arrow-return-left w-3 h-3 me-2 flex items-center justify-center"></i>
              Atrás
            </button>
          </div>

        </div>
      </div>
    </div>
  </main>
</template>