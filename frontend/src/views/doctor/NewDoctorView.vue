<script setup>
import {computed, onMounted, ref} from "vue";
import {ErrorMessage, Field, Form} from "vee-validate";
import {useRouter} from "vue-router";
import {
  DEFAULT_DOCTOR_AVAILABILITIES as WORK_DAYS,
  ERROR_MESSAGES as EM,
  SUCCESS_MESSAGES as SM
} from "@/utils/constants.js";
import WeekdayConfigCard from "@/components/doctor/WeekdayConfigCard.vue";
import * as yup from "yup";
import {useAuthStore} from "@/stores/auth.js";
import Swal from "sweetalert2";
import {reloadOnDismiss} from "@/utils/helpers.js";
import {DoctorService} from "@/services/doctor-service.js";
import {SettingService} from "@/services/setting-service.js";


const isLoading = ref(false);
const submitting = ref(false);
const weekendsAllowed = ref('false'); //TODO: Get config value from db.
const workDays = ref([...WORK_DAYS]);
const doctorForm = ref();
const router = useRouter();
const authService = useAuthStore();
const CONFIG_KEY = ref('ESTADO_TRABAJO_FINDES');

const schema = yup.object().shape({
  name: yup
      .string()
      .required('Debe ingresar un nombre.')
      .min(2, 'El nombre debe tener entre 2 y 30 carácteres.')
      .max(30, 'El nombre debe tener entre 2 y 30 carácteres.')
      .matches(/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/),

  paternal_surname: yup
      .string()
      .required('Debe ingresar un apellido paterno.')
      .matches(/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/, 'El apellido paterno debe tener entre 2 y 30 carácteres.')
      .min(2, 'El apellido paterno debe tener entre 2 y 30 carácteres.')
      .max(30, 'El apellido paterno debe tener entre 2 y 30 carácteres.'),

  maternal_surname: yup
      .string()
      .matches(/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/, 'El apellido materno debe tener entre 2 y 30 carácteres.')
      .min(2, 'El apellido materno debe tener entre 2 y 30 carácteres.')
      .max(30, 'El apellido materno debe tener entre 2 y 30 carácteres.'),

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
      .min(5, 'La dirección debe tener entre 5 y 100 carácteres.')
      .max(100, 'La dirección debe tener entre 5 y 100 carácteres.'),
});

async function onSubmit(values) {
  if (values.maternal_surname === undefined) {
    values.maternal_surname = '';
  }
  if (weekdaysValid) {
    submitting.value = true;
    const payload = {
      ...values,
      availabilities: workDays.value,
      email: values.email.trim().toUpperCase(),
      created_by: authService.getUserId(),
    }
    try {
      const response = await DoctorService.create(payload);
      Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then((r) => reloadOnDismiss(r));
    } catch (err) {
      if (err?.errors.dni) {
        doctorForm.value.setFieldValue('dni', '');
        Swal.fire(EM.ERROR_TAG, EM.DNI_TAKEN, 'warning');
      } else if (err?.errors.email) {
        doctorForm.value.setFieldValue('email', '');
        Swal.fire(EM.ERROR_TAG, EM.EMAIL_TAKEN, 'warning');
      } else {
        Swal.fire(EM.ERROR_TAG, EM.SERVER_ERROR, 'error').then((r) => reloadOnDismiss(r));
      }
    } finally {
      submitting.value = false;
    }
  } else {
    Swal.fire(EM.ERROR_TAG, EM.INVALID_WORK_SCHEDULE, 'error').then((r) => reloadOnDismiss(r));
  }
}

async function getConfig() {
  isLoading.value = true;
  try {
    const response = await SettingService.getByKey(CONFIG_KEY.value);
    weekendsAllowed.value = response.value;
    removeWeekends();
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, EM.WEEKENDS_CONFIG_NOT_FOUND, 'error');
  } finally {
    isLoading.value = false;
  }
}

function handleWeekdayUpdate(updatedDay) {
  const idx = workDays.value.findIndex(d => d.weekday === updatedDay.weekday)
  if (idx !== -1) {
    // replace entire object so Vue notices
    workDays.value.splice(idx, 1, {...updatedDay})
  }
}

const weekdaysValid = computed(() => {
  const allValid = workDays.value.every(d => {
    if (!d.start_time || !d.end_time || d.start_time >= d.end_time) return false;
    if (d.break_start && d.break_end) {
      if (d.break_start >= d.break_end) return false;
      if (d.break_start < d.start_time || d.break_end > d.end_time) return false;
    }
    return true;
  });
  
  const anyActive = workDays.value.some(d => d.is_active);

  return allValid && anyActive;
});

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - NUEVO DOCTOR'
  getConfig();
});

function removeWeekends() {
  if (weekendsAllowed.value === 'false') {
    for (let i = workDays.value.length - 1; i >= 0; i--) {
      if (workDays.value[i].weekday === 6 || workDays.value[i].weekday === 7) {
        workDays.value.splice(i, 1);
      }
    }
  }
}
</script>

