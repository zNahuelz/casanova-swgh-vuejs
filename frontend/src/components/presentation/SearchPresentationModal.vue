<script setup>
import {ErrorMessage, Field, Form} from "vee-validate";
import {PRESENTATION_SEARCH_MODES as PSM} from "@/utils/constants.js";
import {computed, ref} from "vue";
import * as yup from "yup";
import {PresentationService} from "@/services/presentation-service.js";

const searchMode = ref('id');
const submitting = ref(false);
const {onClose} = defineProps(['onClose']);
const isLoading = ref(false);
const loadError = ref(false);
const presentations = ref([]);
const presentationsLoaded = ref(false);

const dynamicSchema = computed(() => {
  let keywordValidation = yup.string().required('Debe ingresar un criterio de busqueda.');

  if (searchMode.value === 'id') {
    keywordValidation = keywordValidation.matches(/^[0-9]+$/, 'El ID debe ser numérico.')
        .min(1, 'El ID debe tener al menos una cifra.');
  } else if (searchMode.value === 'name') {
    keywordValidation = keywordValidation.min(2, 'El nombre debe tener al menos 2 carácteres');
  } else if (searchMode.value === 'aux') {
    keywordValidation = keywordValidation.min(1, 'El auxiliar debe tener al menos 2 carácteres.');
  }

  return yup.object({
    searchMode: yup.string().required(),
    keyword: keywordValidation,
  });
});

function onSubmit(values) {
  const keyword = values.keyword;
  let filters = {};
  if (values.searchMode === 'id') {
    filters = {id: keyword}
    loadPresentations(filters);
  }
  if (values.searchMode === 'name') {
    filters = {name: keyword};
    loadPresentations(filters);
  }
  if (values.searchMode === 'aux') {
    filters = {aux: keyword};
    loadPresentations(filters);
  }
}

const loadPresentations = async (filters = {}) => {
  isLoading.value = true;
  loadError.value = false;
  presentationsLoaded.value = false;
  try {
    const pagination = {page: 1, per_page: 1000}
    const response = await PresentationService.get(filters, pagination);
    presentations.value = response.data;
    presentationsLoaded.value = true
    if (response.data.length <= 0) {
      loadError.value = true;
      presentationsLoaded.value = false;
    }
  } catch (err) {
    loadError.value = true;
    presentationsLoaded.value = false;
  } finally {
    isLoading.value = false;
  }
}

</script>

<template>
  <div id="new-presentation" tabindex="-1"
       class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm">
    <div class="relative bg-white rounded-lg shadow-lg w-auto">
      <div
          class="flex items-center justify-between p-4 md:p-5 border-b rounded-t  border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Buscar presentación
        </h3>
        <button type="button" @click="onClose(null)" :disabled="submitting || isLoading"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <div class="p-6 space-y-6 items-center flex flex-col">
        <div class="container mt-3 mb-3">
          <Form @submit="onSubmit" :validation-schema="dynamicSchema" v-slot="{ validate }" v-if="!isLoading"
                class="text-center">
            <div class="flex items-center justify-between w-full">

              <div class="flex items-center">
                <Field id="searchMode" name="searchMode" v-model="searchMode" as="select" @change="validate"
                       class="shrink-0 z-10 inline-flex w-45 items-center py-2.5 px-4 text-sm font-medium text-gray-900 bg-gray-100 border border-gray-300 rounded-s-lg hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100">
                  <option v-for="sm in PSM" :key="sm.value" :value="sm.value">{{ sm.label }}</option>
                </Field>

                <div class="relative w-full">
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
            </div>
            <ErrorMessage name="keyword" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
          </Form>

          <table class="w-full text-sm text-left rtl:text-right text-gray-500 mt-5" v-if="presentationsLoaded">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3">
                ID
              </th>
              <th scope="col" class="px-6 py-3">
                NOMBRE COMPLETO
              </th>
              <th scope="col" class="px-6 py-3 text-center">
                SELECCIONAR
              </th>
            </tr>
            </thead>
            <tbody>
            <tr
                class="bg-white border-b border-gray-200 hover:bg-gray-50" v-for="p in presentations" :key="p.id">
              <th scope="row" class="px-6 py-2 whitespace-nowrap">
                {{ p.id }}
              </th>
              <td class="px-6 py-2 font-medium text-gray-900">
                {{ p.name + ' ' + p.numeric_value + ' ' + p.aux }}
              </td>
              <td class="px-6 py-3 flex justify-center items-center">
                <div class="inline-flex rounded-md shadow-xs" role="group">
                  <button type="button" @click="onClose(p)"
                          class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed">
                    <i class="bi bi-check2-circle w-4 h-4"></i>
                  </button>
                </div>
              </td>
            </tr>

            </tbody>
          </table>

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
            <h1 class="mt-5 text-2xl font-light">Buscando presentaciones...</h1>
          </div>


          <div class="container mt-5  flex flex-col items-center"
               v-if="!isLoading && !presentationsLoaded && loadError">
            <span><i class="bi bi-exclamation-triangle-fill text-8xl text-red-700"></i></span>
            <h1 class="mt-5 text-lg font-light text-center">No se encontraron presentaciones con el criterio
              ingresado.</h1>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>
