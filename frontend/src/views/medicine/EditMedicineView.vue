<script setup>
import {onMounted, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import {reloadOnDismiss, reloadPage} from "@/utils/helpers.js";
import {MedicineService} from "@/services/medicine-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM, SUCCESS_MESSAGES as SM} from "@/utils/constants.js";
import {ErrorMessage, Field, Form} from "vee-validate";
import SearchPresentationModal from "@/components/presentation/SearchPresentationModal.vue";
import SearchSupplierModal from "@/components/supplier/SearchSupplierModal.vue";
import * as yup from "yup";
import {SupplierService} from "@/services/supplier-service.js";
import {PresentationService} from "@/services/presentation-service.js";
import {useAuthStore} from "@/stores/auth.js";
import {SettingService} from "@/services/setting-service.js";

const IGV_VALUE = ref(0.18);
const router = useRouter();
const route = useRoute();
const isLoading = ref(false);
const loadError = ref(false);
const medicine = ref(null);
const submitting = ref(false);
const buyPriceValue = ref(0);
const sellPriceValue = ref(0);
const profitValue = ref(0);
const igvValue = ref(0);
const igvStatus = ref(true);
const salable = ref(true);
const originalBarcode = ref('');

const presentationName = ref('NO SELECCIONADA');
const supplierName = ref('NO SELECCIONADO');
const selectedPresentationId = ref(0);
const selectedSupplierId = ref(0);
const selectedPresentation = ref({});
const selectedSupplier = ref({});

const showSearchPresentationModal = ref(false);
const showSearchSupplierModal = ref(false);
const editMedicineForm = ref();
const authService = useAuthStore();

const medicineSchema = yup.object({
  name: yup.string()
      .min(5, 'El nombre debe tener entre 5 y 100 carácteres.')
      .max(100, 'El nombre debe tener entre 5 y 100 carácteres.')
      .matches(/^.*\S.*$/, 'El nombre no puede ser solo espacios en blanco.')
      .matches(/^\S.*$/, 'El nombre no debe comenzar con espacios.')
      .required('Debe ingresar un nombre.'),
  composition: yup.string()
      .min(5)
      .max(100)
      .matches(/^.*\S.*$/, 'La composición no puede ser solo espacios en blanco.')
      .matches(/^\S.*$/, 'La composición no debe comenzar con espacios.')
      .required('Debe ingresar la composición'),
  description: yup.string()
      .min(5, 'La descripción debe tener entre 5 y 150 carácteres.')
      .max(150, 'La descripción debe tener entre 5 y 150 carácteres.')
      .matches(/^.*\S.*$/, 'La descripción no puede ser solo espacios en blanco.')
      .matches(/^\S.*$/, 'La descripción no debe comenzar con espacios.')
      .required('Debe ingresar una descripción'),
  buy_price: yup.number()
      .typeError('Debe ingresar un precio de compra válido.')
      .positive().test(
          "is-decimal", "Máximo dos decimales permitidos.",
          (value) => /^\d+(\.\d{1,2})?$/.test(String(value))
      ).required('Debe ingresar un precio de compra.'),
  sell_price: yup.number()
      .typeError('Debe ingresar un precio de venta válido.')
      .positive().test(
          "is-decimal", "Máximo dos decimales permitidos.",
          (value) => /^\d+(\.\d{1,2})?$/.test(String(value))
      ).min(yup.ref('buy_price'),'El precio de venta debe ser igual o superior al de compra.').required('Debe ingresar un precio de venta.'),
  igv: yup.number()
      .typeError('Los valores de precio de compra-venta deben ser válidos.')
      .required('El IGV es requerido.'),
  profit: yup.number()
      .typeError('Los valores de precio de compra-venta deben ser válidos.')
      .required('La ganancia es requerida.'),
  stock: yup.number().moreThan(0).positive().required('Debe ingresar el stock.'),
  presentation: yup.number().positive('Debe seleccionar una presentación válida.').required('Debe seleccionar una presentación.'),
  supplier: yup.number().positive('Debe seleccionar una presentación válida.').required('Debe seleccionar un proveedor.'),
  barcode: yup
      .string()
      .min(8, 'El código de barras debe tener entre 8 y 30 carácteres.')
      .max(30, 'El código de barras debe tener entre 8 y 30 carácteres.')
      .matches(/^[A-Za-z0-9]{8,30}$/, 'El código de barras debe contener solo números y letras.')
      .required('Debe ingresar un código de barras.'),
});


async function loadMedicine(id) {
  isLoading.value = true;
  try {
    medicine.value = await MedicineService.getById(id);
    buyPriceValue.value = medicine.value.buy_price;
    sellPriceValue.value = medicine.value.sell_price;
    igvValue.value = medicine.value.igv;
    profitValue.value = medicine.value.profit;
    salable.value = medicine.value.salable;
    igvStatus.value = medicine.value.igv >= 1;
    originalBarcode.value = medicine.value.barcode;
    onPricesChanges();
    await loadSupplier();
    await loadPresentation();
    await loadIgvValue();
    loadError.value = false;
    isLoading.value = false;
  } catch (err) {
    loadError.value = true;
    Swal.fire(EM.ERROR_TAG, EM.MEDICINE_NOT_FOUND, 'error').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        router.back();
      }
    });
  }
}

