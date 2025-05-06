<script setup>
import {ErrorMessage, Field, Form} from "vee-validate";
import {useAuthStore} from "@/stores/auth.js";
import {ref} from "vue";
import * as yup from "yup";
import {UserService} from "@/services/user-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {reloadOnDismiss} from "@/utils/helpers.js";

const {onClose} = defineProps(['onClose']);
const authService = useAuthStore();
const submitting = ref(false);

const schema = yup.object({
  address: yup
      .string()
      .required('Debe ingresar una dirección.')
      .min(5, 'La dirección debe tener entre 5 y 100 carácteres.')
      .max(100, 'La dirección debe tener entre 5 y 100 carácteres.'),
  phone: yup
      .string()
      .required('Debe ingresar un número de teléfono.')
      .matches(/^\+?\d{6,15}$/, 'El teléfono debe contener solo números.')
      .min(6, 'El número de teléfono debe tener entre 6 y 15 carácteres.')
      .max(15, 'El número de teléfono debe tener entre 6 y 15 carácteres.'),
  password: yup.string()
      .min(5, 'Su contraseña debe tener entre 5 y 20 carácteres.')
      .max(20, 'Su contraseña debe tener entre 5 y 20 carácteres.')
      .required('Debe ingresar su contraseña.'),
});

async function onSubmit(values) {
  submitting.value = true;
  try {
    const response = await UserService.changePersonalInfo(values);
    Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        authService.logout();
      }
    });
  } catch (err) {
    if (err.aux === 'INVALID_PASSWORD') {
      Swal.fire(EM.ERROR_TAG, err.message, 'warning').then((r) => reloadOnDismiss(r));
    } else {
      Swal.fire(EM.ERROR_TAG, EM.SERVER_ERROR, 'error').then((r) => reloadOnDismiss(r));
    }
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <div id="update-pi-modal"
       class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm"
       tabindex="-1">
    <div class="relative bg-white rounded-lg shadow-lg w-100 max-w-md">
      <div
          class="flex items-center justify-between p-4 md:p-5 border-b rounded-t  border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Actualizar datos personales
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
        <Form ref="presentationForm" :validation-schema="schema" class="grid gap-6" @submit="onSubmit">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Dirección</label>
            <Field id="address" :disabled="submitting" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5"
                   name="address"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="address"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Teléfono</label>
            <Field id="phone" :disabled="submitting" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5"
                   name="phone"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="phone"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Contraseña Actual</label>
            <Field id="password" :disabled="submitting" :validate-on-input="true" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                    focus:ring-green-600 focus:border-green-600 block w-full p-2.5" name="password"
                   type="password"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="password"></ErrorMessage>
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