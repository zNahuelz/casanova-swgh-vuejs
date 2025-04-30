<script setup>
import {onMounted, ref} from 'vue';
import {useRouter} from 'vue-router';
import {useAuthStore} from '@/stores/auth';
import * as yup from 'yup';
import {Form, Field, ErrorMessage, useForm} from 'vee-validate';
import {initFlowbite} from 'flowbite';
import Swal from 'sweetalert2';
import {reloadOnDismiss} from '@/utils/helpers';

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
    console.log(rememberMe.value)
    const success = await authStore.login(values.username, values.password, rememberMe.value);
    if (success) {
      router.push('/a');
    }
  } catch (err) {
    Swal.fire('Oops! Credenciales incorrectas.', 'Usuario o contraseña incorrecta, intente nuevamente.', 'error').then((r) => reloadOnDismiss(r))
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
      <Form class="space-y-6" @submit="onSubmit" :validation-schema="schema">
        <div class="flex items-center justify-center">
          <img src="/images/logo_white.png" width="160" height="160">
        </div>
        <div class="mb-4">
          <label class="block mb-2 text-sm font-medium text-gray-900 ">Nombre de Usuario</label>
          <div class="flex">
  <span
      class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md ">
<svg class="w-6 h-6 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
     fill="currentColor" viewBox="0 0 24 24">
  <path fill-rule="evenodd"
        d="M12 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4h-4Z"
        clip-rule="evenodd"/>
</svg>
  </span>
            <Field type="text" id="username" name="username" :validate-on-input="true"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-none rounded-e-lg focus:ring-green-800 focus:border-green-800 block w-full p-2.5 "/>


          </div>
          <ErrorMessage name="username" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"/>
        </div>
        <div class="mb-4">
          <label class="block mb-2 text-sm font-medium text-gray-900 ">Contraseña</label>
          <div class="flex">
  <span
      class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md ">
<svg class="w-6 h-6 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
     fill="none" viewBox="0 0 24 24">
  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2"/>
</svg>
  </span>
            <Field type="password" id="password" name="password" :validate-on-input="true"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-none rounded-e-lg focus:ring-green-800 focus:border-green-800 block w-full p-2.5 "/>

          </div>
          <ErrorMessage name="password" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"/>
        </div>
        <div class="flex items-start">
          <div class="flex items-start">
            <div class="flex items-center h-5">
              <input type="checkbox" id="rememberMe" name="rememberMe" v-model="rememberMe"
                     class="w-4 h-4 border border-gray-300 rounded-sm bg-gray-50 focus:ring-3 focus:ring-blue-300"
              />
            </div>
            <label class="ms-2 text-sm font-medium text-gray-900">Recuérdame</label>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <button type="reset" :disabled="submitting"
                  class="px-5 py-2.5 text-sm font-medium text-white inline-flex items-center bg-blue-600 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg text-center justify-center disabled:bg-gray-600 disabled:cursor-not-allowed">
            Limpiar
          </button>

          <button type="submit" :disabled="submitting"
                  class="px-5 py-2.5 text-sm font-medium text-white inline-flex items-center bg-green-600 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg text-center justify-center disabled:bg-gray-600 disabled:cursor-not-allowed">
            {{ submitting ? 'Cargando...' : 'Iniciar Sesión' }}
          </button>
        </div>
        <div class="text-sm font-medium text-gray-500">
          ¿Olvido su contraseña? <a class="text-blue-700 hover:underline" :disabled="submitting">Click aquí</a>
        </div>
      </Form>

    </div>
  </main>
</template>