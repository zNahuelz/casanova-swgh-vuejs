<script setup>
import ScanBarcodeForm from "@/components/medicine/ScanBarcodeForm.vue";
import {ErrorMessage, Field, Form} from "vee-validate";
import {onMounted, ref} from "vue";
import {useRouter} from "vue-router";
import {reloadOnDismiss, reloadPage} from "@/utils/helpers.js";
import * as yup from "yup";
import {useAuthStore} from "@/stores/auth.js";
import SearchPresentationModal from "@/components/presentation/SearchPresentationModal.vue";
import SearchSupplierModal from "@/components/supplier/SearchSupplierModal.vue";
import {MedicineService} from "@/services/medicine-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {SettingService} from "@/services/setting-service.js";

const IGV_VALUE = ref(0.18);
//I'm tired '-'
const submitting = ref(false);
const barcodeLocked = ref(false); //Change to false on prod.
const barcode = ref(''); //Change to '' on prod. 0000000063457
const isLoading = ref(false);

const selectedPresentationId = ref(0); //Change this to 5 for test. SupId for 3.
const selectedSupplierId = ref(0);
const selectedPresentation = ref({});
const selectedSupplier = ref({});
const presentationName = ref('NO SELECCIONADA');
const supplierName = ref('NO SELECCIONADO');
const profitValue = ref(0);
const igvValue = ref(0);

const buyPriceValue = ref(0);
const sellPriceValue = ref(0);

const igvStatus = ref(true);
const salable = ref(true);

const authService = useAuthStore();
const router = useRouter();

const showSearchPresentationModal = ref(false);
const showSearchSupplierModal = ref(false);


const medicineSchema = yup.object({
  name: yup.string().min(5, 'El nombre debe tener entre 5 y 100 carácteres.').max(100, 'El nombre debe tener entre 5 y 100 carácteres.').required('Debe ingresar un nombre.'),
  composition: yup.string().min(5, 'La composición debe tener entre 5 y 100 carácteres.').max(100, 'La composición debe tener entre 5 y 100 carácteres.').required('Debe ingresar la composición'),
  description: yup.string().min(5, 'La descripción debe tener entre 5 y 150 carácteres.').max(100, 'La descripción debe tener entre 5 y 150 carácteres.').required('Debe ingresar una descripción'),
  buy_price: yup.number().positive('El precio de compra debe ser positivo.').test(
      "is-decimal",
      "Máximo dos decimales permitidos.",
      (value) => /^\d+(\.\d{1,2})?$/.test(String(value))
  ).required('Debe ingresar un precio de compra.'),
  sell_price: yup.number().positive('El precio de venta debe ser positivo.').test(
      "is-decimal",
      "Máximo dos decimales permitidos.",
      (value) => /^\d+(\.\d{1,2})?$/.test(String(value))
  ).min(yup.ref('buy_price'), 'El precio de venta debe ser igual o superior al de compra.').required('Debe ingresar un precio de venta.'),
  igv: yup.number().required('El IGV es requerido.'),
  profit: yup.number().required('El IGV es requerido.'),
  stock: yup.number().moreThan(0, 'El valor de stock debe ser superior a 0.').positive('El stock debe ser positivo.').required('Debe ingresar el stock.'),
  presentation: yup.number().positive('Debe seleccionar una presentación.').required('Debe seleccionar una presentación.'),
  supplier: yup.number().positive('Debe seleccionar un proveedor.').required('Debe seleccionar un proveedor.'),
});

async function onSubmit(values) {
  onPricesChanges();
  submitting.value = true;
  const payload = {
    ...values,
    barcode: barcode.value,
    igv: igvValue.value,
    profit: profitValue.value,
    salable: salable.value,
    presentation: selectedPresentationId.value,
    supplier: selectedSupplierId.value,
    created_by: authService.getUserId(),
  }
  try {
    const response = await MedicineService.create(payload);
    Swal.fire(SM.SUCCESS_TAG, `${SM.MEDICINE_CREATED} ID Asignado: ${response.medicine.id}`, 'success').then((r) => reloadOnDismiss(r));
  } catch (err) {
    if (err.errors.barcode) {
      Swal.fire(EM.ERROR_TAG, EM.BARCODE_TAKEN, 'warning').then((r) => reloadOnDismiss(r));
    } else {
      Swal.fire(EM.ERROR_TAG, EM.SERVER_ERROR, 'error').then((r) => reloadOnDismiss(r));
    }
  } finally {
    submitting.value = false;
  }
}

