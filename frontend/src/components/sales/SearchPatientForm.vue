<script setup>
import {ErrorMessage, Field, Form} from "vee-validate";
import {ref} from "vue";
import {useRouter} from "vue-router";
import * as yup from "yup";
import {PatientService} from "@/services/patient-service.js";
import Swal from "sweetalert2";
import {SUCCESS_MESSAGES as SM, ERROR_MESSAGES as EM} from "@/utils/constants.js";
import {reloadPage} from "@/utils/helpers.js";

const isLoading = ref(false);
const emit = defineEmits(['buyerInfo']);
const loadingMessage = ref('');
const router = useRouter();


const schema = yup.object({
  dni: yup
      .string()
      .required('Debe ingresar un DNI.')
      .matches(/^[0-9]{8,15}$/, 'El DNI solo debe contener números.')
      .min(8, 'El DNI debe tener entre 8 y 15 cifras.')
      .max(15, 'El DNI debe tener entre 8 y 15 cifras.'),
});
//TODO: CONTINUE ** 19/06/2025 ??? Seems to be OK.
async function onSubmit(values) {
  isLoading.value = true;
  try{
    loadingMessage.value = 'Buscando paciente...';
    const response = await PatientService.getByDni(values.dni.trim());
    emit('buyerInfo',response);
  }
  catch(err){
    //** TODO: IF RESPONSE ISNT 404 THROW SERVER ERROR.
    Swal.fire({
      title: 'Información',
      html: `<p class="font-light">Paciente con DNI: ${values.dni.trim()} no encontrado.
    <br> <span class="font-bold">¿Desea registrarlo?</span> <br>
    <strong class="text-red-600">Recuerde que puede realizar ventas ingresando DNI 0 siempre y cuando el valor de venta no supere los 700 soles.</strong></p>`,
      icon: 'question',
      showCancelButton: true,
      cancelButtonText: 'NO',
      confirmButtonText: 'SI',
      confirmButtonColor: '#008236',
      cancelButtonColor: '#e7000b',
    }).then((op) => {
      if (op.isConfirmed) {
        router.push({name: 'new-patient', query: {dni: values.dni}});
      } else {
        reloadPage();
      }
    });
  }
}
</script>

<template>
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
    <h1 class="mt-5 text-2xl font-light">{{ loadingMessage }}</h1>
  </div>
  <Form v-if="!isLoading" :validation-schema="schema" class="max-w-md mx-auto mt-4" @submit="onSubmit">
    <div class="relative">
      <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
        <svg aria-hidden="true" class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none"
             viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2"/>
        </svg>
      </div>
      <Field
          id="dni" :validate-on-input="true"
          class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-green-500 focus:border-green-500"
          name="dni"
          placeholder="Ingrese DNI del comprador."
          type="text"
      />
      <div class="absolute flex gap-2 end-2.5 bottom-2.5">
        <button
            :disabled="isLoading" class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2"
            type="submit"
        >
          Buscar
        </button>
      </div>
    </div>
    <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="dni"></ErrorMessage>
  </Form>
</template>