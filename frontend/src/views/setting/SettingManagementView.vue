<script setup>
import {SETTING_SEARCH_MODES as SSM} from "@/utils/constants.js";
import {formatAsDatetime, reloadPage} from "@/utils/helpers.js";
import {computed, onMounted, ref} from "vue";
import {Field, Form} from "vee-validate";
import * as yup from "yup";
import {useAuthStore} from "@/stores/auth.js";
import {SettingService} from "@/services/setting-service.js";
import Swal from "sweetalert2";
import {useRouter} from "vue-router";
import UpdateIgvModal from "@/components/setting/UpdateIgvModal.vue";
import UpdateAppointmentPriceModal from "@/components/setting/UpdateAppointmentPriceModal.vue";

const searchMode = ref('id');
const settings = ref([]);
const isLoading = ref(false);
const loadError = ref(false);
const authService = useAuthStore();
const router = useRouter();

const showEditIgv = ref(false);
const igvObject = ref({});

const showEditAppPrice = ref(false);
const appObject = ref({});

const dynamicSchema = computed(() => {
  let keywordValidation = yup.string().required();

  if (searchMode.value === 'id') {
    keywordValidation = keywordValidation.matches(/^[0-9]+$/, 'El ID debe ser numérico.')
        .min(1, 'El ID debe tener al menos una cifra.');
  } else if (searchMode.value === 'key') {
    keywordValidation = keywordValidation.min(2, 'La clave debe tener al menos 2 carácteres');
  } else if (searchMode.value === 'value') {
    keywordValidation = keywordValidation.min(1, 'El valor debe tener al menos 2 carácteres.');
  }

  return yup.object({
    searchMode: yup.string().required(),
    keyword: keywordValidation,
  });
});

async function loadSettings(filter = {}) {
  try {
    isLoading.value = true;
    settings.value = await SettingService.get(filter);
    if (settings.value.length <= 0) {
      loadError.value = true;
    }
  } catch (err) {
    loadError.value = true;
  } finally {
    isLoading.value = false;
  }
}

function onSubmit(values) {
  const keyword = values.keyword;
  let filter = {}
  if (values.searchMode === 'id') {
    filter = {id: keyword}
  }
  if (values.searchMode === 'key') {
    filter = {key: keyword}
  }
  if (values.searchMode === 'value') {
    filter = {value: keyword}
  }
  if (values.searchMode === 'description') {
    filter = {description: keyword}
  }
  loadSettings(filter);
}

function showWarning() {
  Swal.fire({
    title: '⚠️ ATENCIÓN ⚠️',
    html: `Estás accediendo a la configuración del sistema.
           Modificaciones incorrectas pueden afectar su funcionamiento. <br> <strong>Procede con precaución.</strong>`,
    icon: 'warning',
    showDenyButton: true,
    denyButtonText: 'VOLVER',
    confirmButtonText: 'CONTINUAR',
    confirmButtonColor: '#008236',
    denyButtonColor: '#e7000b',
  }).then((op) => {
    if (op.isDenied) {
      router.back();
    }
  });
}

function handleEditIgvModal() {
  showEditIgv.value = !showEditIgv.value;
}

function handleEditAppCostModal(){
  showEditAppPrice.value = !showEditAppPrice.value;
}

function editVar(setting) {
  if (setting.key === 'VALOR_IGV') {
    igvObject.value = setting;
    handleEditIgvModal();
  }
  if(setting.key === 'COSTO_CITA_REGULAR'){
    appObject.value = setting;
    handleEditAppCostModal();
  }
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - CONFIGURACIÓN DEL SISTEMA';
  loadSettings()//.then(() => showWarning());
});
</script>

