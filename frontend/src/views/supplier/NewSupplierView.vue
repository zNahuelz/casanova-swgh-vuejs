<script setup>
import {onMounted, ref} from 'vue';
import {RouterView, useRouter} from 'vue-router';
import * as yup from 'yup';
import {ErrorMessage, Field, Form, useForm} from 'vee-validate';
import {useAuthStore} from "@/stores/auth.js";
import {SupplierService} from "@/services/supplier-service.js";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import Swal from "sweetalert2";
import {reloadOnDismiss} from "@/utils/helpers.js";


const submitting = ref(false);
const authStore = useAuthStore();
const supplierForm = ref();
const router = useRouter();

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

async function onSubmit(values) {
  submitting.value = true;
  const payload = {
    ...values,
    ruc: String(values.ruc),
    phone: String(values.phone),
    created_by: authStore.getUserId(),
  }
  try {
    const response = await SupplierService.create(payload);
    Swal.fire(SM.SUCCESS_TAG, `${SM.SUPPLIER_CREATED} ID Asignado: ${response.supplier.id}`, 'success').then((r) => reloadOnDismiss(r));
  } catch (err) {
    if (err.errors.ruc) {
      supplierForm.value.setFieldValue('ruc', '');
      Swal.fire(EM.ERROR_TAG, EM.RUC_TAKEN, 'warning');
    } else {
      Swal.fire(EM.ERROR_TAG, EM.SERVER_ERROR, 'error').then((r) => reloadOnDismiss(r));
    }
  } finally {
    submitting.value = false;
  }
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - NUEVO PROVEEDOR'
});

function goBack() {
  router.back();
}
</script>
<template>
  <main class="flex flex-col items-center pt-5">
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm  min-w-150">
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-black text-center">NUEVO PROVEEDOR</h5>
      <Form class="space-y-3" @submit="onSubmit" :validation-schema="schema" ref="supplierForm">
        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Nombre</label>
          <Field type="text" id="name" name="name" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"/>
          <ErrorMessage name="name" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">RUC</label>
          <Field type="number" id="ruc" name="ruc" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"/>
          <ErrorMessage name="ruc" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Dirección</label>
          <Field type="text" id="address" name="address" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"/>
          <ErrorMessage name="address" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Teléfono de Contacto</label>
          <Field type="text" id="phone" name="phone" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"/>
          <ErrorMessage name="phone" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">E-Mail</label>
          <Field type="email" id="email" name="email" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"/>
          <ErrorMessage name="email" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-gray-900 ">Descripción</label>
          <Field type="text" id="description" name="description" :validate-on-input="true"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg  focus:ring-green-800 focus:border-green-800 w-full"/>
          <ErrorMessage name="description"
                        class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
        </div>


        <div class="flex justify-center mt-5">
          <div class="inline-flex rounded-md shadow-xs" role="group">
            <button @click="goBack" :disabled="submitting" type="button"
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