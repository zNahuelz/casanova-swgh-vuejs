<script setup>
import {onMounted, ref} from "vue";
import {ErrorMessage, Field, Form} from "vee-validate";
import * as yup from "yup";
import {useRoute, useRouter} from "vue-router";
import dayjs from "dayjs";
import {reloadOnDismiss, validateDni} from "@/utils/helpers.js";
import {PatientService} from "@/services/patient-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {useAuthStore} from "@/stores/auth.js";

const submitting = ref(false);
const router = useRouter();
const route = useRoute();
const authService = useAuthStore();
const today = dayjs().startOf('day').subtract(1, 'day');
const patientDni = ref('');
const patientForm = ref();

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
      .matches(/^\+?\d{6,15}$/, 'El teléfono debe contener solo números y maximo 15 carácteres.')
      .min(6, 'El número de teléfono debe tener entre 6 y 15 carácteres.')
      .max(15, 'El número de teléfono debe tener entre 6 y 15 carácteres.'),

  address: yup
      .string()
      .required('Debe ingresar una dirección.')
      .matches(/^.*\S.*$/, 'La dirección no puede ser solo espacios en blanco.')
      .matches(/^\S.*$/, 'La dirección no debe comenzar con espacios.')
      .min(5, 'La dirección debe tener entre 5 y 100 carácteres.')
      .max(100, 'La dirección debe tener entre 5 y 100 carácteres.'),

  birth_date: yup
      .date()
      .max(today.toDate(), 'La fecha de nacimiento debe estar en el pasado.')
      .required('Debe ingresar una fecha de nacimiento.')
});

async function onSubmit(values) {
  submitting.value = true;
  try {
    const payload = {
      ...values,
      email: values.email.trim().toUpperCase(),
      maternal_surname: values.maternal_surname === undefined ? '' : values.maternal_surname,
      created_by: authService.getUserId(),
    }
    const response = await PatientService.create(payload);
    Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then((r) => {
      if (r.isDismissed || r.dismiss || r.isConfirmed) {
        router.push({name: 'patient-list', query: {}});
      }
    });
  } catch (err) {
    if (err?.errors?.dni) {
      patientForm.value.setFieldValue('dni', '');
      patientDni.value = '';
      Swal.fire(EM.ERROR_TAG, EM.PATIENT_DNI_TAKEN, 'warning');
    } else if (err?.errors?.email) {
      patientForm.value.setFieldValue('email', '');
      Swal.fire(EM.ERROR_TAG, EM.PATIENT_EMAIL_TAKEN, 'warning');
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
  document.title = 'ALTERNATIVA CASANOVA - NUEVO PACIENTE'
  const dni = route.query.dni || '';
  if (dni !== '') {
    validateDni(dni) ? patientDni.value = dni : patientDni.value = '';
  }
});
</script>

<template>
  <main class="flex flex-col items-center pt-5">
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm  min-w-150">
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-black text-center">NUEVO PACIENTE</h5>
      <Form ref="patientForm" v-slot="{validate}" :validation-schema="schema" class="space-y-3" @submit="onSubmit">
        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Nombre</label>
          <Field id="name" :disabled="submitting" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                 name="name"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="name"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Apellido Paterno</label>
          <Field id="paternal_surname" :disabled="submitting" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                 name="paternal_surname"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                        name="paternal_surname"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Apellido Materno</label>
          <Field id="maternal_surname" :disabled="submitting" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                 name="maternal_surname"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                        name="maternal_surname"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Fecha de Nacimiento</label>
          <Field id="birth_date" :disabled="submitting" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                 name="birth_date"
                 type="date"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                        name="birth_date"></ErrorMessage>
        </div>


        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">DNI</label>
          <Field id="dni" v-model="patientDni" :disabled="submitting" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                 name="dni"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="dni"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">E-Mail</label>
          <Field id="email" :disabled="submitting" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"
                 name="email"
                 type="email"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="email"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Teléfono de Contacto</label>
          <Field id="phone" :disabled="submitting" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                 name="phone"
                 type="text"/>
          <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="phone"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Dirección</label>
          <Field id="address" :disabled="submitting" :validate-on-input="true"
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
