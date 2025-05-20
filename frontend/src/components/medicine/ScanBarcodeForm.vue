<script setup>
import {ErrorMessage, Field, Form, validate} from "vee-validate";
import * as yup from "yup";
import {ref} from "vue";
import {MedicineService} from "@/services/medicine-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM} from "@/utils/constants.js";
import {reloadOnDismiss, reloadPage} from "@/utils/helpers.js";
import {useRouter} from "vue-router";

const isLoading = ref(false);
const emit = defineEmits(['barcodeGenerated']);
const loadingMessage = ref('');
const router = useRouter();


const schema = yup.object({
  barcode: yup.string().min(8, 'El código de barras debe tener entre 8 y 30 carácteres.').max(30, 'El código de barras debe tener entre 8 y 30 carácteres.').matches(/^[A-Za-z0-9]{8,30}$/, 'El código de barras debe contener solo números y letras.').required('Debe ingresar un código de barras o generar uno aleatorio.'),
});


async function loadRandomBarcode() {
  loadingMessage.value = 'Generando código de barras...'
  isLoading.value = true;
  try {
    const response = await MedicineService.generateRandomBarcode();
    isLoading.value = false;
    emit('barcodeGenerated', response.barcode);
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, EM.BARCODE_GENERATION_ERROR, 'error').then((r) => reloadOnDismiss(r))
  }
}

async function validateBarcode(value) {
  loadingMessage.value = 'Validando código de barras...'
  isLoading.value = true;
  try {
    const response = await MedicineService.getByBarcode(value.barcode);
    let text = `El código de barras ingresado pertenece al siguiente producto: </br>ID: ${response.id} </br>NOMBRE: ${response.name} </br> CÓDIGO DE BARRAS: ${response.barcode} </br> ¿Desea modificar el stock del producto mencionado?`
    Swal.fire({
      title: 'Información',
      html: text,
      icon: 'question',
      showCancelButton: true,
      cancelButtonText: 'NO',
      confirmButtonText: 'SI',
      confirmButtonColor: '#008236',
      cancelButtonColor: '#e7000b',
    }).then((op) => {
      if (op.isConfirmed) {
        router.replace('/') //TODO: Redirect to edit-medicine/BARCODE or update stock...
      } else {
        reloadPage();
      }
    });
  } catch (err) {
    emit('barcodeGenerated', value.barcode)
  }
}
</script>

<template>
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
    <h1 class="mt-5 text-2xl font-light">{{ loadingMessage }}</h1>
  </div>
  <Form class="max-w-md mx-auto mt-4" @submit="validateBarcode" :validation-schema="schema" v-if="!isLoading">
    <div class="relative">
      <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
             fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
        </svg>
      </div>
      <Field
          type="text"
          id="barcode"
          name="barcode"
          class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-green-500 focus:border-green-500"
          placeholder="Ingrese código de barras"
      />
      <div class="absolute flex gap-2 end-2.5 bottom-2.5">
        <button
            type="button" :disabled="isLoading" @click="loadRandomBarcode"
            class="text-gray-700 bg-yellow-400 hover:bg-yellow-200 focus:ring-4 focus:outline-none focus:ring-yellow-400 font-medium rounded-lg text-sm px-4 py-2"
        >
          Aleatorio
        </button>
        <button
            type="submit" :disabled="isLoading"
            class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2"
        >
          Asignar
        </button>
      </div>
    </div>
    <ErrorMessage name="barcode" class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"></ErrorMessage>
  </Form>
</template>

