<script setup>
import {ErrorMessage, Field, Form} from "vee-validate";
import {onMounted, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {TreatmentService} from "@/services/treatment-service.js";
import * as yup from "yup";
import {formatAsDatetime, reloadOnDismiss, reloadPage} from "@/utils/helpers.js";
import {useAuthStore} from "@/stores/auth.js";
import {SettingService} from "@/services/setting-service.js";

const submitting = ref(false);
const isLoading = ref(false);
const loadError = ref(false);
const treatment = ref({});
const router = useRouter();
const route = useRoute();
const authService = useAuthStore();
const treatmentForm = ref();

const igv = ref(0);
const profit = ref(0);
const IGV_VALUE = ref(0.18);
const igvStatus = ref(true);

const schema = yup.object().shape({
  name: yup
      .string()
      .required('Debe ingresar un nombre.')
      .min(2, 'El nombre debe tener entre 5 y 100 carácteres.')
      .max(30, 'El nombre debe tener entre 5 y 100 carácteres.'),

  description: yup
      .string()
      .max(255, 'La descripción puede tener hasta 255 carácteres.'),

  procedure: yup
      .string()
      .required('Debe ingresar el procedimiento.')
      .min(5, 'El procedimiento debe tener entre 5 y 255 carácteres.')
      .max(255, 'El procedimiento debe tener entre 5 y 255 carácteres.'),

  price: yup.number().positive('El precio debe ser positivo.').test(
      "is-decimal",
      "Máximo dos decimales permitidos.",
      (value) => /^\d+(\.\d{1,2})?$/.test(String(value))
  ).required('Debe ingresar un precio.'),
});

async function loadTreatment(id) {
  isLoading.value = true;
  try {
    treatment.value = await TreatmentService.getById(id);
    igv.value = treatment.value.igv;
    profit.value = treatment.value.profit;
    igvStatus.value = treatment.value.igv > 0;
    loadError.value = false;
    isLoading.value = false;
    await loadIgvValue();
  } catch (err) {
    loadError.value = true;
    Swal.fire(EM.ERROR_TAG, EM.TREATMENT_NOT_FOUND, 'error').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        router.back();
      }
    });
  }
}

async function onSubmit(values) {
  submitting.value = true;
  onPriceChange();
  if (values.description === undefined) {
    values.description = null;
  }
  const payload = {
    ...values,
    igv: igv.value,
    profit: profit.value,
    updated_by: authService.getUserId(),
  }
  try {
    const response = await TreatmentService.update(treatment.value.id, payload);
    Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then((r) => reloadOnDismiss(r));
  } catch (err) {
    if (err?.errors.name) {
      treatmentForm.value.setFieldValue('name', '');
      Swal.fire(EM.ERROR_TAG, EM.TREATMENT_NAME_TAKEN, 'warning');
    } else {
      Swal.fire(EM.ERROR_TAG, EM.SERVER_ERROR, 'error').then((r) => reloadOnDismiss(r));
    }
  } finally {
    submitting.value = false;
  }
}

function onPriceChange() {
  const getPrice = parseFloat(treatmentForm.value.values.price);
  if (igvStatus.value) {
    igv.value = (getPrice * IGV_VALUE.value).toFixed(2);
    profit.value = parseFloat((getPrice - igv.value).toFixed(2));
  } else {
    igv.value = 0;
    profit.value = getPrice;
  }
  console.log(getPrice);
}

function goBack() {
  router.back();
}

async function loadIgvValue() {
  isLoading.value = true;
  try {
    const response = await SettingService.getByKey('VALOR_IGV');
    IGV_VALUE.value = parseFloat(response.value);
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, EM.IGV_VAL_NOT_LOADED, 'error');
    IGV_VALUE.value = 0.18;
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - GESTIONAR TRATAMIENTO'
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
      <h5 v-if="!isLoading && !loadError" class="mb-2 text-2xl font-bold tracking-tight text-black text-center">EDITAR
        TRATAMIENTO</h5>
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

      <Form v-if="!isLoading && !loadError" ref="treatmentForm" v-slot="{validate}" :validation-schema="schema"
            class="space-y-3"
            @submit="onSubmit">
        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Nombre</label>
          <Field id="name" :disabled="submitting" :model-value="treatment?.name" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                 name="name"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="name"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Descripción</label>
          <Field id="description" :disabled="submitting" :model-value="treatment?.description" :validate-on-input="true"
                 as="textarea"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                 name="description"
                 rows="5"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                        name="description"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Procedimiento</label>
          <Field id="procedure" :disabled="submitting" :model-value="treatment?.procedure" :validate-on-input="true"
                 as="textarea"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                 name="procedure"
                 rows="5"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                        name="procedure"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Precio</label>
          <Field id="price" :disabled="submitting" :model-value="treatment?.price" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                 name="price"
                 type="number"
                 @change="onPriceChange"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="price"></ErrorMessage>
        </div>

        <div class="grid grid-cols-3 gap-4 ">
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">IGV</label>
            <div class="flex items-center ps-4 border border-gray-300 rounded-lg h-10 bg-gray-50">

              <input id="igvStatus" v-model="igvStatus" :disabled="submitting"
                     class="w-4 h-4 bg-gray-50 border-gray-300 rounded-sm focus:ring-red-500 text-red-600"
                     name="igvStatus"
                     type="checkbox"
                     @change="onPriceChange"/>
              <label class="ms-2 text-sm font-medium text-gray-900" for="igvStatus">Afecto al IGV</label>
            </div>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900 ">IGV</label>
            <input id="igv" v-model="igv"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                   disabled
                   name="igv"
                   type="number"/>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900 ">Ganancia</label>
            <input id="profit" v-model="profit"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                   disabled
                   name="profit"
                   type="number"/>
          </div>
        </div>

        <div class="flex justify-center mt-5">
          <div class="inline-flex rounded-md shadow-xs" role="group">
            <button :disabled="submitting"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                    type="button"
                    @click="goBack">
              <i class="bi bi-x-circle w-3 h-3 me-2 flex items-center justify-center"></i>
              Cancelar
            </button>
            <button :disabled="submitting" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                    type="reset"
                    @click="reloadPage()">
              <i class="bi bi-arrow-clockwise w-3 h-3 me-2 flex items-center justify-center"></i>
              Limpiar
            </button>
            <button :disabled="submitting"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                    type="submit">
              <i class="bi bi-floppy-fill w-3 h-3 me-2 flex items-center justify-center"></i>
              {{ submitting ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </div>
      </Form>
    </div>
  </main>
</template>