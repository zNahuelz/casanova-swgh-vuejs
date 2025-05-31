<script setup>
import {onMounted, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import AddToCartForm from "@/components/sales/AddToCartForm.vue";
import SearchPatientForm from "@/components/sales/SearchPatientForm.vue";

const router = useRouter();
const route = useRoute();
const isLoading = ref(false);
const clientLocked = ref(true);
const cart = ref([]);
const buyerInfo = ref({});


onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - NUEVA VENTA'
});
//TODO: Before continue add igv and profit to treatment (table-front) or add a note and make the maths on the sales module.
//TODO : Add a message on top of the createTreatment so when the user inputs the price the message is updated telling the value of IGV and profit using the sell_price value.
//TODO: Lock patient -> Load pending payments -> (show them with modal) -> Scan barcodes -> Add Product to cart.
//TODO: Duplicated product? Update the value on cart. Changes something when searching the same product again? Update the entire object on cart.
//TODO: Calc IGV based on docs.
//TODO: Design BOL and FACT.
</script>

<template>
  <main class="flex flex-col items-center pt-5">
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm min-w-150">
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-black text-center">NUEVA VENTA</h5>

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

      <div v-if="!clientLocked && !isLoading" class="container">
        <SearchPatientForm></SearchPatientForm>
      </div>

      <div class="grid grid-cols-2 ms-2 me-2">
        <div class="col">
          <h1 class="font-light text-lg">Datos del Cliente</h1>
          <span class="font-bold">NOMBRE: <span class="font-light">CLI_NOMBRE</span></span>
          <br>
          <span class="font-bold">APELLIDOS: <span class="font-light">CLI_SURNAME</span></span>
          <br>
          <span class="font-bold">DNI: <span class="font-light">CLI_DNI</span></span>
        </div>
        <div class="col">
          <h1 class="font-light text-lg">Herramientas</h1>
          <div>
            <button
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed w-70"
                type="button"
            >
              <i class="bi bi-receipt w-3 h-3 me-2 flex items-center justify-center"></i>
              PAGOS PENDIENTES (x)
            </button>
          </div>
          <div>
            <button
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed w-70 mt-2"
                type="button"
            >
              <i class="bi bi-clipboard2-pulse w-3 h-3 me-2 flex items-center justify-center"></i>
              AÑADIR SERVICIO
            </button>
          </div>
        </div>
      </div>

      <div v-if="!isLoading && clientLocked" class="container">
        <AddToCartForm></AddToCartForm>
      </div>

      <div v-if="cart.length <= 0 && clientLocked" class="container">
        <div class="flex flex-col items-center">
          <h1 class="bi bi-cart-x" style="font-size: 150px"></h1>
          <h1 class="font-light text-2xl">No hay productos o servicios en el carrito.</h1>
        </div>
      </div>
    </div>
  </main>
</template>