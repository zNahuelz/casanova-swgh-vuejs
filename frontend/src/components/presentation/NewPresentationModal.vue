<script setup>
import {ref} from "vue";
import * as yup from "yup";
import {ErrorMessage, Field, Form} from "vee-validate";
import Swal from "sweetalert2";
import {PresentationService} from "@/services/presentation-service.js";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {reloadOnDismiss} from "@/utils/helpers.js";

const {onClose} = defineProps(['onClose']);
const submitting = ref(false);
const presentationForm = ref();

const schema = yup.object({
  name: yup.string().min(2, 'El nombre debe tener entre 2 y 20 carácteres.').max(50, 'El nombre debe tener entre 2 y 20 carácteres.').required('Debe ingresar un nombre.'),
  numeric_value: yup
      .number()
      .typeError('Debe ingresar números.')
      .positive('El valor debe ser un número positivo.')
      .test('is-two-decimals', 'El valor debe tener dos decimales como máximo.', value => {
        return value === undefined || /^\d+(\.\d{1,2})?$/.test(value.toString());
      })
      .required('Debe ingresar el valor numérico de la presentación'),
  aux: yup.string().max(20, 'El valor auxiliar debe tener entre 2 y 20 carácteres.'),
});

async function onSubmit(values) {
  submitting.value = true;
  try {
    const response = await PresentationService.create(values);
    Swal.fire(SM.SUCCESS_TAG, SM.PRESENTATION_CREATED, 'success').then((r) => reloadOnDismiss(r));
  } catch (err) {
    if (err.errors.presentation) {
      Swal.fire(EM.ERROR_TAG, EM.DUPLICATED_PRESENTATION, 'warning').then((r) => reloadOnDismiss(r));
    } else {
      Swal.fire(EM.ERROR_TAG, EM.SERVER_ERROR, 'error').then((r) => reloadOnDismiss(r));
    }
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <div id="new-presentation" tabindex="-1"
       class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm">
    <div class="relative bg-white rounded-lg shadow-lg w-100 max-w-md">
      <div
          class="flex items-center justify-between p-4 md:p-5 border-b rounded-t  border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Nueva presentación
        </h3>
        <button type="button" @click="onClose" :disabled="submitting"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <div class="p-6 space-y-6 items-center flex flex-col">
        <Form class="grid gap-6" :validation-schema="schema" @submit="onSubmit" ref="presentationForm">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Nombre</label>
            <Field type="text" id="name" name="name" :validate-on-input="true" :disabled="submitting"
                   class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5"/>
            <ErrorMessage name="name" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Valor Numérico</label>
            <Field type="number" id="numeric_value" name="numeric_value" :validate-on-input="true" :disabled="submitting"
                   class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5"/>
            <ErrorMessage name="numeric_value"
                          class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
          </div>

          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Auxiliar</label>
            <Field type="text" id="aux" name="aux" :validate-on-input="true" :disabled="submitting"
                   class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5"/>
            <ErrorMessage name="aux" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
          </div>

          <div class="inline-flex rounded-md shadow-xs" role="group">
            <button :disabled="submitting" @click="onClose" type="button"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed">
              <i class="bi bi-x-circle w-3 h-3 me-2 flex items-center justify-center"></i>
              Cancelar
            </button>
            <button type="reset" :disabled="submitting"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 disabled:bg-gray-200 disabled:cursor-not-allowed">
              <i class="bi bi-arrow-clockwise w-3 h-3 me-2 flex items-center justify-center"></i>
              Limpiar
            </button>
            <button type="submit" :disabled="submitting"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed">
              <i class="bi bi-floppy-fill w-3 h-3 me-2 flex items-center justify-center"></i>
              {{ submitting ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </Form>
      </div>
    </div>
  </div>
</template>
