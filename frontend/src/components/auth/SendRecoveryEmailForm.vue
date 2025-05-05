<script setup>
import {ErrorMessage, Field, Form} from "vee-validate";
import {ref} from "vue";
import {useRouter} from "vue-router";
import * as yup from "yup";
import {UserService} from "@/services/user-service.js";
import Swal from "sweetalert2";
import {SUCCESS_MESSAGES as SM} from "@/utils/constants.js";

const router = useRouter();
const submitting = ref(false);

const schema = yup.object({
  email: yup
      .string()
      .required('Debe ingresar una dirección de E-Mail.')
      .email('El E-Mail debe cumplir el formato EMAIL@DOMINIO.COM.')
      .max(50, 'El E-Mail debe tener como máximo 50 carácteres.').matches(/^(?=.{1,50}$)[^\s@]{1,64}@[^\s@]{1,255}\.[^\s@]{1,24}$/, 'El E-Mail debe cumplir el formato EMAIL@DOMINIO.COM.'),
});

async function onSubmit(values) {
  submitting.value = true;
  try {
    const response = await UserService.sendRecoveryMail(values);
    Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        goBack();
      }
    });
  } catch (err) {
    Swal.fire(SM.SUCCESS_TAG, err.message, 'success').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        goBack();
      }
    });
  } finally {
    submitting.value = false;
  }
}

function goBack() {
  router.back();
}
</script>

<template>
  <main class="h-screen flex justify-center items-center bg-slate-200">
    <div class="w-full max-w-sm p-4 bg-white border border-gray-200 rounded-lg shadow-lg sm:p-6 md:p-8">
      <Form class="space-y-6" @submit="onSubmit" :validation-schema="schema">
        <div class="flex items-center justify-center">
          <img src="/images/logo_white.png" width="160" height="160" alt="Logo compañia...">
        </div>
        <div class="mb-4">
          <p class="text-center font-light text-[14px]">El siguiente formulario le permite recuperar el acceso a su
            cuenta en caso de haber olvidado su contraseña. <br>Debe llenar el formulario con un correo electrónico
            válido; posteriormente debe esperar un correo con instrucciones para recuperar el acceso a su cuenta.</p>
        </div>
        <div class="mb-4">
          <label class="block mb-2 text-sm font-medium text-gray-900 ">Correo Electrónico</label>
          <div class="flex">
  <span
      class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md ">
<svg class="w-6 h-6 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
     fill="currentColor" viewBox="0 0 24 24">
  <path fill-rule="evenodd"
        d="M5.024 3.783A1 1 0 0 1 6 3h12a1 1 0 0 1 .976.783L20.802 12h-4.244a1.99 1.99 0 0 0-1.824 1.205 2.978 2.978 0 0 1-5.468 0A1.991 1.991 0 0 0 7.442 12H3.198l1.826-8.217ZM3 14v5a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-5h-4.43a4.978 4.978 0 0 1-9.14 0H3Z"
        clip-rule="evenodd"/>
</svg>

  </span>
            <Field type="email" id="email" name="email" :validate-on-input="true" :disabled="submitting"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-none rounded-e-lg focus:ring-green-800 focus:border-green-800 block w-full p-2.5 "/>
          </div>
          <ErrorMessage name="email" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"/>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <button type="button" :disabled="submitting" @click="goBack"
                  class="px-5 py-2.5 text-sm font-medium text-white inline-flex items-center bg-blue-600 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg text-center justify-center disabled:bg-gray-600 disabled:cursor-not-allowed">
            Atras
          </button>
          <button type="submit" :disabled="submitting"
                  class="px-5 py-2.5 text-sm font-medium text-white inline-flex items-center bg-green-600 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg text-center justify-center disabled:bg-gray-600 disabled:cursor-not-allowed">
            {{ submitting ? 'Cargando...' : 'Continuar' }}
          </button>
        </div>
      </Form>
    </div>
  </main>
</template>
