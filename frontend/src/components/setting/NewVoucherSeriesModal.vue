<script setup>
import {ErrorMessage, Field, Form} from "vee-validate";
import {ref} from "vue";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM, VOUCHER_SERIE_TYPES as VST} from "@/utils/constants.js";
import * as yup from "yup";
import Swal from "sweetalert2";
import {SettingService} from "@/services/setting-service.js";
import {generateSerie, generateVoucherCode, reloadOnDismiss} from "@/utils/helpers.js";

const {onClose} = defineProps(['onClose']);
const seriesForm = ref();
const submitting = ref(false);
const selectedVoucherType = ref(VST[0].value);

const schema = yup.object({
  serie: yup
      .string()
      .required('Debe ingresar una serie')
      .matches(
          /^[1-9][0-9]{0,2}$/,
          'La serie debe ser un número entre 1 y 999.'
      ),

  next_correlative: yup
      .number()
      .typeError('El correlativo debe ser un número.')
      .required('Debe ingresar un correlativo inicial.')
      .integer('El correlativo debe ser un número entero.')
      .min(1, 'El correlativo debe ser al menos 1.')
      .max(999999, 'El correlativo debe ser como máximo 999.999.'),

  type: yup
      .string()
      .required('Debe seleccionar un tipo para la serie.')
      .oneOf(['BOL', 'FACT'], 'El tipo debe ser BOLETA o FACTURA.'),
});

async function onSubmit(values) {
  submitting.value = true;
  Swal.fire({
    title: 'Confirmación de Solicitud',
    html: `¿Está seguro de crear la siguiente serie? <br>
    SERIE: ${values.serie} - CORRELATIVO INICIAL: ${values.next_correlative} Los comprobantes de pago serán generados bajo el esquema:
    <br> ${generateVoucherCode(parseInt(values.serie), values.type, parseInt(values.next_correlative))}
    `,
    icon: 'question',
    showCancelButton: true,
    cancelButtonText: 'CANCELAR',
    confirmButtonText: 'CONFIRMAR',
    confirmButtonColor: '#008236',
    cancelButtonColor: '#e7000b',
  }).then(async (op) => {
    if (op.isConfirmed) {
      try {
        const payload = {
          ...values,
          serie: generateSerie(parseInt(values.serie), values.type),
          next_correlative: parseInt(values.next_correlative) || 0,
        }
        const response = await SettingService.createVoucherSeries(payload);
        Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then(reloadOnDismiss);
        console.log(payload);
      } catch (err) {
        if (err.errors?.serie) {
          submitting.value = false;
          Swal.fire(EM.ERROR_TAG, 'La serie ingresada ya se encuentra en uso. Intente nuevamente.', 'warning');
        } else {
          Swal.fire(EM.ERROR_TAG, err.message ? err.message : EM.SERVER_ERROR, 'error').then(reloadOnDismiss);
        }
      }
    } else {
      submitting.value = false;
    }
  });
}
</script>

<template>
  <div id="new-series" class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm"
       tabindex="-1">
    <div class="relative bg-white rounded-lg shadow-lg w-100 max-w-md">
      <div
          class="flex items-center justify-between p-4 md:p-5 border-b rounded-t  border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Comprobante de Pago: Nueva serie
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

        <div v-if="submitting" class="container mt-5 mb-5 flex flex-col items-center">
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
          <h1 class="mt-5 text-2xl font-light">Guardando...</h1>
        </div>

        <Form v-if="!submitting" ref="seriesForm" :validation-schema="schema" class="grid gap-6" @submit="onSubmit">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Serie (1-999)</label>
            <Field id="serie" :disabled="submitting" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5"
                   name="serie"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium" name="serie"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Próximo Correlativo</label>
            <Field id="next_correlative" :disabled="submitting" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5"
                   name="next_correlative"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium"
                          name="next_correlative"></ErrorMessage>
          </div>

          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Tipo de Comprobante</label>
            <Field id="type" v-model="selectedVoucherType" :disabled="submitting" :validate-on-input="true" as="select"
                   class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5"
                   name="type">
              <option v-for="e in VST" :key="e.value" :value="e.value">{{ e.label }}</option>
            </Field>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="type"></ErrorMessage>
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
              {{ submitting ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </Form>
      </div>
    </div>
  </div>
</template>