<template>
  <main class="flex flex-col items-center pt-5 relative">
    <div class="container px-12 mx-auto">
      <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm w-full">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-black text-start">CONFIGURACIÓN DEL SISTEMA</h5>

        <div class="container mt-5 mb-3">
          <Form v-slot="{ validate }" :validation-schema="dynamicSchema" @submit="onSubmit">
            <div class="flex items-center justify-between w-full">
              <button
                  v-if="authService.getTokenDetails().role === 'ADMINISTRADOR'"
                  :disabled="isLoading"
                  class="p-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-500"
                  type="button"
              >
                <i class="bi bi-plus"></i> Nueva
              </button>

              <div class="flex items-center">
                <Field id="searchMode" v-model="searchMode" as="select"
                       class="shrink-0 z-10 inline-flex w-45 items-center py-2.5 px-4 text-sm font-medium text-gray-900 bg-gray-100 border border-gray-300 rounded-s-lg hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100"
                       name="searchMode"
                       @change="validate">
                  <option v-for="sm in SSM" :key="sm.value" :value="sm.value">{{ sm.label }}</option>
                </Field>

                <div class="relative w-70">
                  <Field id="keyword" :type="searchMode === 'id' ? 'number' : 'text'"
                         class="block p-2.5 w-full z-20 text-sm text-gray-900 bg-gray-50 border-l-0 border border-gray-300 focus:ring-green-500 focus:border-green-500"
                         name="keyword"
                         placeholder="Buscar..."
                         @input="validate"/>
                  <button
                      :disabled="isLoading"
                      class="absolute top-0 right-0 p-2.5 h-full text-sm font-medium text-white bg-green-600 rounded-e-lg hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-500"
                      type="submit"
                  >
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 20 20"
                         xmlns="http://www.w3.org/2000/svg">
                      <path d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" stroke="currentColor"
                            stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </Form>
        </div>

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
          <h1 class="mt-5 text-2xl font-light">Cargando valores de configuración del sistema...</h1>
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
                  CLAVE
                </th>
                <th class="px-6 py-3" scope="col">
                  VALOR
                </th>
                <th class="px-6 py-3" scope="col">
                  DESCRIPCIÓN
                </th>
                <th class="px-6 py-3" scope="col">
                  FECHA DE CREACIÓN
                </th>
                <th class="px-6 py-3" scope="col">
                  ULT. MODIFICACIÓN
                </th>
                <th class="px-6 py-3 text-center" scope="col">
                  HERRAMIENTAS
                </th>
              </tr>
              </thead>
              <tbody>
              <tr
                  v-for="s in settings" :key="s.id" class="bg-white border-b border-gray-200 hover:bg-gray-50">
                <th class="px-6 py-2 whitespace-nowrap" scope="row">
                  {{ s.id }}
                </th>
                <td class="px-6 py-2 font-medium text-gray-900">
                  {{ s.key }}
                </td>
                <td :class="{ 'text-red-800 font-bold': s.value === 'false', 'text-green-800 font-bold': s.value === 'true' }"
                    class="px-6 py-2">
                  {{
                    s.key === 'VALOR_IGV' ? `${s.value} - ${parseFloat(s.value) * 100}%` :
                        s.key === 'COSTO_CITA_REGULAR' ? `S./ ${s.value}` :
                            s.value === 'true' ? 'HABILITADO - true' :
                                s.value === 'false' ? 'DESHABILITADO - false' :
                                    s.value
                  }}
                </td>
                <td class="px-6 py-2">
                  {{ s.description }}
                </td>
                <td class="px-6 py-2">
                  {{ formatAsDatetime(s.created_at) }}
                </td>
                <td class="px-6 py-2">
                  {{ formatAsDatetime(s.updated_at) }}
                </td>
                <td class="px-6 py-3 flex justify-center items-center">
                  <div class="inline-flex rounded-md shadow-xs" role="group">
                    <button
                        class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                        title="MODIFICAR"
                        type="button"
                        @click="editVar(s)">
                      <i class="bi bi-pencil-square w-4 h-4"></i>
                    </button>
                  </div>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-if="!isLoading && !loadError" class="flex flex-col items-start">
          <button
              :disabled="isLoading"
              class="p-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-500"
              type="button"
              @click="reloadPage"
          >
            <i class="bi bi-arrow-clockwise"></i> Recargar
          </button>
        </div>

        <div v-if="loadError" class="container mt-5 mb-5 flex flex-col items-center space-y-5">
          <span><i class="bi bi-exclamation-triangle-fill text-9xl text-red-700"></i></span>
          <h1 class="text-2xl font-light">Oops! No se encontraron valores de configuración para el sistema. Comuniquese
            con administración.</h1>
          <h1 class="text-xl font-semibold text-green-800 underline" @click="reloadPage">Recargar</h1>
        </div>
      </div>
    </div>
    <UpdateIgvModal v-if="showEditIgv" :igv="igvObject" :onClose="() => {showEditIgv = false;}"></UpdateIgvModal>
    <UpdateAppointmentPriceModal v-if="showEditAppPrice" :app="appObject" :onClose="() => {showEditAppPrice = false;}"></UpdateAppointmentPriceModal>
  </main>
</template>
