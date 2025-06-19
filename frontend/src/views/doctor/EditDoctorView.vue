<script setup>
import {onMounted, ref} from "vue";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {useRoute, useRouter} from "vue-router";
import {useAuthStore} from "@/stores/auth.js";
import {ErrorMessage, Field, Form} from "vee-validate";
import * as yup from "yup";
import {DoctorService} from "@/services/doctor-service.js";
import Swal from "sweetalert2";
import {reloadOnDismiss} from "@/utils/helpers.js";

const submitting = ref(false);
const router = useRouter();
const route = useRoute();
const authService = useAuthStore();
const doctorForm = ref();
const doctor = ref({});
const isLoading = ref(false);
const loadError = ref(false);

const schema = yup.object().shape({
  name: yup
      .string()
      .required('Debe ingresar un nombre.')
      .min(2, 'El nombre debe tener entre 2 y 30 carácteres.')
      .max(30, 'El nombre debe tener entre 2 y 30 carácteres.')
      .matches(/^.*\S.*$/, 'El nombre no puede ser solo espacios en blanco.')
      .matches(/^\S.*$/, 'El nombre no debe comenzar con espacios.')
      .matches(/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/),

  paternal_surname: yup
      .string()
      .required('Debe ingresar un apellido paterno.')
      .matches(/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/, 'El apellido paterno debe tener entre 2 y 30 carácteres.')
      .matches(/^.*\S.*$/, 'El apellido paterno no puede ser solo espacios en blanco.')
      .matches(/^\S.*$/, 'El apellido paterno no debe comenzar con espacios.')
      .min(2, 'El apellido paterno debe tener entre 2 y 30 carácteres.')
      .max(30, 'El apellido paterno debe tener entre 2 y 30 carácteres.'),

  maternal_surname: yup
      .string()
      .matches(/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/, 'El apellido materno debe tener entre 2 y 30 carácteres.')
      .matches(/^.*\S.*$/, 'El apellido materno no puede ser solo espacios en blanco.')
      .matches(/^\S.*$/, 'El apellido materno no debe comenzar con espacios.')
      .min(2, 'El apellido materno debe tener entre 2 y 30 carácteres.')
      .max(30, 'El apellido materno debe tener entre 2 y 30 carácteres.')
      .notRequired(),

  dni: yup
      .string()
      .required('Debe ingresar un DNI.')
      .matches(/^[0-9]{8,15}$/, 'El DNI solo debe contener números.')
      .min(8, 'El DNI debe tener entre 8 y 15 cifras.')
      .max(15, 'El DNI debe tener entre 8 y 15 cifras.'),

  email: yup
      .string()
      .required('Debe ingresar una dirección de E-Mail.')
      .email('El E-Mail debe cumplir el formato EMAIL@DOMINIO.COM.')
      .matches(/^(?=.{1,50}$)[^\s@]{1,64}@[^\s@]{1,255}\.[^\s@]{1,24}$/, 'El E-Mail debe cumplir el formato EMAIL@DOMINIO.COM.')
      .max(50, 'El E-Mail debe tener como máximo 50 carácteres.'),

  phone: yup
      .string()
      .required('Debe ingresar un número de teléfono.')
      .matches(/^\+?\d{6,15}$/, 'El teléfono debe contener solo números.')
      .min(6, 'El número de teléfono debe tener entre 6 y 15 carácteres.')
      .max(15, 'El número de teléfono debe tener entre 6 y 15 carácteres.'),

  address: yup
      .string()
      .required('Debe ingresar una dirección.')
      .matches(/^.*\S.*$/, 'La dirección no puede ser solo espacios en blanco.')
      .matches(/^\S.*$/, 'La dirección no debe comenzar con espacios.')
      .min(5, 'La dirección debe tener entre 5 y 100 carácteres.')
      .max(100, 'La dirección debe tener entre 5 y 100 carácteres.'),
});

async function loadDoctor(id) {
  isLoading.value = true;
  try {
    doctor.value = await DoctorService.getById(id);
    isLoading.value = false;
  } catch (err) {
    loadError.value = true;
    Swal.fire(EM.ERROR_TAG, EM.DOCTOR_NOT_FOUND, 'error').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        router.back();
      }
    });
  }
}

async function onSubmit(values) {
  submitting.value = true;
  if (values.maternal_surname.length <= 0 || values.maternal_surname === undefined) {
    values.maternal_surname = null;
  }
  const payload = {
    ...values,
    email: values.email.trim().toUpperCase(),
    updated_by: authService.getUserId(),
  }
  try {
    const response = await DoctorService.update(doctor.value.id, payload);
    Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then((r) => reloadOnDismiss(r));
  } catch (err) {
    if (err?.errors?.dni) {
      doctorForm.value.setFieldValue('dni', '');
      Swal.fire(EM.ERROR_TAG, EM.DNI_TAKEN, 'warning');
    } else if (err?.errors?.email) {
      doctorForm.value.setFieldValue('email', '');
      Swal.fire(EM.ERROR_TAG, EM.EMAIL_TAKEN, 'warning');
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
  document.title = 'ALTERNATIVA CASANOVA - GESTIONAR DOCTOR'
  const id = route.params.id;
  if (isNaN(id)) {
    router.back();
  }
  loadDoctor(id);
})
</script>

<template>
  <main class="flex flex-col items-center pt-5">
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm  min-w-150">
      <h5 v-if="!isLoading && !loadError" class="mb-2 text-2xl font-bold tracking-tight text-black text-center">EDITAR
        DOCTOR</h5>
      <div v-if="isLoading" class="container mt-5 mb-5 flex flex-col items-center">
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
        <h1 class="mt-5 text-2xl font-light">Cargando información...</h1>
      </div>
      <Form v-if="!isLoading && !loadError" ref="doctorForm" v-slot="{validate}" :validation-schema="schema"
            class="space-y-3"
            @submit="onSubmit">
        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Nombre</label>
          <Field id="name" :disabled="submitting" :model-value="doctor?.name" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                 name="name"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="name"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Apellido Paterno</label>
          <Field id="paternal_surname" :disabled="submitting" :model-value="doctor?.paternal_surname"
                 :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                 name="paternal_surname"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                        name="paternal_surname"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Apellido Materno</label>
          <Field id="maternal_surname" :disabled="submitting" :model-value="doctor?.maternal_surname"
                 :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                 name="maternal_surname"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                        name="maternal_surname"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">DNI</label>
          <Field id="dni" :disabled="submitting" :model-value="doctor?.dni" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                 name="dni"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="dni"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">E-Mail</label>
          <Field id="email" :disabled="submitting" :model-value="doctor?.email" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                 name="email"
                 type="email"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="email"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Teléfono de Contacto</label>
          <Field id="phone" :disabled="submitting" :model-value="doctor?.phone" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                 name="phone"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="phone"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Dirección</label>
          <Field id="address" :disabled="submitting" :model-value="doctor?.address" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                 name="address"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                        name="address"></ErrorMessage>
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
