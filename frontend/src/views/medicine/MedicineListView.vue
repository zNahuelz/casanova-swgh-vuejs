<script setup>

import {MEDICINE_SEARCH_MODES as MSM} from "@/utils/constants.js";
import {formatAsDatetime, reloadPage} from "@/utils/helpers.js";
import {computed, onMounted, ref} from "vue";
import {useRouter} from "vue-router";
import {useAuthStore} from "@/stores/auth.js";
import * as yup from 'yup';
import {ErrorMessage, Field, Form} from 'vee-validate';
import {SupplierService} from "@/services/supplier-service.js";
import {MedicineService} from "@/services/medicine-service.js";

const searchMode = ref('id');
const medicines = ref([]);
const isLoading = ref(false);
const loadError = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);
const totalItems = ref(0);
const pageSize = ref(10);
const router = useRouter();
const authService = useAuthStore();

const dynamicSchema = computed(() => {
  let keywordValidation = yup.string().required();

  if (searchMode.value === 'id') {
    keywordValidation = keywordValidation.matches(/^[0-9]+$/, 'El ID debe ser numérico.')
        .min(1, 'El ID debe tener al menos una cifra.');
  } else if (searchMode.value === 'name') {
    keywordValidation = keywordValidation.min(3, 'El nombre debe tener al menos 3 carácteres');
  } else if (searchMode.value === 'composition') {
    keywordValidation = keywordValidation.min(3, 'La composición debe tener al menos 3 carácteres');
  } else if (searchMode.value === 'description') {
    keywordValidation = keywordValidation.min(3, 'La descripción debe tener al menos 3 carácteres');
  } else if (searchMode.value === 'barcode') {
    keywordValidation = keywordValidation.min(8, 'El código de barras debe tener al menos 8 carácteres');
  }

  return yup.object({
    searchMode: yup.string().required(),
    keyword: keywordValidation,
  });
});

async function loadMedicines(filters = {}) {
  isLoading.value = true;
  loadError.value = false;
  try {
    const pagination = {page: currentPage.value, per_page: pageSize.value}
    const sorting = {sort_by: 'stock',sort_dir:'desc'}
    const response = await MedicineService.get(filters, pagination);
    medicines.value = response.data;
    totalPages.value = response.last_page;
    totalItems.value = response.total;
    if (response.data.length <= 0) {
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
  let filters = {};
  if (values.searchMode === 'id') {
    filters = {id: keyword};
    currentPage.value = 1;
    loadMedicines(filters);
  }
  if (values.searchMode === 'name') {
    filters = {name: keyword};
    currentPage.value = 1;
    loadMedicines(filters);
  }
  if (values.searchMode === 'composition') {
    filters = {composition: keyword};
    currentPage.value = 1;
    loadMedicines(filters);
  }
  if (values.searchMode === 'description') {
    filters = {description: keyword};
    currentPage.value = 1;
    loadMedicines(filters);
  }
  if (values.searchMode === 'barcode') {
    filters = {barcode: keyword};
    currentPage.value = 1;
    loadMedicines(filters);
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    loadMedicines();
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    loadMedicines();
  }
};

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - LISTADO DE MEDICAMENTOS'
  loadMedicines();
});

function goToDetails(id) {
  router.push({name: 'medicine-detail', params: {id}});
}

function gotToEdit(id){
  router.push({name: 'edit-medicine', params: {id}});
}
</script>

