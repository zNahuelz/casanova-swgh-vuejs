<script setup>
import {ErrorMessage, Field, Form} from "vee-validate";
import {ref} from "vue";
import * as yup from "yup";
import {UserService} from "@/services/user-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {useRouter} from "vue-router";


const props = defineProps(['token']);
const router = useRouter();
const submitting = ref(false);

const schema = yup.object({
  passwordOne: yup
      .string()
      .min(5, 'Su contraseña debe tener entre 5 y 20 carácteres.')
      .max(20, 'Su contraseña debe tener entre 5 y 20 carácteres.')
      .required('Debe ingresar su nueva contraseña.'),
  passwordTwo: yup
      .string()
      .min(5, 'Su contraseña debe tener entre 5 y 20 carácteres.')
      .max(20, 'Su contraseña debe tener entre 5 y 20 carácteres.')
      .required('Debe confirmar su contraseña.')
      .oneOf([yup.ref('passwordOne')], 'Las contraseñas no coinciden.'),
});

async function onSubmit(values) {
  submitting.value = true;
  const payload = {
    password: values.passwordOne,
    token: props.token
  }
  try {
    const response = await UserService.changePasswordWithToken(payload);
    Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        router.push({name: 'login'});
      }
    });
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, EM.EXPIRED_RECOVERY_TOKEN, 'error').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        router.push({name: 'login'});
      }
    });
  }
}
</script>

<template>
  <main class="h-screen flex justify-center items-center bg-slate-200">
    <div class="w-full max-w-sm p-4 bg-white border border-gray-200 rounded-lg shadow-lg sm:p-6 md:p-8">
      <Form :validation-schema="schema" class="space-y-6" @submit="onSubmit">
        <div class="flex items-center justify-center">
          <img height="160" src="/images/logo_white.png" width="160" draggable="false" @contextmenu.prevent>
        </div>
        <div class="mb-4">
          <p class="text-center font-light text-[14px]">El siguiente formulario le permite recuperar el acceso a su
            cuenta en caso de haber olvidado su contraseña. <br>Debe llenar los campos con su nueva contraseña,
            posteriormente podrá iniciar sesión normalmente con sus nuevas credenciales.
            <br><span class="font-bold text-red-500">Si no solicito la recuperación de su cuenta, puede salir de esta página.</span>
          </p>
        </div>
        <div class="mb-4">
          <label class="block mb-2 text-sm font-medium text-gray-900 ">Nueva Contraseña</label>
          <div class="flex">
  <span
      class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md ">
<svg aria-hidden="true" class="w-6 h-6 text-gray-500" fill="none" height="24" viewBox="0 0 24 24"
     width="24" xmlns="http://www.w3.org/2000/svg">
  <path d="M12 14v3m-3-6V7a3 3 0 1 1 6 0v4m-8 0h10a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1Z"
        stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
        stroke-width="2"/>
</svg>

  </span>
            <Field id="passwordOne" :disabled="submitting" :validate-on-input="true"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-none rounded-e-lg focus:ring-green-800 focus:border-green-800 block w-full p-2.5 "
                   name="passwordOne"
                   type="password"/>
          </div>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="passwordOne"/>
        </div>
        <div class="mb-4">
          <label class="block mb-2 text-sm font-medium text-gray-900 ">Repetir contraseña</label>
          <div class="flex">
  <span
      class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md ">
<svg aria-hidden="true" class="w-6 h-6 text-gray-500" fill="none" height="24" viewBox="0 0 24 24"
     width="24" xmlns="http://www.w3.org/2000/svg">
  <path d="M12 14v3m-3-6V7a3 3 0 1 1 6 0v4m-8 0h10a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1Z"
        stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
        stroke-width="2"/>
</svg>
  </span>
            <Field id="passwordTwo" :disabled="submitting" :validate-on-input="true"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-none rounded-e-lg focus:ring-green-800 focus:border-green-800 block w-full p-2.5 "
                   name="passwordTwo"
                   type="password"/>
          </div>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="passwordTwo"/>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <button :disabled="submitting"
                  class="px-5 py-2.5 text-sm font-medium text-white inline-flex items-center bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 rounded-lg text-center justify-center disabled:bg-gray-600 disabled:cursor-not-allowed"
                  type="button">
            Cancelar
          </button>
          <button :disabled="submitting"
                  class="px-5 py-2.5 text-sm font-medium text-white inline-flex items-center bg-green-600 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg text-center justify-center disabled:bg-gray-600 disabled:cursor-not-allowed"
                  type="submit">
            {{ submitting ? 'Cargando...' : 'Continuar' }}
          </button>
        </div>
      </Form>

    </div>
  </main>
</template>

