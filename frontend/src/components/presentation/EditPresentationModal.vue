<script setup>
import {ref} from "vue";
import * as yup from "yup";
import {ErrorMessage, Field, Form} from "vee-validate";
import Swal from "sweetalert2";
import {PresentationService} from "@/services/presentation-service.js";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {reloadOnDismiss} from "@/utils/helpers.js";

const {onClose, presentation} = defineProps(['onClose', 'presentation']);
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
  const payload = {
    ...values,
    numeric_value: Number(values.numeric_value),
  }
  try {
    const response = await PresentationService.update(presentation.id, payload);
    Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then((r) => reloadOnDismiss(r));
  } catch (err) {
    if (err.errors?.presentation) {
      Swal.fire(EM.ERROR_TAG, EM.DUPLICATED_PRESENTATION, 'warning').then((r) => reloadOnDismiss(r));
    } else {
      Swal.fire(EM.ERROR_TAG, EM.SERVER_ERROR, 'error').then((r) => reloadOnDismiss(r));
    }
  }
}
</script>

<template>
  <div id="edit-presentation" class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm"
       tabindex="-1">
    <div class="relative bg-white rounded-lg shadow-lg w-100 max-w-md">
      <div
          class="flex items-center justify-between p-4 md:p-5 border-b rounded-t  border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Editar Presentación - ID: {{ presentation?.id }}
        </h3>
        <button :disabled="submitting"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                type="button"
                @click="onClose">
          <svg aria-hidden="true" class="w-3 h-3" fill="none" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
            <path d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" stroke="currentColor" stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"/>
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <div class="p-6 space-y-6 items-center flex flex-col">
        <Form ref="presentationForm" :initial-values="{
            name: presentation?.name || '',
            numeric_value: presentation?.numeric_value || '',
            aux: presentation?.aux || ''
        }" :validation-schema="schema" class="grid gap-6" @submit="onSubmit">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Nombre</label>
            <Field id="name" :disabled="submitting" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5" name="name"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="name"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Valor Numérico</label>
            <Field id="numeric_value" :disabled="submitting" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5"
                   name="numeric_value"

                   type="number"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="numeric_value"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Auxiliar</label>
            <Field id="aux" :disabled="submitting" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5" name="aux"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="aux"></ErrorMessage>
          </div>

          <div class="inline-flex rounded-md shadow-xs" role="group">
            <button :disabled="submitting"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                    type="button"
                    @click="onClose">
              <i class="bi bi-x-circle w-3 h-3 me-2 flex items-center justify-center"></i>
              Cancelar
            </button>
            <button :disabled="submitting"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                    type="reset">
              <i class="bi bi-arrow-clockwise w-3 h-3 me-2 flex items-center justify-center"></i>
              Limpiar
            </button>
            <button :disabled="submitting"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                    type="submit">
              <i class="bi bi-floppy-fill w-3 h-3 me-2 flex items-center justify-center"></i>
              {{ submitting ? 'Guardando...' : 'Actualizar' }}
            </button>
          </div>
        </Form>
      </div>
    </div>
  </div>
</template>