function onPricesChanges() {
  //Don't event think about touching this lol
  //Psss... You touched it!
  //Counter = 2
  const buy = parseFloat(buyPriceValue.value);
  const sell = parseFloat(sellPriceValue.value);
  const igvRate = parseFloat(IGV_VALUE.value);

  if (igvStatus.value) {
    // 1. Calculate base price (subtotal) from sell including IGV
    const base = sell / (1 + igvRate);

    // 2. Calculate IGV from included value
    const igv = parseFloat((sell - base).toFixed(2));

    // 3. Profit = base - buy
    let profit = parseFloat((base - buy).toFixed(2));
    if (profit < 0) profit = 0;

    igvValue.value = igv;
    profitValue.value = profit;
  } else {
    igvValue.value = 0;
    let profit = parseFloat((sell - buy).toFixed(2));
    if (profit < 0) profit = 0;

    profitValue.value = profit;
  }
}

function handleBarcodeGeneration(newBarcode) {
  barcode.value = newBarcode;
  barcodeLocked.value = true;
}

function handleSearchPresentationModal(p) {
  showSearchPresentationModal.value = !showSearchPresentationModal.value;
  if (p && p.id != null) {
    selectedPresentationId.value = p.id;
    selectedPresentation.value = p;
    presentationName.value = `${p.name} ${p.numeric_value} ${p.aux}`
  } else {
    selectedPresentationId.value = 0;
    selectedPresentation.value = {};
    presentationName.value = 'NO SELECCIONADA';
  }
}

function handleSearchSupplierModal(s) {
  showSearchSupplierModal.value = !showSearchSupplierModal.value;
  if (s && s.id != null) {
    selectedSupplierId.value = s.id;
    selectedSupplier.value = s;
    supplierName.value = s.name;
  } else {
    selectedSupplierId.value = 0;
    selectedSupplier.value = {};
    supplierName.value = 'NO SELECCIONADO';
  }
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - NUEVO MEDICAMENTO'
  loadIgvValue();
});

