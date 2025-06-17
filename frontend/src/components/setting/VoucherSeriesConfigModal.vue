<script setup>
import {reloadOnDismiss} from "@/utils/helpers.js";
import {onMounted, ref} from "vue";
import {SettingService} from "@/services/setting-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import NewVoucherSeriesModal from "@/components/setting/NewVoucherSeriesModal.vue";
import EditVoucherSeriesModal from "@/components/setting/EditVoucherSeriesModal.vue";

const {onClose} = defineProps(['onClose']);
const isLoading = ref(false);
const loadError = ref(false);
const voucherSeries = ref([]);
const showNewVoucherSeries = ref(false);
const showEditVoucherSeries = ref(false);
const selectedSerie = ref({});

async function loadVoucherSeries() {
  try {
    isLoading.value = true;
    voucherSeries.value = await SettingService.loadVoucherSeries();
    if (voucherSeries.value.length <= 0) {
      loadError.value = true;
    }
  } catch (err) {
    loadError.value = true;
    Swal.fire(EM.ERROR_TAG, err.message ? err.message : 'Error en la carga de series de vouchers. Comuniquese con administración o registre alguna.', 'error').then(reloadOnDismiss);
  } finally {
    isLoading.value = false;
  }
}

function enableSerie(voucherSerie) {
  Swal.fire({
    title: 'Confirmación de Solicitud',
    html: `¿Está seguro de habilitar la siguiente serie? <br>
    SERIE: ${voucherSerie.serie} - CORRELATIVO: ${voucherSerie.next_correlative} <br>
    Los nuevos comprobantes de pago serán generados siguiendo ese patrón.`,
    icon: 'question',
    showCancelButton: true,
    cancelButtonText: 'CANCELAR',
    confirmButtonText: 'CONFIRMAR',
    confirmButtonColor: '#008236',
    cancelButtonColor: '#e7000b',
  }).then((op) => {
    if (op.isConfirmed) {
      doEnableSerie(voucherSerie.id);
    }
  });
}

async function doEnableSerie(voucherSerieId) {
  try {
    isLoading.value = true;
    const payload = {serie_id: voucherSerieId}
    const response = await SettingService.enableVoucherSeries(payload);
    Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then(reloadOnDismiss);
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, err.message ? err.message : EM.SERVER_ERROR, 'error').then(reloadOnDismiss);
  }
}

function showHelp() {
  Swal.fire(
      'Información',
      `<span class="font-light">Los comprobantes siguen el formato B### / F### - 000000##, por ejemplo: B004-00000045 o F003-00000012, donde:</span>
  <ul class="list-disc pl-5 mt-2 text-left font-light">
    <li>B o F indica el tipo de comprobante (Boleta o Factura).</li>
    <li>El número de serie (001–999) es único y debe estar habilitado por tipo.</li>
    <li>Solo puede existir una serie activa por tipo de comprobante.</li>
    <li>El correlativo se gestiona automáticamente por el sistema.</li>
    <li>Si el número ingresado ya fue utilizado, se ajustará al siguiente disponible de forma automática al registrar una compra.</li>
  </ul>
  <span class="font-bold text-red-800">No es necesario preocuparse por duplicados: el sistema garantiza la secuencia continua y única de los comprobantes.</span>`,
      'info'
  );
}

function handleNewSeriesModal() {
  showNewVoucherSeries.value = !showNewVoucherSeries.value;
}

function handleEditSerieModal(serie) {
  selectedSerie.value = serie;
  showEditVoucherSeries.value = !showEditVoucherSeries.value;
}

onMounted(() => {
  loadVoucherSeries();
});
</script>

