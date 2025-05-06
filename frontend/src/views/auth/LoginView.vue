<script setup>
import {onMounted, ref} from 'vue';
import {useRouter} from 'vue-router';
import {useAuthStore} from '@/stores/auth';
import * as yup from 'yup';
import {ErrorMessage, Field, Form} from 'vee-validate';
import {initFlowbite} from 'flowbite';
import Swal from 'sweetalert2';
import {reloadOnDismiss} from '@/utils/helpers';
import {ERROR_MESSAGES as EM} from "@/utils/constants.js";

const router = useRouter();
const authStore = useAuthStore();
const submitting = ref(false);
const rememberMe = ref(false);

const schema = yup.object({
  username: yup.string().min(5, 'Su nombre de usuario debe tener entre 5 y 20 carácteres.').max(20, 'Su nombre de usuario debe tener entre 5 y 20 carácteres.').required('Debe ingresar su nombre de usuario.'),
  password: yup.string().min(5, 'Su contraseña debe tener entre 5 y 20 carácteres.').max(20, 'Su contraseña debe tener entre 5 y 20 carácteres.').required('Debe ingresar su contraseña.'),
});

async function onSubmit(values) {
  submitting.value = true;
  try {
    const success = await authStore.login(values.username, values.password, rememberMe.value);
    if (success) {
      router.push('/a');
    }
  } catch (err) {
    if (err.aux === 'ACCOUNT_DISABLED') {
      Swal.fire(EM.ERROR_TAG, err.message, 'error').then((r) => reloadOnDismiss(r));
    } else {
      Swal.fire('Oops! Credenciales incorrectas.', 'Usuario o contraseña incorrecta, intente nuevamente.', 'error').then((r) => reloadOnDismiss(r));
    }
  } finally {
    submitting.value = false;
  }
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - INICIO DE SESIÓN'
  initFlowbite();
})
</script>

<template>
  <main class="h-screen flex justify-center items-center bg-slate-200">
    <div class="w-full max-w-sm p-4 bg-white border border-gray-200 rounded-lg shadow-lg sm:p-6 md:p-8">
      <Form :validation-schema="schema" class="space-y-6" @submit="onSubmit">
        <div class="flex items-center justify-center">
          <img height="160" src="/images/logo_white.png" width="160">
        </div>
        <div class="mb-4">
          <label class="block mb-2 text-sm font-medium text-gray-900 ">Nombre de Usuario</label>
          <div class="flex">
  <span
      class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md ">
<svg aria-hidden="true" class="w-6 h-6 text-gray-500" fill="currentColor" height="24" viewBox="0 0 24 24"
     width="24" xmlns="http://www.w3.org/2000/svg">
  <path clip-rule="evenodd"
        d="M12 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4h-4Z"
        fill-rule="evenodd"/>
</svg>
  </span>
            <Field id="username" :validate-on-input="true"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-none rounded-e-lg focus:ring-green-800 focus:border-green-800 block w-full p-2.5 "
                   name="username"
                   type="text"/>
          </div>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="username"/>
        </div>
        <div class="mb-4">
          <label class="block mb-2 text-sm font-medium text-gray-900 ">Contraseña</label>
          <div class="flex">
  <span
      class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md ">
<svg aria-hidden="true" class="w-6 h-6 text-gray-500" fill="none" height="24" viewBox="0 0 24 24"
     width="24" xmlns="http://www.w3.org/2000/svg">
  <path d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2" stroke="currentColor"
        stroke-linecap="round" stroke-linejoin="round"
        stroke-width="2"/>
</svg>
  </span>
            <Field id="password" :validate-on-input="true"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-none rounded-e-lg focus:ring-green-800 focus:border-green-800 block w-full p-2.5 "
                   name="password"
                   type="password"/>
          </div>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="password"/>
        </div>
        <div class="flex items-start">
          <div class="flex items-start">
            <div class="flex items-center h-5">
              <input id="rememberMe" v-model="rememberMe"
                     class="w-4 h-4 border border-gray-300 rounded-sm bg-gray-50 focus:ring-3 focus:ring-blue-300"
                     name="rememberMe"
                     type="checkbox"
              />
            </div>
            <label class="ms-2 text-sm font-medium text-gray-900">Recuérdame</label>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <button :disabled="submitting"
                  class="px-5 py-2.5 text-sm font-medium text-white inline-flex items-center bg-blue-600 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg text-center justify-center disabled:bg-gray-600 disabled:cursor-not-allowed"
                  type="reset">
            Limpiar
          </button>

          <button :disabled="submitting"
                  class="px-5 py-2.5 text-sm font-medium text-white inline-flex items-center bg-green-600 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg text-center justify-center disabled:bg-gray-600 disabled:cursor-not-allowed"
                  type="submit">
            {{ submitting ? 'Cargando...' : 'Iniciar Sesión' }}
          </button>
        </div>
        <div class="text-sm font-medium text-gray-500">
          ¿Olvido su contraseña?
          <router-link :to="{name: 'recover-account'}" class="text-blue-700 hover:underline">Click aquí</router-link>
        </div>
      </Form>

    </div>
  </main>
</template>