async function loadIgvValue() {
  isLoading.value = true;
  try {
    const response = await SettingService.getByKey('VALOR_IGV');
    IGV_VALUE.value = parseFloat(response.value);
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, EM.IGV_VAL_NOT_LOADED, 'error');
    IGV_VALUE.value = 0.18;
  } finally {
    isLoading.value = false;
  }
}
</script>
<template>
  <main class="flex flex-col items-center pt-5">
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm min-w-150">
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-black text-center">NUEVO MEDICAMENTO</h5>
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
      <ScanBarcodeForm v-if="!barcodeLocked && !isLoading"
                       @barcodeGenerated="handleBarcodeGeneration"></ScanBarcodeForm>

      <div
          v-if="barcodeLocked"
          class="flex items-center p-4 mb-4 text-sm text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50 mt-3"
          role="alert">
        <svg aria-hidden="true" class="shrink-0 inline w-4 h-4 me-3" fill="currentColor"
             viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path
              d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
        </svg>
        <span class="sr-only">Info</span>
        <div>
          Código de barras actual: <span class="font-medium">{{ barcode }}</span> <br>
          Valor del IGV: <span class="font-medium">{{ IGV_VALUE * 100 }}%</span>
        </div>
      </div>

      <Form v-if="barcodeLocked && !isLoading" :validation-schema="medicineSchema" class="space-y-3 mt-5"
            @submit="onSubmit">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Nombre</label>
            <Field id="name" :validate-on-input="true"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   name="name"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="name"></ErrorMessage>
          </div>

          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Composición</label>
            <Field id="composition"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   name="composition"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="composition"></ErrorMessage>
          </div>

          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Descripción</label>
            <Field id="description"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   name="description"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="description"></ErrorMessage>
          </div>

          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Stock</label>
            <Field id="stock"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   name="stock"
                   type="number"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="stock"></ErrorMessage>
          </div>

          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Precio de Compra</label>
            <Field id="buy_price" v-model="buyPriceValue"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   name="buy_price" type="number"
                   @change="onPricesChanges"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="buy_price"></ErrorMessage>
          </div>

          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Precio de Venta</label>
            <Field id="sell_price" v-model="sellPriceValue"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   name="sell_price" type="number"
                   @change="onPricesChanges"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="sell_price"></ErrorMessage>
          </div>

          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Valor IGV</label>
            <Field id="igv" v-model="igvValue"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   disabled
                   name="igv"
                   type="number"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="igv"></ErrorMessage>
          </div>

          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Ganancia</label>
            <Field id="profit" v-model="profitValue"
                   :class="{'text-red-800 font-bold': profitValue <= 0, 'text-green-800 font-bold': profitValue >= 0.1}"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   disabled
                   name="profit"
                   type="number"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium" name="profit"></ErrorMessage>
          </div>

          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Vendible</label>
            <div class="flex items-center ps-4 border border-gray-300 rounded-lg h-10 bg-gray-50">

              <input id="salable" v-model="salable" :disabled="submitting"
                     class="w-4 h-4 bg-gray-50 border-gray-300 rounded-sm focus:ring-green-500 text-green-600"
                     name="salable"
                     type="checkbox"/>
              <label class="ms-2 text-sm font-medium text-gray-900" for="salable">Venta Habilitada</label>
            </div>
          </div>

          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">IGV</label>
            <div class="flex items-center ps-4 border border-gray-300 rounded-lg h-10 bg-gray-50">

              <input id="igvStatus" v-model="igvStatus" :disabled="submitting"
                     class="w-4 h-4 bg-gray-50 border-gray-300 rounded-sm focus:ring-red-500 text-red-600"
                     name="igvStatus"
                     type="checkbox"
                     @change="onPricesChanges"/>
              <label class="ms-2 text-sm font-medium text-gray-900" for="igvStatus">Afecto al IGV</label>
            </div>
          </div>

          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Presentación</label>
            <div class="flex">
              <input :value="presentationName"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-l-lg focus:ring-green-800 focus:border-green-800 w-full h-10 ps-3"
                     disabled
                     type="text"
              />
              <button
                  :disabled="submitting"
                  class="bg-green-600 text-white px-4 rounded-r-lg border border-l-0 border-gray-300 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-500"
                  type="button"
                  @click="handleSearchPresentationModal"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" stroke-linecap="round"
                        stroke-linejoin="round"/>
                </svg>
              </button>

            </div>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="presentation"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Proveedor</label>
            <div class="flex">
              <input :value="supplierName"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-l-lg focus:ring-green-800 focus:border-green-800 w-full h-10 ps-3"
                     disabled
                     type="text"
              />
              <button
                  :disabled="submitting"
                  class="bg-green-600 text-white px-4 rounded-r-lg border border-l-0 border-gray-300 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-500"
                  type="button"
                  @click="handleSearchSupplierModal"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" stroke-linecap="round"
                        stroke-linejoin="round"/>
                </svg>
              </button>
            </div>
            <ErrorMessage class="mt-1 text-sm text-red-600 dark:text-red-500 font-medium"
                          name="supplier"></ErrorMessage>
          </div>
        </div>

        <Field id="presentation" v-model="selectedPresentationId" name="presentation" type="hidden"/>
        <Field id="supplier" v-model="selectedSupplierId" name="supplier" type="hidden"/>

        <div v-if="barcodeLocked && !isLoading" class="flex justify-center mt-5">
          <div class="inline-flex rounded-md shadow-xs" role="group">
            <button :disabled="submitting"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                    type="button"
                    @click="reloadPage()">
              <i class="bi bi-x-circle w-3 h-3 me-2 flex items-center justify-center"></i>
              Cancelar
            </button>
            <button :disabled="submitting"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                    type="button"
                    @click="reloadPage()">
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

    <SearchPresentationModal v-if="showSearchPresentationModal"
                             :onClose="handleSearchPresentationModal"></SearchPresentationModal>
    <SearchSupplierModal v-if="showSearchSupplierModal" :onClose="handleSearchSupplierModal"></SearchSupplierModal>
  </main>
</template>

