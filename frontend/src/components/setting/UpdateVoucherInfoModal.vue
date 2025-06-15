<script setup>
import {ErrorMessage, Field, Form} from "vee-validate";
import {ref} from "vue";
import * as yup from "yup";
import {SettingService} from "@/services/setting-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {reloadOnDismiss} from "@/utils/helpers.js";

const {onClose, setting} = defineProps(['onClose', 'setting']);
const submitting = ref(false);
const rucForm = ref();
const addressForm = ref();

const addressSchema = yup.object({
  address: yup
      .string()
      .trim()
      .min(10, 'La dirección debe tener entre 10 y 120 carácteres.')
      .max(120, 'La dirección debe tener entre 10 y 120 carácteres.')
      .required('Debe ingresar una dirección.')
      .test(
          'not-only-spaces',
          'La dirección no puede contener solo espacios en blanco.',
          value => !!value && value.trim().length > 0
      ),

  description: yup
      .string()
      .max(255, 'La descripción debe tener entre 5 y 255 carácteres.')
      .notRequired()
      .nullable(),
});

const rucSchema = yup.object({
  ruc: yup
      .string()
      .required('Debe ingresar un RUC.')
      .matches(/^(10|20)[0-9]{9}$/, 'El RUC debe comenzar con 10 o 20 y tener máximo 11 carácteres.')
      .min(11, 'El RUC debe tener 11 carácteres.')
      .max(11, 'El RUC debe tener 11 carácteres.'),

  description: yup
      .string()
      .max(255, 'La descripción debe tener entre 5 y 255 carácteres.')
      .notRequired()
      .nullable(),
});


async function onSubmit(values) {
  submitting.value = true;
  let payload = {};
  if (setting.key === 'VALOR_SEDE') {
    payload = {
      key: 'VALOR_SEDE',
      address: values.address.trim(),
      description: values.description.length >= 1 ? values.description : null,
      type: 'ADDRESS_CHANGE'
    }
  } else if (setting.key === 'VALOR_RUC') {
    payload = {
      key: 'VALOR_RUC',
      ruc: values.ruc.trim(),
      description: values.description.length >= 1 ? values.description : null,
      type: 'RUC_CHANGE'
    }
  }
  try {
    if (payload.type) {
      const response = await SettingService.updateVoucherInfo(payload);
      Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then(reloadOnDismiss);
    } else {
      Swal.fire(EM.ERROR_TAG, EM.INVALID_SETTINGS_PAYLOAD, 'error').then(reloadOnDismiss);
    }
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, err.message ? err.message : EM.SERVER_ERROR, 'error').then(reloadOnDismiss);
  } finally {
    submitting.value = false;
  }
}

</script>

<template>
  <div id="edit-address-nd-ruc" class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm"
       tabindex="-1">
    <div class="relative bg-white rounded-lg shadow-lg w-100 max-w-md">
      <div
          class="flex items-center justify-between p-4 md:p-5 border-b rounded-t  border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Modificar variable: {{ setting.key }}
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
        <div class="flex p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50" role="alert">
          <svg aria-hidden="true" class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" fill="currentColor"
               viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
          </svg>
          <span class="sr-only">Info</span>
          <div>
            <span class="font-medium">Recuerde:</span>
            <ul class="mt-1.5 list-disc list-inside">
              <li>Al modificar el valor del RUC o dirección este se vera reflejado en las boletas de venta.
              </li>
              <li>Los valores en comprobantes de pago antiguos se verán afectados.</li>
              <li>No es necesario que modifique la descripción.</li>
            </ul>
          </div>
        </div>
        <Form v-if="setting.key === 'VALOR_SEDE'" ref="addressForm" :initial-values="{
            address: setting.value || '',
            description: setting.description || ''
        }" :validation-schema="addressSchema" class="grid gap-6" @submit="onSubmit">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Dirección</label>
            <Field id="address" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5" name="address"
                   :disabled="submitting"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium"
                          name="address"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Descripción</label>
            <Field id="description" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5" name="description"
                   :disabled="submitting"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium"
                          name="description"></ErrorMessage>
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

        <Form v-if="setting.key === 'VALOR_RUC'" ref="addressForm" :initial-values="{
            ruc: setting.value || '',
            description: setting.description || ''
        }" :validation-schema="rucSchema" class="grid gap-6" @submit="onSubmit">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Dirección</label>
            <Field id="ruc" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5" name="ruc"
                   :disabled="submitting"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium"
                          name="ruc"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Descripción</label>
            <Field id="description" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5" name="description"
                   :disabled="submitting"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium"
                          name="description"></ErrorMessage>
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