<template>
  <main class="flex flex-col items-center pt-5 relative">
    <div class="container px-12 mx-auto">
      <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm w-full">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-black text-start">LISTADO DE MEDICAMENTOS</h5>

        <div class="container mt-5 mb-5 flex flex-col items-center" v-if="isLoading">
          <div role="status">
            <svg aria-hidden="true" class="inline w-30 h-30 text-gray-200 animate-spin  fill-green-600"
                 viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                  d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                  fill="currentColor"/>
              <path
                  d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                  fill="currentFill"/>
            </svg>
            <span class="sr-only">Loading...</span>
          </div>
          <h1 class="mt-5 text-2xl font-light">Cargando medicamentos...</h1>
        </div>

        <div class="container mt-5 mb-3 flex flex-col items-end" v-if="!isLoading && !loadError">
          <Form @submit="onSubmit" :validation-schema="dynamicSchema" v-slot="{ validate }">
            <div class="flex">
              <Field id="searchMode" name="searchMode" v-model="searchMode" as="select" @change="validate"
                     class="shrink-0 z-10 inline-flex w-45 items-center py-2.5 px-4 text-sm font-medium text-gray-900 bg-gray-100 border border-gray-300 rounded-s-lg hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100">
                <option v-for="sm in MSM" :key="sm.value" :value="sm.value">{{ sm.label }}</option>
              </Field>
              <ErrorMessage name="searchMode"></ErrorMessage>
              <div class="relative w-70">
                <Field :type="searchMode === 'id' ? 'number' : 'text'" id="keyword"
                       name="keyword" @input="validate"
                       class="block p-2.5 w-full z-20 text-sm text-gray-900 bg-gray-50 border-l-0 border border-gray-300 focus:ring-green-500 focus:border-green-500"
                       placeholder="Buscar..."/>
                <button type="submit"
                        class="absolute top-0 right-0 p-2.5 h-full text-sm font-medium text-white bg-green-600 rounded-e-lg hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-500">
                  <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                       viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                  </svg>
                </button>
              </div>
            </div>
          </Form>
        </div>
        <div class="container mt-5 mb-5" v-if="!isLoading && !loadError">
          <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500">
              <thead class="text-xs text-gray-700 uppercase bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3">
                  ID
                </th>
                <th scope="col" class="px-6 py-3">
                  NOMBRE
                </th>
                <th scope="col" class="px-6 py-3">
                  COMPOSICIÓN
                </th>
                <th scope="col" class="px-6 py-3">
                  CÓDIGO DE BARRAS
                </th>
                <th scope="col" class="px-6 py-3 text-end">
                  PC. VENTA
                </th>
                <th scope="col" class="px-6 py-3 text-end">
                  STOCK
                </th>
                <th scope="col" class="px-6 py-3">
                  VENDIBLE
                </th>
                <th scope="col" class="px-6 py-3">
                  PRESENTACIÓN
                </th>
                <th scope="col" class="px-6 py-3 text-center">
                  HERRAMIENTAS
                </th>
              </tr>
              </thead>
              <tbody>
              <tr
                  class="bg-white border-b border-gray-200 hover:bg-gray-50" v-for="m in medicines" :key="m.id">
                <th scope="row" class="px-6 py-2 whitespace-nowrap">
                  {{ m.id }}
                </th>
                <td class="px-6 py-2 font-medium text-gray-900">
                  {{ m.name }}
                </td>
                <td class="px-6 py-2">
                  {{ m.composition }}
                </td>
                <td class="px-6 py-2">
                  {{ m.barcode }}
                </td>
                <td class="px-6 py-2 text-end font-bold text-green-800">
                  {{ 'S./ ' + m.sell_price }}
                </td>
                <td class="px-6 py-2 text-end">
                  {{ m.stock }}
                </td>
                <td class="px-6 py-2"
                    :class="{'text-green-700 font-bold': m?.salable, 'text-red-500 font-bold': !m?.salable }">
                  {{ m?.salable ? 'SI' : 'NO' }}
                </td>
                <td class="px-6 py-2">
                  {{ m.presentation.name + ' ' + m.presentation.numeric_value + ' ' + m.presentation.aux }}
                </td>
                <td class="px-6 py-3 flex justify-center items-center">
                  <div class="inline-flex rounded-md shadow-xs" role="group">
                    <button type="button" @click="gotToEdit(m.id)" v-if="authService.getTokenDetails().role === 'ADMINISTRADOR' || authService.getTokenDetails().role === 'SECRETARIA'" title="EDITAR"
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed">
                      <i class="bi bi-pencil-square w-4 h-4"></i>
                    </button>
                    <button type="button"
                            v-if="authService.getTokenDetails().role === 'ADMINISTRADOR'" title="ELIMINAR"
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed">
                      <i class="bi bi-trash-fill w-4 h-4"></i>
                    </button>
                    <button type="button" @click="goToDetails(m.id)" title="DETALLES"
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue  -700 disabled:bg-gray-200 disabled:cursor-not-allowed">
                      <i class="bi bi-three-dots w-4 h-4"></i>
                    </button>
                  </div>
                </td>
              </tr>

              </tbody>
            </table>
            <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4 p-4"
                 aria-label="Table navigation">
            <span
                class="text-sm font-normal text-gray-500  mb-4 md:mb-0 block w-full md:inline md:w-auto">De un total de <span
                class="font-semibold text-gray-900 ">{{ totalItems }}</span> medicamentos</span>
              <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                <li>
                  <a
                      class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700"
                      @click="prevPage">Anterior</a>
                </li>
                <li>
                  <a
                      class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700"
                      @click="reloadPage" title="REFRESCAR">{{ currentPage }}</a>
                </li>
                <li>
                  <a
                      class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700"
                      @click="nextPage">Siguiente</a>
                </li>
              </ul>
            </nav>
          </div>

        </div>

        <div class="container mt-5 mb-5 flex flex-col items-center space-y-5" v-if="loadError">
          <span><i class="bi bi-exclamation-triangle-fill text-9xl text-red-700"></i></span>
          <h1 class="text-2xl font-light">Oops! No se encontraron medicamentos con los parametros ingresados. Intente
            nuevamente o registre
            algunos.</h1>
          <h1 class="text-xl font-semibold text-green-800 underline" @click="reloadPage">Recargar</h1>
        </div>
      </div>
    </div>
  </main>
</template>