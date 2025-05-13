<script setup>
import {onMounted, ref} from "vue";
import {ErrorMessage, Field, Form} from "vee-validate";
import * as yup from "yup";
import {useRouter} from "vue-router";
import {useAuthStore} from "@/stores/auth.js";
import {TreatmentService} from "@/services/treatment-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {reloadOnDismiss} from "@/utils/helpers.js";

const submitting = ref(false);
const router = useRouter();
const authService = useAuthStore();
const treatmentForm = ref();

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

async function onSubmit(values) {
  submitting.value = true;
  if (values.description === undefined) {
    values.description = null;
  }
  const payload = {
    ...values,
    created_by: authService.getUserId(),
  }
  try {
    const response = await TreatmentService.create(payload);
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


function goBack() {
  router.back();
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - NUEVO TRATAMIENTO'
});
</script>

<template>
  <main class="flex flex-col items-center pt-5">
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm  min-w-150">
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-black text-center">NUEVO TRATAMIENTO</h5>
      <Form ref="treatmentForm" v-slot="{validate}" :validation-schema="schema" class="space-y-3" @submit="onSubmit">
        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Nombre</label>
          <Field id="name" :disabled="submitting" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                 name="name"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="name"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Descripción</label>
          <Field id="description" :disabled="submitting" :validate-on-input="true" as="textarea"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                 name="description"
                 rows="5"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                        name="description"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Procedimiento</label>
          <Field id="procedure" :disabled="submitting" :validate-on-input="true" as="textarea"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                 name="procedure"
                 rows="5"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                        name="procedure"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Precio</label>
          <Field id="price" :disabled="submitting" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                 name="price"
                 type="number"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="price"></ErrorMessage>
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
        </div>
      </Form>
    </div>
  </main>
</template>