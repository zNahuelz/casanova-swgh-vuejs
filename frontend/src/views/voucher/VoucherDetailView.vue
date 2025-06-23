<script setup>
import {onMounted, ref} from "vue";
import {VoucherService} from "@/services/voucher-service.js";
import {useRoute, useRouter} from "vue-router";
import {ERROR_MESSAGES as EM} from "@/utils/constants.js";
import Swal from "sweetalert2";

const isLoading = ref(false);
const loadError = ref(false);
const voucher = ref({});
const route = useRoute();
const router = useRouter();

async function loadVoucher(id) {
  try {
    isLoading.value = true;
    voucher.value = await VoucherService.getById(id);
    isLoading.value = false;
  } catch (err) {
    loadError.value = true;
    Swal.fire(EM.ERROR_TAG, EM.VOUCHER_DETAIL_NOT_FOUND, 'error').then((r) => {
      if (r.isConfirmed || r.dismiss || r.isDismissed) {
        router.back();
      }
    });
  }
}

function goToPatientDetail(id) {
  router.push({name: 'patient-detail', params: {id}});
}

function goBack() {
  router.back();
}

function goToPdf() {
  router.push({name: 'voucher-viewer', params: {id: voucher.id}});
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - DETALLE DE VOUCHER';
  const id = route.params.id;
  if (isNaN(id)) {
    router.back();
  }
  loadVoucher(id);
});
</script>

<template>
  <main class="flex flex-col items-center pt-5 relative">
    <div class="container px-12 mx-auto">
      <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm w-full">
        <h5 v-if="!isLoading" class="mb-2 text-2xl font-bold tracking-tight text-black text-center">DETALLE DE VOUCHER
          #{{ voucher.id }}</h5>
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
                @click="goBack"
            >
              <i class="bi bi-arrow-return-left w-3 h-3 me-2 flex items-center justify-center"></i>
              Atras
            </button>
            <button
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-e border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                type="button"
                @click="goToPdf"
            >
              <i class="bi bi-file-earmark-pdf-fill w-3 h-3 me-2 flex items-center justify-center"></i>
              Ver PDF
            </button>
          </div>
        </div>

        <div v-if="!isLoading" class="grid grid-cols-3 gap-5 mt-3">
          <div>
            <h5 class="text-xl font-light mb-3">Información de Comprobante de Pago</h5>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">SERIE-CORRELATIVO</label>
              <input :value="voucher.set + '-' + voucher.correlative"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>

            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">SUBTOTAL (S./)</label>
              <input :value="voucher.subtotal"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>

            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">IGV (S./)</label>
              <input :value="voucher.igv"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>

            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">TOTAL (S./)</label>
              <input :value="voucher.total"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>

            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">CAMBIO (S./)</label>
              <input :value="voucher.change === 0 || voucher.change === '0' ? '---' : voucher.change "
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>

            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">MÉTODO DE PAGO</label>
              <input :value="voucher.payment_type?.name"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>
          </div>

          <div>
            <h5 class="text-xl font-light mb-3">Información de Comprador</h5>
            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">NOMBRES</label>
              <input :value="voucher.patient?.name"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>

            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">APELLIDOS</label>
              <input :value="voucher.patient?.paternal_surname + ' ' + voucher.patient?.maternal_surname"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>

            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">DNI</label>
              <input :value="voucher.patient?.dni === '00000000' ? '-----' : voucher.patient?.dni"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>

            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">TELÉFONO</label>
              <input :value="voucher.patient?.phone === '000000000' ? '-----' : voucher.patient?.phone"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>

            <div class="mb-3">
              <label class="block mb-1 text-sm font-medium text-gray-900 ">DIRECCIÓN</label>
              <input :value="voucher.patient?.address"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                     disabled
                     type="text"/>
            </div>

            <div class="mb-3 mt-5 pt-4">
              <button
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed w-full"
                  type="button"
                  @click="goToPatientDetail(voucher?.patient.id)">
                <i class="bi bi-person-arms-up w-3 h-3 me-2 flex items-center justify-center"></i>
                DETALLE DE PACIENTE
              </button>
            </div>
          </div>

          <div>
            <h5 class="text-xl font-light mb-3">Contenido</h5>
            <div class="max-h-[400px] overflow-auto">
              <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                  <thead class="text-xs text-gray-700 uppercase bg-gray-50 ">
                  <tr>
                    <th class="px-6 py-3" scope="col">
                      NOMBRE
                    </th>
                    <th class="px-6 py-3" scope="col">
                      CANTIDAD
                    </th>
                    <th class="px-6 py-3" scope="col">
                      PRECIO UNITARIO (S./)
                    </th>
                    <th class="px-6 py-3" scope="col">
                      SUBTOTAL (S./)
                    </th>
                    <th class="px-6 py-3" scope="col">
                      PRESENTACIÓN
                    </th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr v-for="vd in voucher.voucher_details" :key="vd.id"
                      class="bg-white border-b  border-gray-200 hover:bg-green-50">
                    <th class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap" scope="row">
                      {{
                        vd.medicine ? vd.medicine.name : vd.treatment ? vd.treatment.name : vd.appointment ? `CITA: ${vd.appointment.date}` : '----'
                      }}
                    </th>
                    <td class="px-6 py-4">
                      {{ vd.amount }}
                    </td>
                    <td class="px-6 py-4">
                      {{ vd.unit_price }}
                    </td>
                    <td class="px-6 py-4">
                      {{ vd.subtotal }}
                    </td>
                    <td class="px-6 py-4">
                      {{
                        vd.medicine ? `${vd.medicine.presentation.name} ${vd.medicine.presentation.numeric_value} ${vd.medicine.presentation.aux}` : vd.treatment ? 'SERVICIO (1)' : vd.appointment ? 'CITA (1)' : 'OTROS'
                      }}
                    </td>
                  </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>
