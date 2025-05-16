<script setup>
import {ErrorMessage, Field, Form} from "vee-validate";
import {ref} from "vue";
import * as yup from "yup";
import {DoctorService} from "@/services/doctor-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM, UNAVAILABILITY_REASONS as URO} from "@/utils/constants.js";
import {reloadOnDismiss} from "@/utils/helpers.js";

const {onClose, doctor} = defineProps(['onClose', 'doctor']);
const submitting = ref(false);
const unavailabilityForm = ref();
const selectedReason = ref('VACACIONES');

const today = new Date();
today.setHours(0, 0, 0, 0);


//Caja negra para parsear fechas que no se debe tocar nunca lmao...
function parseDate(str) {
  if (typeof str !== 'string') return null;

  if (str.includes('-')) {
    const [y, m, d] = str.split('-').map(Number);
    return new Date(y, m - 1, d);
  }
  if (str.includes('/')) {
    const [d, m, y] = str.split('/').map(Number);
    return new Date(y, m - 1, d);
  }
  return null;
}

const schema = yup.object({
  start_datetime: yup
      .string()
      .required('Debe ingresar una fecha de inicio.')
      .test(
          'valid-start',
          'La fecha de inicio inválida o anterior al día actual.',
          value => {
            const d = parseDate(value);
            return (
                d instanceof Date &&
                !isNaN(d.getTime()) &&
                d.getTime() >= today.getTime()
            );
          }
      ),

  end_datetime: yup
      .string()
      .required('Debe ingresar una fecha de fin.')
      .test(
          'valid-end',
          'La fecha de fin debe ser al menos un día después de la fecha de inicio.',
          function (value) {
            const startStr = this.parent.start_datetime;
            const start = parseDate(startStr);
            const end = parseDate(value);

            if (
                !(start instanceof Date) ||
                isNaN(start.getTime()) ||
                !(end instanceof Date) ||
                isNaN(end.getTime())
            ) {
              return false;
            }

            // min one day after start:
            const minEnd = new Date(start);
            minEnd.setDate(minEnd.getDate() + 1);
            return end.getTime() >= minEnd.getTime();
          }
      ),

  reason: yup
      .string()
      .required('Debe seleccionar una razón.')
      .min(5, 'La razón debe tener al menos 5 caracteres.')
      .max(20, 'La razón no debe exceder 20 caracteres.'),
});

async function onSubmit(values) {
  submitting.value = true;
  if (values.reason === undefined) {
    values.reason = null
  }
  const payload = {
    ...values,
    doctor_id: doctor.id,
  }
  try {
    const response = await DoctorService.setUnavailability(payload);
    Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then((r) => reloadOnDismiss(r));
  } catch (err) {
    if (err.errors?.start_datetime) {
      unavailabilityForm.value.setFieldValue('start_datetime', '');
      unavailabilityForm.value.setFieldValue('end_datetime', '');
      Swal.fire(EM.ERROR_TAG, EM.UNAVAILABILITY_OVERLAP, 'warning');
    } else {
      Swal.fire(EM.ERROR_TAG, EM.SERVER_ERROR, 'error').then((r) => reloadOnDismiss(r));
    }
  } finally {
    submitting.value = false;
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
          Asignar indisponibilidad
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
        <Form ref="unavailabilityForm" :validation-schema="schema" class="grid gap-6" @submit="onSubmit">
          <div
              class="flex items-center p-2 text-sm text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50"
              role="alert">
            <svg aria-hidden="true" class="shrink-0 inline w-4 h-4 me-3" fill="currentColor"
                 viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
              <path
                  d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <span class="sr-only">Info</span>
            <div>
              {{ `${doctor.name} ${doctor.paternal_surname} ${doctor.maternal_surname} - DNI: ${doctor.dni}` }}
            </div>
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Fecha de Inicio</label>
            <Field id="start_datetime" :disabled="submitting" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5"
                   name="start_datetime"
                   type="date"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="start_datetime"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Fecha de Fin</label>
            <Field id="end_datetime" :disabled="submitting" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5" name="end_datetime"

                   type="date"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="end_datetime"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Razón</label>
            <Field id="reason" v-model="selectedReason" :disabled="submitting" :validate-on-input="true" as="select"
                   class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5"
                   name="reason">
              <option v-for="r in URO" :key="r.reason" :value="r.reason">{{ r.reason }}</option>
            </Field>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="reason"></ErrorMessage>
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