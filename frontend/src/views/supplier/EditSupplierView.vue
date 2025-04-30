<script setup>
import {useRoute, useRouter} from 'vue-router';
import {onMounted, ref} from 'vue';
import {SupplierService} from "@/services/supplier-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {ErrorMessage, Field, Form, useForm} from "vee-validate";
import * as yup from "yup";
import {formatDate, reloadOnDismiss} from "@/utils/helpers.js";
import {useAuthStore} from "@/stores/auth.js";


const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const supplier = ref(null);
const isLoading = ref(true);
const loadError = ref(false);
const submitting = ref(false);
const editSupplierForm = ref();

const schema = yup.object().shape({
  name: yup
      .string()
      .required('Debe ingresar un nombre.')
      .min(2, 'El nombre debe tener entre 2 y 150 carácteres.')
      .max(150, 'El nombre debe tener entre 2 y 150 carácteres.'),

  ruc: yup
      .string()
      .required('Debe ingresar un RUC.')
      .matches(/^(10|20)[0-9]{9}$/, 'El RUC debe comenzar con 10 o 20 y tener máximo 11 carácteres.')
      .min(11, 'El RUC debe tener 11 carácteres.')
      .max(11, 'El RUC debe tener 11 carácteres.'),

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

  email: yup
      .string()
      .required('Debe ingresar una dirección de E-Mail.')
      .email('El E-Mail debe cumplir el formato EMAIL@DOMINIO.COM.')
      .max(50, 'El E-Mail debe tener como máximo 50 carácteres.'),

  description: yup
      .string()
      .max(150, 'La descripción debe tener como máximo 150 carácteres.'),
});


onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - EDITAR PROVEEDOR';
  const id = route.params.id;
  if (isNaN(id)) {
    router.back();
  }
  loadSupplier(id);
});

async function onSubmit(values) {
  submitting.value = true;
  const payload = {
    ...values,
    updated_by: authStore.getUserId(),
  }
  try{
    const response = await SupplierService.update(supplier.value.id, payload);
    Swal.fire(SM.SUCCESS_TAG,response.message, 'success').then((r) => reloadOnDismiss(r));
  }
  catch(err){
    if(err.errors.ruc){
      editSupplierForm.value.setFieldValue('ruc', '');
      Swal.fire(EM.ERROR_TAG, EM.RUC_TAKEN,'warning');
    }
    else {
      Swal.fire(EM.ERROR_TAG, EM.SERVER_ERROR,'error').then((r) => reloadOnDismiss(r));
    }
  }
  finally {
    submitting.value = false;
  }
}

const loadSupplier = async (id) => {
  try {
    supplier.value = await SupplierService.getById(id);
    loadError.value = false;
    isLoading.value = false;
  } catch (err) {
    loadError.value = true;
    Swal.fire(EM.ERROR_TAG, EM.SUPPLIER_NOT_FOUND, 'error').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        router.back();
      }
    });
  }
}

function goBack() {
  router.back();
}
</script>

<template>
  <main class="flex flex-col items-center pt-5">
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm  min-w-150">
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-black text-center" v-if="!isLoading && !loadError">EDITAR
        PROVEEDOR</h5>
      <div class="container mt-5 mb-5 flex flex-col items-center" v-if="isLoading">
        <div role="status">
          <svg aria-hidden="true" class="inline w-30 h-30 text-gray-200 animate-spin  fill-green-600"
               viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
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
      <Form class="space-y-3" v-if="!isLoading && !loadError" :validation-schema="schema" @submit="onSubmit" ref="editSupplierForm">
        <div class="border-b pb-4 border-gray-200">
          <h6 class="text-lg font-semibold text-gray-900 mb-4">Información de Auditoría</h6>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-500">Fecha de Registro</label>
                <div class="mt-1 text-sm text-gray-900">
                  {{ formatDate(supplier?.created_at) }}
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Registrado por</label>
                <div class="mt-1 text-sm text-gray-900">
                  {{ !supplier?.created_by ? 'INSERTADO EN BASE DE DATOS' : supplier.created_by_name }}
                </div>
              </div>
            </div>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-500">Última Modificación</label>
                <div class="mt-1 text-sm text-gray-900">
                  {{ formatDate(supplier?.updated_at) }}
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Modificado por</label>
                <div class="mt-1 text-sm text-gray-900">
                  {{ !supplier?.updated_by ? '---' : supplier.updated_by_name }}
                </div>
              </div>
            </div>
          </div>
        </div>
        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">ID</label>
          <input type="text" :value="supplier?.id" disabled
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"/>
        </div>
        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Nombre</label>
          <Field type="text" id="name" name="name" :validate-on-input="true" :model-value="supplier?.name"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"/>
          <ErrorMessage name="name" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
        </div>
        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">RUC</label>
          <Field type="number" id="ruc" name="ruc" :validate-on-input="true" :model-value="supplier?.ruc"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"/>
          <ErrorMessage name="ruc" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
        </div>
        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Dirección</label>
          <Field type="text" id="address" name="address" :validate-on-input="true" :model-value="supplier?.address"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"/>
          <ErrorMessage name="address" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
        </div>
        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Teléfono de Contacto</label>
          <Field type="text" id="phone" name="phone" :validate-on-input="true" :model-value="supplier?.phone"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"/>
          <ErrorMessage name="phone" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
        </div>
        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">E-Mail</label>
          <Field type="email" id="email" name="email" :validate-on-input="true" :model-value="supplier?.email"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"/>
          <ErrorMessage name="email" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
        </div>
        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Descripción</label>
          <Field type="text" id="description" name="description" :validate-on-input="true"
                 :model-value="supplier?.description"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"/>
          <ErrorMessage name="description"
                        class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
        </div>
        <div class="flex justify-center mt-5">
          <div class="inline-flex rounded-md shadow-xs" role="group">
            <button :disabled="submitting" @click="goBack"
                         class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed">
              <i class="bi bi-x-circle w-3 h-3 me-2 flex items-center justify-center"></i>
              Cancelar
            </button>
            <button type="reset" :disabled="submitting"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 disabled:bg-gray-200 disabled:cursor-not-allowed">
              <i class="bi bi-arrow-clockwise w-3 h-3 me-2 flex items-center justify-center"></i>
              Limpiar
            </button>
            <button type="submit" :disabled="submitting"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed">
              <i class="bi bi-floppy-fill w-3 h-3 me-2 flex items-center justify-center"></i>
              {{ submitting ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </div>
      </Form>
    </div>
  </main>
</template>