<template>
  <div id="voucher-config-modal"
       class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm"
       tabindex="-1">
    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-5xl">
      <div
          class="flex items-center justify-between p-4 md:p-5 border-b rounded-t  border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Configurar Comprobantes de Pago
        </h3>
        <button
            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
            type="button"
            @click="onClose"
        >
          <svg aria-hidden="true" class="w-3 h-3" fill="none" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
            <path d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" stroke="currentColor" stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"/>
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <div class="p-6 space-y-6">
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
          <h1 class="mt-5 text-2xl font-light">Cargando series y correlativos...</h1>
        </div>

        <div v-if="!isLoading && !loadError" class="container">
          <div class="grid grid-cols-2 gap-3 max-h-[300px] overflow-y-scroll">
            <div class="col">
              <h1 class="text-lg font-light">Series: {{ voucherSeries[0]?.name }}</h1>

              <table class="min-w-full bg-white border border-gray-200 rounded-lg mt-3">
                <thead class="bg-gray-100">
                <tr>
                  <th class="px-4 py-2 border-b text-left text-sm font-medium text-gray-700">ID</th>
                  <th class="px-4 py-2 border-b text-left text-sm font-medium text-gray-700">SERIE</th>
                  <th class="px-4 py-2 border-b text-left text-sm font-medium text-gray-700 w-20">PROX. CORRELATIVO</th>
                  <th class="px-4 py-2 border-b text-left text-sm font-medium text-gray-700">ESTADO</th>
                  <th class="px-4 py-2 border-b text-left text-sm font-medium text-gray-700"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="b in voucherSeries[0]?.voucher_series" :key="b.serie" class="hover:bg-gray-50">
                  <td class="px-4 py-2 border-b text-sm text-gray-600">{{ b.id }}</td>
                  <td class="px-4 py-2 border-b text-sm text-gray-600">{{ b.serie }}</td>
                  <td class="px-4 py-2 border-b text-sm text-gray-600">{{ b.next_correlative }}</td>
                  <td class="px-4 py-2 border-b text-sm text-gray-600" :class="{'text-green-800 font-bold': b.is_active, 'text-red-800 font-bold': !b.is_active}">{{ b.is_active ? 'EN USO' : 'RESERVA' }}</td>
                  <td class="px-4 py-2 border-b text-sm text-gray-600">
                    <ul>
                      <li class="font-bold text-green-800"
                          @click="enableSerie(b)">ACTIVAR
                      </li>
                      <li class="font-bold text-blue-800"
                          @click="handleEditSerieModal(b)"
                      >EDITAR
                      </li>
                    </ul>
                  </td>
                </tr>
                </tbody>
              </table>

            </div>
            <div class="col">
              <h1 class="text-lg font-light">Series: {{ voucherSeries[1]?.name }}</h1>

              <table class="min-w-full bg-white border border-gray-200 rounded-lg mt-3">
                <thead class="bg-gray-100">
                <tr>
                  <th class="px-4 py-2 border-b text-left text-sm font-medium text-gray-700">ID</th>
                  <th class="px-4 py-2 border-b text-left text-sm font-medium text-gray-700">SERIE</th>
                  <th class="px-4 py-2 border-b text-left text-sm font-medium text-gray-700 w-20">PROX. CORRELATIVO</th>
                  <th class="px-4 py-2 border-b text-left text-sm font-medium text-gray-700">ESTADO</th>
                  <th class="px-4 py-2 border-b text-left text-sm font-medium text-gray-700"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="f in voucherSeries[1]?.voucher_series" :key="f.serie" class="hover:bg-gray-50">
                  <td class="px-4 py-2 border-b text-sm text-gray-600">{{ f.id }}</td>
                  <td class="px-4 py-2 border-b text-sm text-gray-600">{{ f.serie }}</td>
                  <td class="px-4 py-2 border-b text-sm text-gray-600">{{ f.next_correlative }}</td>
                  <td class="px-4 py-2 border-b text-sm text-gray-600" :class="{'text-green-800 font-bold': f.is_active, 'text-red-800 font-bold': !f.is_active}">{{ f.is_active ? 'EN USO' : 'RESERVA' }}</td>
                  <td class="px-4 py-2 border-b text-sm text-gray-600">
                    <ul>
                      <li class="font-bold text-green-800"
                          @click="enableSerie(f)">ACTIVAR
                      </li>
                      <li class="font-bold text-blue-800"
                          @click="handleEditSerieModal(f)"
                      >EDITAR
                      </li>
                    </ul>
                  </td>
                </tr>
                </tbody>
              </table>

            </div>
          </div>
        </div>

        <div class="flex flex-col items-center">
          <div class="inline-flex rounded-md shadow-xs" role="group">
            <button
                :disabled="isLoading"
                class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-s border-t border-b border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                title="NUEVA SERIE"
                type="button"
                @click="handleNewSeriesModal">
              <i class="bi bi-plus-circle w-4 h-4"></i>
            </button>
            <button
                :disabled="isLoading"
                class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-900 bg-white  border rounded-e-lg border-gray-200 hover:bg-gray-100 hover:text-yellow-700 focus:z-10 focus:ring-2 focus:ring-yellow-700 focus:text-yellow-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                title="AYUDA"
                type="button"
                @click="showHelp">
              <i class="bi bi-info-circle w-4 h-4"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    <NewVoucherSeriesModal v-if="showNewVoucherSeries" :onClose="handleNewSeriesModal"></NewVoucherSeriesModal>
    <EditVoucherSeriesModal v-if="showEditVoucherSeries"
                            :onClose="() => {showEditVoucherSeries = false; }"
                            :serie="selectedSerie"></EditVoucherSeriesModal>
  </div>
</template>