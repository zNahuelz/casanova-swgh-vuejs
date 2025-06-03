<script setup>
import {formatAsDatetime} from "@/utils/helpers.js";
import {onMounted, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import {useAuthStore} from "@/stores/auth.js";
import {TreatmentService} from "@/services/treatment-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM} from "@/utils/constants.js";

const isLoading = ref(false);
const loadError = ref(false);
const treatment = ref({});
const router = useRouter();
const route = useRoute();
const authService = useAuthStore();

async function loadTreatment(id) {
  isLoading.value = true;
  try {
    treatment.value = await TreatmentService.getById(id);
    loadError.value = false;
    isLoading.value = false;
  } catch (err) {
    loadError.value = true;
    Swal.fire(EM.ERROR_TAG, EM.TREATMENT_NOT_FOUND, 'error').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        router.back();
      }
    });
  }
}

function goBack() {
  router.back();
}

function goToEdit(id) {
  router.push({name: 'edit-treatment', params: {id}});
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - DETALLE DE TRATAMIENTO'
  const id = route.params.id;
  if (isNaN(id)) {
    router.back();
  }
  loadTreatment(id);
});
</script>

<template>
  <main class="flex flex-col items-center pt-5">
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm  min-w-150">
      <h5 v-if="!isLoading && !loadError" class="mb-2 text-2xl font-bold tracking-tight text-black text-center">DETALLE
        DE TRATAMIENTO</h5>
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
        <h1 class="mt-5 text-2xl font-light">Cargando tratamiento...</h1>
      </div>

      <div v-if="!isLoading && !loadError" class="border-b pb-4 border-gray-200 mb-4">
        <h6 class="text-lg font-semibold text-gray-900 mb-4">Información de Auditoría</h6>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-500">Fecha de Registro</label>
              <div class="mt-1 text-sm text-gray-900">
                {{ formatAsDatetime(treatment?.created_at) }}
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500">Registrado por</label>
              <div class="mt-1 text-sm text-gray-900">
                {{ !treatment?.created_by ? 'INSERTADO EN BASE DE DATOS' : treatment.created_by_name }}
              </div>
            </div>
          </div>
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-500">Última Modificación</label>
              <div class="mt-1 text-sm text-gray-900">
                {{ formatAsDatetime(treatment?.updated_at) }}
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500">Modificado por</label>
              <div class="mt-1 text-sm text-gray-900">
                {{ !treatment?.updated_by ? '---' : treatment.updated_by_name }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <form v-if="!isLoading && !loadError"
            class="space-y-3">
        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Nombre</label>
          <input id="name" v-model="treatment.name"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                 disabled
                 name="name"
                 type="text"/>
        </div>
        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Descripción</label>
          <textarea id="description" v-model="treatment.description"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                    disabled
                    name="description"
                    rows="5"
                    type="text"/>
        </div>
        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Procedimiento</label>
          <textarea id="procedure" v-model="treatment.procedure"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                    disabled
                    name="procedure"
                    rows="5"
                    type="text"/>
        </div>

        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900 ">IGV</label>
            <input id="igv" v-model="treatment.igv"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                   disabled
                   name="igv"
                   type="number"/>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900 ">Ganancia</label>
            <input id="profit" v-model="treatment.profit"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                   disabled
                   name="profit"
                   type="number"/>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900 ">Precio</label>
            <input id="price" v-model="treatment.price"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                   disabled
                   name="price"
                   type="number"/>
          </div>
        </div>
        <div class="flex justify-center mt-5">
          <div class="inline-flex rounded-md shadow-xs" role="group">
            <button
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                type="button"
                @click="goBack">
              <i class="bi bi-arrow-return-left w-3 h-3 me-2 flex items-center justify-center"></i>
              Atras
            </button>
            <button
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                type="button"
                @click="goToEdit(treatment.id)">
              <i class="bi bi-pencil-square w-3 h-3 me-2 flex items-center justify-center"></i>
              Editar
            </button>
          </div>
        </div>
      </form>
    </div>
  </main>
</template>