<template>
  <main class="flex flex-col items-center pt-5 relative">
    <div class="container px-12 mx-auto">
      <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm w-full">
        <h5 v-if="!isLoading" class="mb-5 text-2xl font-bold tracking-tight text-black text-center">NUEVO DOCTOR</h5>
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
          <h1 class="mt-5 text-2xl font-light">Cargando configuración...</h1>
        </div>

        <div v-if="!isLoading" class="grid grid-cols-4 gap-4 ms-5 me-5">
          <Form ref="doctorForm" v-slot="{meta}" :validation-schema="schema"
                class="col-span-2 grid grid-cols-2 gap-4" @submit="onSubmit">
            <div>
              <div class="mb-4">
                <label class="block mb-1 text-sm font-medium text-gray-900">Nombre</label>
                <Field id="name" :disabled="submitting" :validate-on-input="true"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                       name="name" type="text"/>
                <ErrorMessage
                    class="block min-h-[1.25rem] mt-1 text-sm text-red-600 font-medium"
                    name="name"
                />
              </div>

              <div class="mb-4">
                <label class="block mb-1 text-sm font-medium text-gray-900">Apellido Paterno</label>
                <Field id="paternal_surname" :disabled="submitting" :validate-on-input="true"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                       name="paternal_surname" type="text"/>
                <ErrorMessage
                    class="block min-h-[1.25rem] mt-1 text-sm text-red-600 font-medium"
                    name="paternal_surname"
                />
              </div>

              <div class="mb-4">
                <label class="block mb-1 text-sm font-medium text-gray-900">Correo Electrónico</label>
                <Field id="email" :disabled="submitting" :validate-on-input="true"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                       name="email" type="email"/>
                <ErrorMessage
                    class="block min-h-[1.25rem] mt-1 text-sm text-red-600 font-medium"
                    name="email"
                />
              </div>
            </div>

            <div>
              <div class="mb-4">
                <label class="block mb-1 text-sm font-medium text-gray-900">DNI</label>
                <Field id="dni" :disabled="submitting" :validate-on-input="true"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                       name="dni" type="text"/>
                <ErrorMessage
                    class="block min-h-[1.25rem] mt-1 text-sm text-red-600 font-medium"
                    name="dni"
                />
              </div>


              <div class="mb-4">
                <label class="block mb-1 text-sm font-medium text-gray-900">Apellido Materno</label>
                <Field id="maternal_surname" :disabled="submitting" :validate-on-input="true"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                       name="maternal_surname" type="text"/>
                <ErrorMessage
                    class="block min-h-[1.25rem] mt-1 text-sm text-red-600 font-medium"
                    name="maternal_surname"
                />
              </div>

              <div class="mb-4">
                <label class="block mb-1 text-sm font-medium text-gray-900">Teléfono</label>
                <Field id="phone" :disabled="submitting" :validate-on-input="true"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                       name="phone" type="text"/>
                <ErrorMessage
                    class="block min-h-[1.25rem] mt-1 text-sm text-red-600 font-medium"
                    name="phone"
                />
              </div>
            </div>
            <div class="col-span-2">
              <div class="mb-4">
                <label class="block mb-1 text-sm font-medium text-gray-900">Dirección</label>
                <Field id="address" :disabled="submitting" :validate-on-input="true"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                       name="address" type="text"/>
                <ErrorMessage class="block min-h-[1.25rem] mt-1 text-sm text-red-600 font-medium" name="address"/>
              </div>
            </div>

            <div class="col-span-2 flex justify-center">
              <div class="inline-flex rounded-md shadow-xs" role="group">
                <button :disabled="submitting"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                        type="button"
                        @click="router.back()">
                  <i class="bi bi-x-circle w-3 h-3 me-2 flex items-center justify-center"></i>
                  Cancelar
                </button>
                <button :disabled="submitting"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                        type="reset">
                  <i class="bi bi-arrow-clockwise w-3 h-3 me-2 flex items-center justify-center"></i>
                  Limpiar
                </button>
                <button :disabled="submitting || !meta.valid || !weekdaysValid"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                        type="submit">
                  <i class="bi bi-floppy-fill w-3 h-3 me-2 flex items-center justify-center"></i>
                  {{ submitting ? 'Guardando...' : 'Guardar' }}
                </button>
              </div>
            </div>
          </Form>

          <div v-show="!submitting" class="col-span-2 ms-5 ps-5 overflow-auto max-h-[370px]">
            <weekday-config-card v-for="wd in workDays" :key="wd.weekday" :weekdayInfo="wd"
                                 @update:weekdayInfo="handleWeekdayUpdate"></weekday-config-card>
          </div>
        </div>

        <div v-if="!isLoading" class="container text-center mt-5">
          <p :class="{'text-green-600': weekdaysValid, 'text-red-600': !weekdaysValid}" class="mt-2 font-bold">
            {{
              weekdaysValid ? '- Horario de Trabajo Válido -' : '- Horario de Trabajo Inválido: Algunos días tienen horarios incorrectos -'
            }}
          </p>
        </div>
      </div>
    </div>
  </main>
</template>