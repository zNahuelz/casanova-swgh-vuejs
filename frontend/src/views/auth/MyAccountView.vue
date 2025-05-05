<script setup>
import {onMounted, ref} from "vue";
import {UserService} from "@/services/user-service.js";
import {useAuthStore} from "@/stores/auth.js";
import {formatDate, reloadPage} from "@/utils/helpers.js";
import {ErrorMessage, Field, Form} from "vee-validate";

const isLoading = ref(false);
const isAdmin = ref(false);
const loadError = ref(false);
const accountData = ref({});
const profileData = ref({});
const authService = useAuthStore();
const submitting = ref(false);


async function loadProfile() {
  isLoading.value = true;
  try {
    const response = await UserService.getCurrenUserProfile();
    if (authService.getTokenDetails().role === 'ADMINISTRADOR') {
      accountData.value = response.user;
    } else if (authService.getTokenDetails().role === 'DOCTOR') {
      accountData.value = response.user;
      profileData.value = response.doctor;
    } else {
      accountData.value = response.user;
      profileData.value = response.worker;
    }
    //console.log(accountData.value);
    //console.log(profileData.value);
  } catch (err) {
    //console.log(err);
    loadError.value = true;
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - MI CUENTA';
  loadProfile();
  isAdmin.value = authService.getTokenDetails().role === 'ADMINISTRADOR';
});
</script>

<template>
  <div v-if="isLoading" class="h-screen flex justify-center items-center bg-slate-200">
    <div class="w-full max-w-sm p-4 bg-white border border-gray-200 rounded-lg shadow-lg sm:p-6 md:p-8">
      <div class="container mt-5 mb-5 flex flex-col items-center">
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
        <h1 class="mt-5 text-2xl font-light">Cargando perfil...</h1>
      </div>
    </div>
  </div>

  <div v-else class="h-screen flex justify-center items-center bg-slate-200">
    <div class="w-full max-w-lg bg-white border border-gray-200 rounded-lg shadow-sm col">
      <div class="flex flex-col items-center pb-10 pt-10">
        <img alt="User Icon" class="w-24 h-24 mb-3 rounded-full shadow-lg" src="/images/user_icon_transparent.png"/>
        <h5 v-if="isAdmin" class="mb-1 text-xl font-medium text-gray-900">Hola, {{ accountData.username }}</h5>
        <h5 v-else class="mb-1 text-xl font-medium text-gray-900">Hola,
          {{ profileData.name + ' ' + profileData.paternal_surname }}</h5>
        <div class="grid grid-cols-2 gap-1 mt-3 ps-5 pe-5">
          <span class="col text-md text-gray-500"><span
              class="font-bold text-black">USUARIO: </span> {{ accountData.username || 'NO DEFINIDO' }}</span>
          <span class="col text-md text-gray-500"><span
              class="font-bold text-black">ROL: </span>{{ accountData.role?.name || 'NO DEFINIDO' }}</span>
          <span v-if="isAdmin" class="col-span-full text-md text-gray-500"><span
              class="font-bold text-black">E-MAIL: </span>{{ accountData.email }}</span>
          <span v-if="!isAdmin" class="col text-md text-gray-500"><span
              class="font-bold text-black">NOMBRE: </span>{{ profileData.name }}</span>
          <span v-if="!isAdmin" class="col text-md text-gray-500"><span class="font-bold text-black">APELLIDOS: </span>{{
              profileData.paternal_surname + ' ' + profileData.maternal_surname
            }}</span>
          <span v-if="!isAdmin" class="col text-md text-gray-500"><span
              class="font-bold text-black">DNI: </span>{{ profileData.dni }}</span>
          <span v-if="!isAdmin" class="col text-md text-gray-500"><span
              class="font-bold text-black">TELÉFONO: </span>{{ profileData.phone }}</span>
          <span v-if="!isAdmin && authService.getTokenDetails().role === 'DOCTOR'"
                class="col-span-full text-md text-gray-500"><span
              class="font-bold text-black">FECHA DE CONTRATO: </span>{{ formatDate(profileData.created_at) }}</span>
          <span v-if="!isAdmin && authService.getTokenDetails().role !== 'DOCTOR'"
                class="col-span-full text-md text-gray-500"><span
              class="font-bold text-black">FECHA DE CONTRATO: </span>{{ formatDate(profileData.hiring_date) }}</span>
          <span v-if="!isAdmin" class="col-span-full text-md text-gray-500"><span
              class="font-bold text-black">E-MAIL: </span>{{ profileData.email }}</span>
          <span v-if="!isAdmin" class="col-span-full text-md text-gray-500"><span class="font-bold text-black">DIRECCIÓN: </span>{{
              profileData.address
            }}</span>
        </div>

        <div class="flex justify-center mt-5">
          <div class="inline-flex rounded-md shadow-xs" role="group">
            <button :disabled="submitting" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                    type="button">
              <i class="bi bi-person-bounding-box w-3 h-3 me-2 flex items-center justify-center"></i>
              {{ authService.getTokenDetails().role === 'ADMINISTRADOR' ? 'Cambiar usuario' : 'Modificar datos personales' }}
            </button>
            <!--TODO: Admin debe cambiar nombre de usuario. Trabajador: Direccion y telefono-->
          </div>
        </div>
      </div>
    </div>


    <div class="w-full max-w-lg bg-white border border-gray-200 rounded-lg shadow-sm  ms-5">
      <div class="flex flex-col items-center pb-10 pt-10">
        <h5 class="mb-1 text-xl font-medium text-gray-900 mb-3">Actualizar Credenciales</h5>
        <Form class="flex flex-col items-center">
          <div class="w-full mb-2">
            <label class=" mb-1 text-sm font-medium text-gray-900 ">Correo Electrónico</label>
            <Field id="email" :disabled="submitting" :validate-on-input="true" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full" name="email"
                   type="email"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="email"></ErrorMessage>
          </div>

          <div class="w-full mb-2">
            <label class=" mb-1 text-sm font-medium text-gray-900 ">Contraseña Actual</label>
            <Field id="currentPassword" :disabled="submitting" :validate-on-input="true" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                   name="currentPassword"
                   type="password"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="email"></ErrorMessage>
          </div>

          <div class="w-full mb-2">
            <label class=" mb-1 text-sm font-medium text-gray-900 ">Nueva Contraseña</label>
            <Field id="passwordOne" :disabled="submitting" :validate-on-input="true" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full" name="passwordOne"
                   type="password"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="email"></ErrorMessage>
          </div>

          <div class="w-full">
            <label class=" mb-1 text-sm font-medium text-gray-900 ">Repetir Contraseña</label>
            <Field id="passwordTwo" :disabled="submitting" :validate-on-input="true" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full" name="passwordTwo"
                   type="password"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="email"></ErrorMessage>
          </div>

          <div class="flex justify-center mt-5">
            <div class="inline-flex rounded-md shadow-xs" role="group">
              <button :disabled="submitting" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed" type="button"
                      @click="reloadPage()">
                <i class="bi bi-x-circle w-3 h-3 me-2 flex items-center justify-center"></i>
                Cancelar
              </button>
              <button :disabled="submitting" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                      type="reset">
                <i class="bi bi-arrow-clockwise w-3 h-3 me-2 flex items-center justify-center"></i>
                Limpiar
              </button>
              <button :disabled="submitting" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                      type="submit">
                <i class="bi bi-floppy-fill w-3 h-3 me-2 flex items-center justify-center"></i>
                {{ submitting ? 'Guardando...' : 'Guardar' }}
              </button>
            </div>
          </div>
        </Form>
      </div>
    </div>
  </div>
</template>