async function loadSupplier() {
  try {
    const supplierId = medicine.value.suppliers[0].id;
    selectedSupplier.value = await SupplierService.getById(supplierId);
    selectedSupplierId.value = selectedSupplier.value.id;
    supplierName.value = selectedSupplier.value.name;
  } catch (err) {
    selectedSupplierId.value = 0;
    supplierName.value = 'NO SELECCIONADO';
  }
}

async function loadPresentation() {
  try {
    const presentationId = medicine.value.presentation.id;
    selectedPresentation.value = await PresentationService.getById(presentationId);
    selectedPresentationId.value = selectedPresentation.value.id;
    presentationName.value = `${selectedPresentation.value.name} ${selectedPresentation.value.numeric_value} ${selectedPresentation.value.aux}`;
  } catch (err) {
    selectedPresentationId.value = 0;
    presentationName.value = 'NO SELECCIONADA';
  }
}

async function loadIgvValue() {
  try {
    const response = await SettingService.getByKey('VALOR_IGV');
    IGV_VALUE.value = parseFloat(response.value);
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, EM.IGV_VAL_NOT_LOADED, 'error');
    IGV_VALUE.value = 0.18;
  }
}

function onPricesChanges() {
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

async function onSubmit(values) {
  onPricesChanges();
  submitting.value = true;
  const payload = {
    ...values,
    barcode: medicine.value.barcode,
    igv: igvValue.value,
    profit: profitValue.value,
    salable: salable.value,
    presentation: selectedPresentationId.value,
    supplier: selectedSupplierId.value,
    updated_by: authService.getUserId(),
  }
  try {
    const response = await MedicineService.update(medicine.value.id, payload);
    Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then((r) => reloadOnDismiss(r));
  } catch (err) {
    if (err.errors?.barcode) {
      Swal.fire(EM.ERROR_TAG, EM.BARCODE_TAKEN, 'warning').then((r) => {
        onBarcodeInput('');
      });
    } else {
      Swal.fire(EM.ERROR_TAG, EM.SERVER_ERROR, 'error').then((r) => reloadOnDismiss(r));
    }
  } finally {
    submitting.value = false;
  }
}

function handleSearchPresentationModal(p) {
  showSearchPresentationModal.value = !showSearchPresentationModal.value;
  if (p && p.id != null) {
    selectedPresentationId.value = p.id;
    selectedPresentation.value = p;
    presentationName.value = `${p.name} ${p.numeric_value} ${p.aux}`;
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

function goToMedicineList() {
  router.push({name: 'medicine-list'});
}

async function loadRandomBarcode() {
  isLoading.value = true;
  try {
    const response = await MedicineService.generateRandomBarcode();
    isLoading.value = false;
    medicine.value.barcode = response.barcode;
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, EM.BARCODE_GENERATION_ERROR, 'error').then((r) => reloadOnDismiss(r))
  }
}

function onBarcodeInput(newVal) {
  medicine.value.barcode = newVal;
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - EDITAR PROVEEDOR';
  const id = route.params.id;
  if (isNaN(id)) {
    router.back();
  }
  loadMedicine(id);
});
</script>

<template>
  <main class="flex flex-col items-center pt-5">
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm min-w-150">
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-black text-center">EDITAR MEDICAMENTO</h5>
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
        <h1 class="mt-5 text-2xl font-light">Cargando medicamento...</h1>
      </div>
      <div
          v-if="!isLoading"
          class="flex items-center p-4 mb-4 text-sm text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50 mt-3"
          role="alert">
        <svg aria-hidden="true" class="shrink-0 inline w-4 h-4 me-3" fill="currentColor"
             viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path
              d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
        </svg>
        <span class="sr-only">Info</span>
        <div>
          Código de barras actual: <span class="font-medium">{{ originalBarcode }}</span> - ID: <span
            class="font-medium">{{ medicine?.id }}</span> <br>
          Valor del IGV: <span class="font-medium">{{ IGV_VALUE * 100 }}%</span>
        </div>
      </div>
      <Form v-if="!isLoading" ref="editMedicineForm" :validation-schema="medicineSchema" class="space-y-3 mt-5"
            @submit="onSubmit">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Nombre</label>
            <Field id="name" :disabled="submitting" :model-value="medicine?.name" :validate-on-input="true"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   name="name"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium" name="name"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Composición</label>
            <Field id="composition" :disabled="submitting" :model-value="medicine?.composition"
                   :validate-on-input="true"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   name="composition"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium"
                          name="composition"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Descripción</label>
            <Field id="description" :disabled="submitting" :model-value="medicine?.description"
                   :validate-on-input="true"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   name="description"
                   type="text"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium"
                          name="description"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Stock</label>
            <Field id="stock" :disabled="submitting" :model-value="medicine?.stock" :validate-on-input="true"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   name="stock"
                   type="number"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium"
                          name="stock"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Precio de Compra</label>
            <Field id="buy_price" v-model="buyPriceValue" :disabled="submitting" :validate-on-input="true"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   name="buy_price"
                   type="number"
                   @change="onPricesChanges"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium"
                          name="buy_price"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Precio de Venta</label>
            <Field id="sell_price" v-model="sellPriceValue" :disabled="submitting" :validate-on-input="true"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   name="sell_price"
                   type="number"
                   @change="onPricesChanges"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium"
                          name="sell_price"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Valor IGV</label>
            <Field id="igv" v-model="igvValue"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   disabled
                   name="igv"
                   type="number"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium" name="igv"></ErrorMessage>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900">Ganancia</label>
            <Field id="profit" v-model="profitValue"
                   :class="{'text-red-800 font-bold': profitValue <= 0, 'text-green-800 font-bold': profitValue >= 0.1}"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   disabled
                   name="profit"
                   type="number"/>
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium" name="profit"></ErrorMessage>
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
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium"
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
            <ErrorMessage class="mt-1 text-sm text-red-600 font-medium"
                          name="supplier"></ErrorMessage>
          </div>
        </div>
        <Field id="presentation" v-model="selectedPresentationId" name="presentation" type="hidden"/>
        <Field id="supplier" v-model="selectedSupplierId" name="supplier" type="hidden"/>
        <div class="relative">
          <label class="block mb-1 text-sm font-medium text-gray-900">Código de Barras</label>
          <Field id="barcode" :disabled="submitting" :model-value="medicine?.barcode"
                 class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-green-500 focus:border-green-500"
                 name="barcode"
                 type="text"
                 @update:model-value="onBarcodeInput"/>
          <div class="absolute flex gap-2 end-2.5 bottom-2.5">
            <button
                :disabled="isLoading || submitting"
                class="text-gray-700 bg-yellow-400 hover:bg-yellow-200 focus:ring-4 focus:outline-none focus:ring-yellow-400 font-medium rounded-lg text-sm px-4 py-2"
                type="button"
                @click="loadRandomBarcode"
            >
              Aleatorio
            </button>
          </div>

        </div>
        <ErrorMessage class="mt-1 text-sm text-red-600 font-medium" name="barcode"></ErrorMessage>

        <div v-if="!isLoading" class="flex justify-center mt-5">
          <div class="inline-flex rounded-md shadow-xs" role="group">
            <button :disabled="submitting"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                    type="button"
                    @click="goToMedicineList">
              <i class="bi bi-arrow-return-left w-3 h-3 me-2 flex items-center justify-center"></i>
              Atras
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
