<script setup>
import {onMounted, ref} from "vue";
import * as yup from "yup";
import {ErrorMessage, Field, Form} from "vee-validate";
import {DOCTOR_APPOINTMENT_STATUS as DAS, SUCCESS_MESSAGES as SM, ERROR_MESSAGES as EM} from "@/utils/constants.js";
import {formatAsDate, formatAsTime, reloadOnDismiss} from "@/utils/helpers.js";
import {useAuthStore} from "@/stores/auth.js";
import {AppointmentService} from "@/services/appointment-service.js";
import Swal from "sweetalert2";

const {
  onClose,
  appointment,
} = defineProps(['onClose', 'appointment']);

const submitting = ref(false);
const notesForm = ref();
const authService = useAuthStore();

const schema = yup.object().shape({
  notes: yup
      .string()
      .min(5, 'Las anotaciones deben tener entre 5 y 255 carácteres.')
      .max(255, 'Las anotaciones deben tener entre 5 y 255 carácteres.')
      .test(
          'has-visible-char',
          'Las notas no pueden ser solo espacios en blanco.',
          value => !!value && /\S/.test(value)
      )
      .test(
          'no-leading-space',
          'Las notas no deben comenzar con espacios.',
          value => !!value && !value.startsWith(' ')
      )
      .required('Debe llenar las notas.'),
  status: yup.string().min(1).max(15).required('Debe seleccionar un estado para la cita.'),
});

async function onSubmit(values) {
  submitting.value = true;
  const payload = {
    ...values,
    appointment_id: appointment.id,
    updated_by: authService.getUserId(),
  }
  try{
    const response = await AppointmentService.fillNotes(payload);
    Swal.fire(SM.SUCCESS_TAG,response.message,'success').then((r) => reloadOnDismiss(r));
  }
  catch(err){
    console.log(err);
    if(err.errors?.notes){
      submitting.value = false;
      notesForm.value.setFieldValue('notes','');
      Swal.fire(EM.ERROR_TAG,EM.INVALID_NOTES_FORMAT,'warning');
    }
    else{
      Swal.fire(EM.ERROR_TAG,err.message ? err.message : EM.SERVER_ERROR,'error').then((r) =>reloadOnDismiss(r));
    }

  }
}

onMounted(() => {
  notesForm.value.setFieldValue('status', DAS[0].value);
})

</script>

<template>
  <div id="appointment-notes-modal"
       class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm"
       tabindex="-1">
    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-2xl">
      <div
          class="flex items-center justify-between p-4 md:p-5 border-b rounded-t  border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Notas de Cita #{{ appointment.id }}
        </h3>
        <button :disabled="submitting"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                type="button"
                @click="onClose"
        >
          <svg aria-hidden="true" class="w-3 h-3" fill="none" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
            <path d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" stroke="currentColor" stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"/>
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <div class="p-6 space-y-6">
        <div class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50" role="alert">
          <span class="font-medium">MODIFICANDO LA SIGUIENTE CITA:</span>
          <ul>
            <li><span class="font-bold">DNI: </span>{{appointment.patient?.dni}}</li>
            <li><span class="font-bold">PACIENTE: </span>{{`${appointment.patient?.name} ${appointment.patient?.paternal_surname} ${appointment.patient?.maternal_surname}`}}</li>
            <li><span class="font-bold">FECHA: </span>{{appointment.recheduling_date ? formatAsDate(appointment.recheduling_date) : formatAsDate(appointment.date)}}</li>
            <li><span class="font-bold">HORA: </span>{{ appointment.rescheduling_time ? formatAsTime(appointment.rescheduling_time) : formatAsTime(appointment.time)}}</li>
          </ul>
        </div>
        <Form ref="notesForm" v-slot="{validate}" :validation-schema="schema" @submit="onSubmit">
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900 ">Notas de Cita</label>
            <Field id="notes" :disabled="submitting"
                   :validate-on-input="true"
                   as="textarea"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                   name="notes" rows="6"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="notes"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900 ">Estado de Cita</label>
            <Field id="status" :disabled="submitting"
                   :validate-on-input="true"
                   as="select"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                   name="status"
                   @change="validate">
              <option v-for="s in DAS" :key="s.value" :value="s.value">{{ s.label }}</option>
            </Field>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="status"></ErrorMessage>
          </div>

          <div class="flex flex-col items-center mt-3">
            <div class="inline-flex rounded-md shadow-xs" role="group">
              <button
                  :disabled="submitting"
                  class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-900 bg-white  border-s border-t border-b border-e rounded-s-lg border-gray-200 hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                  type="button"
                  @click="onClose">
                <i class="bi bi-x-square m-1 w-4 h-4"></i>
                Cancelar
              </button>
              <button
                  :disabled="submitting"
                  class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-e border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                  type="submit"
              >
                <i class="bi bi-floppy-fill m-1 w-4 h-4"></i>
                Guardar
              </button>

            </div>
          </div>
        </Form>
      </div>
    </div>
  </div>
</template>