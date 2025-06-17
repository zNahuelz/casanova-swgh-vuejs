<script setup>
import {ErrorMessage, Field, Form} from "vee-validate";
import {ref} from "vue";
import * as yup from "yup";
import dayjs from "dayjs";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {reloadOnDismiss} from "@/utils/helpers.js";
import {HolidayService} from "@/services/holiday-service.js";

const {onClose, holiday} = defineProps(['onClose', 'holiday']);
const submitting = ref(false);

const isRecurring = ref(holiday.is_recurring || false);

const schema = yup.object({
  name: yup
      .string()
      .min(5, 'El nombre debe tener entre 5 y 100 carácteres.')
      .max(100, 'El nombre debe tener entre 5 y 100 carácteres.')
      .matches(
          /^(?!\s*$).{5,100}$/,
          'El nombre debe tener entre 5 y 100 carácteres (sin espacios al inicio/fin).'
      )
      .required('Debe ingresar un nombre.'),

  date: yup
      .string()
      .required('Debe seleccionar una fecha.')
      .test('valid-date-based-on-recurring', 'Una fecha en el pasado solo es válida si es recurrente.', function (value) {
        if (!value) return false;

        const inputDate = dayjs(value, 'YYYY-MM-DD', true);
        if (!inputDate.isValid()) return this.createError({message: 'Formato de fecha inválido. Use YYYY-MM-DD.'});

        const today = dayjs().startOf('day');

        if (inputDate.isSame(today) || inputDate.isAfter(today)) {
          return true;
        }

        return inputDate.isBefore(today) && isRecurring.value === true;
      }),
});

async function onSubmit(values) {
  submitting.value = true;
  try {
    const payload = {
      id: parseInt(holiday.id),
      name: values.name.trim().toUpperCase(),
      date: values.date,
      is_recurring: isRecurring.value,
    }
    const response = await HolidayService.update(payload);
    Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then(reloadOnDismiss);
  } catch (err) {
    if (err.errors?.date) {
      Swal.fire(EM.ERROR_TAG, 'La fecha seleccionada ya se encuentra registrada como un feriado recurrente. Intente nuevamente o configure el feriado existente.', 'warning');
    } else {
      Swal.fire(EM.ERROR_TAG, EM.SERVER_ERROR, 'error').then(reloadOnDismiss);
    }
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <div id="edit-holiday" class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm"
       tabindex="-1">
    <div class="relative bg-white rounded-lg shadow-lg w-100 max-w-md">
      <div
          class="flex items-center justify-between p-4 md:p-5 border-b rounded-t  border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Editar Feriado: {{ holiday.name }}
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
          name: holiday.name || '',
          date: holiday.date || dayjs().format('YYYY-MM-DD'),
        }" :validation-schema="schema" class="grid gap-6" @submit="onSubmit">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Nombre</label>
            <Field id="name" :disabled="submitting" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5"
                   name="name"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="name"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Fecha</label>
            <Field id="date" :disabled="submitting" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5"
                   name="date"
                   type="date"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="date"></ErrorMessage>
          </div>

          <div>
            <div class="flex items-center me-4">
              <input id="isRecurring" v-model="isRecurring"
                     :disabled="submitting"
                     class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-green-500 focus:ring-2"
                     name="isRecurring"
                     type="checkbox"/>
              <label class="ms-2 text-sm font-medium text-gray-900"
                     for="green-checkbox">{{ isRecurring ? 'RECURRENTE' : 'NO RECURRENTE' }}</label>
            </div>
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