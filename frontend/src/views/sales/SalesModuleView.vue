<script setup>
import {onMounted, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import AddToCartForm from "@/components/sales/AddToCartForm.vue";
import SearchPatientForm from "@/components/sales/SearchPatientForm.vue";
import {PaymentService} from "@/services/payment-service.js";
import {SettingService} from "@/services/setting-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM} from "@/utils/constants.js";
import PendingPaymentsModal from "@/components/sales/PendingPaymentsModal.vue";
import {formatAsDate, formatAsTime, formatTwoDecimals} from "@/utils/helpers.js";
import AddServiceModal from "@/components/sales/AddServiceModal.vue";
import MakePaymentModal from "@/components/sales/MakePaymentModal.vue";

const IGV_VALUE = ref(0.18);
const PAYMENT_TYPES = ref([]);
const router = useRouter();
const route = useRoute();
const isLoading = ref(false);
const clientLocked = ref(false);
const cart = ref([]);
const buyerInfo = ref({});
const pendingPayments = ref([]);

const subtotal = ref(0.0);
const total = ref(0.0);
const igv = ref(0.0);

const showPendingPaymentsModal = ref(false);
const showAddServiceModal = ref(false);
const showMakePaymentModal = ref(false);

const submitting = ref(false);
const validSale = ref(false);
//** TODO: CHECK EVERYTHING. MAKE SURE AFTER EVERY OPERATION doMath() and isValidSale() RUNS.

async function handleBuyerInfo(buyer) {
  buyerInfo.value = buyer;
  clientLocked.value = true;
  if (buyerInfo.value.dni !== '00000000') {
    try {
      isLoading.value = true;
      pendingPayments.value = await PaymentService.getByPatientDni(buyerInfo.value.dni);
      //console.log(pendingPayments.value);
    } catch (err) {
      pendingPayments.value = [];
    } finally {
      isLoading.value = false;
    }
  }
}

async function loadIgvValue() {
  isLoading.value = true;
  try {
    const response = await SettingService.getByKey('VALOR_IGV');
    IGV_VALUE.value = parseFloat(response.value);
    await loadPaymentTypes();
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, EM.IGV_VAL_NOT_LOADED, 'error');
    IGV_VALUE.value = 0.18;
  } finally {
    isLoading.value = false;
  }
}

async function loadPaymentTypes() {
  isLoading.value = true;
  try {
    PAYMENT_TYPES.value = await PaymentService.getPaymentTypes();
    isLoading.value = false;
    //console.log(PAYMENT_TYPES.value)
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, EM.PAYMENT_TYPES_NOT_LOADED, 'error').then((r) => {
      if (r.isConfirmed || r.isDismissed || r.dismiss) {
        router.back();
      }
    });
  }
}

function addPendingPaymentToList(payment) {
  handlePendingPaymentsModal();
  const paymentIndex = pendingPayments.value.findIndex(e => e.id === payment.id);
  const original = pendingPayments.value[paymentIndex];
  const clonedDetails = JSON.parse(JSON.stringify(original));
  const cartItem = {
    type: 'SERVICE_APP',
    cost: parseFloat(pendingPayments.value[paymentIndex].value),
    igv: parseFloat(calculateAppointmentIgv(pendingPayments.value[paymentIndex].value)),
    details: clonedDetails,
    amount: 1,
  }
  cart.value.push(cartItem);
  pendingPayments.value.splice(paymentIndex, 1);

  doMath();
  isValidSale();
}

function addProductToList(product) {
  const alreadyOnCart = cart.value.findIndex(e => e.details.id === product.id && e.type === 'PRODUCT');
  //Product already on cart.
  if (alreadyOnCart !== -1) {
    const existing = cart.value[alreadyOnCart];
    if (existing.amount + 1 > product.stock) {
      showStockAlert(product);
    }
    existing.cost = parseFloat(product.sell_price);
    existing.igv = parseFloat(product.igv);
    existing.details = JSON.parse(JSON.stringify(product));
    existing.amount += 1;

  } else {
    if (product.stock <= 0) {
      showStockAlert(product);
    }
    const cartItem = {
      type: 'PRODUCT',
      cost: parseFloat(product.sell_price),
      igv: parseFloat(product.igv),
      details: JSON.parse(JSON.stringify(product)), //product.
      amount: 1,
    }
    cart.value.push(cartItem);
  }
  doMath();
  isValidSale();
}

function addServiceToList(treatment) {
  handleAddServiceModal();
  const alreadyOnCart = cart.value.findIndex(e => e.details.id === treatment.id && e.type === 'SERVICE_TRT');
  //Service already on cart.
  if (alreadyOnCart !== -1) {
    const existing = cart.value[alreadyOnCart];
    existing.cost = parseFloat(treatment.price);
    existing.igv = parseFloat(treatment.igv);
    existing.details = JSON.parse(JSON.stringify(treatment));
    Swal.fire('Información',
        `El servicio: ${treatment.name} ya se encuentra añadido al carrito. Solo puede realizar el pago de distintos tipos de servicio por compra.`,
        'info');
  } else {
    const cartItem = {
      type: 'SERVICE_TRT',
      cost: parseFloat(treatment.price),
      igv: parseFloat(treatment.igv),
      details: JSON.parse(JSON.stringify(treatment)),
      amount: 1,
    }
    cart.value.push(cartItem);
  }
  doMath();
  isValidSale();
  //console.log(treatment);
}

function removeItemFromCart(item) {
  //console.log(item)
  //console.log(cart.value);

  if (item.type === 'SERVICE_APP') {
    const itemIndex = cart.value.findIndex(e => e.details.id === item.details.id && e.type === 'SERVICE_APP');
    if (itemIndex !== -1) {
      const clonedDetails = JSON.parse(JSON.stringify(item.details));
      pendingPayments.value.push(clonedDetails);
      cart.value.splice(itemIndex, 1);
    }
  } else if (item.type === 'PRODUCT') {
    const itemIndex = cart.value.findIndex(e => e.details.id === item.details.id && e.type === 'PRODUCT');
    if (itemIndex !== -1) {
      cart.value.splice(itemIndex, 1);
    }
  } else if (item.type === 'SERVICE_TRT') {
    const itemIndex = cart.value.findIndex(e => e.details.id === item.details.id && e.type === 'SERVICE_TRT');
    if (itemIndex !== -1) {
      cart.value.splice(itemIndex, 1);
    }
  }
  doMath();
  isValidSale();
}

function removeOneProductFromCart(item) {
  if (item.type === 'PRODUCT') {
    const itemIndex = cart.value.findIndex(e => e.details.id === item.details.id && e.type === 'PRODUCT');
    if (cart.value[itemIndex].amount - 1 <= 0) {
      removeItemFromCart(item);
    } else {
      cart.value[itemIndex].amount -= 1;
    }
  }
  doMath();
  isValidSale();
}

function clearCart() {
  cart.value.forEach((e) => {
    if (e.type === 'SERVICE_APP') {
      const clonedDetails = JSON.parse(JSON.stringify(e.details));
      pendingPayments.value.push(clonedDetails);
    }
  });
  cart.value = [];
  isValidSale();
}

function showStockAlert(product) {
  Swal.fire('Información', `El stock registrado en el sistema del producto: ${product.name} es de ${product.stock} unidades.
  Puede continuar con la venta si tiene los productos a mano, caso contrario disminuya la cantidad del producto e informe al cliente.
  <strong>Recuerde mantener el stock de los productos actualizado.</strong>
  `, 'info');
}

function makePayment() {
  doMath();
  handleMakePaymentModal();
}

async function verifyPayload() {
  try {
    submitting.value = true;
    let payload = {
      products: [],
      treatments: [],
      pending_payments: [],
    }
    cart.value.forEach((e) => {
      if (e.type === 'PRODUCT') {
        payload.products.push(e.details.id);
      }
      if (e.type === 'SERVICE_APP') {
        payload.pending_payments.push(e.details.id);
      }
      if (e.type === 'SERVICE_TRT') {
        payload.treatments.push(e.details.id);
      }
    });

    Object.keys(payload).forEach((k) => {
      if (payload[k].length === 0) {
        delete payload[k];
      }
    });

    const response = await PaymentService.verifyShoppingCart(payload);
    submitting.value = false;
    makePayment();
  } catch (err) {
    //console.log(err);
    if (err.itemsToRemove?.products?.length >= 1) {
      err.itemsToRemove?.products.forEach((p) => {
        let itemIndex = cart.value.findIndex((e) => e.details.id === p && e.type === 'PRODUCT');
        if (itemIndex !== -1) {
          cart.value.splice(itemIndex, 1);
        }
      });
    }
    if (err.itemsToRemove?.treatments?.length >= 1) {
      err.itemsToRemove?.treatments.forEach((t) => {
        let itemIndex = cart.value.findIndex((e) => e.details.id === t && e.type === 'SERVICE_TRT');
        if (itemIndex !== -1) {
          cart.value.splice(itemIndex, 1);
        }
      });
    }
    if (err.itemsToRemove?.pending_payments?.length >= 1) {
      cart.value = cart.value.filter(e => e.type !== 'SERVICE_APP');
      pendingPayments.value = [];
      pendingPayments.value = await PaymentService.getByPatientDni(buyerInfo.value.dni);
    }
    doMath();
    isValidSale();
    submitting.value = false;
    Swal.fire(EM.ERROR_TAG, EM.INVALID_CART_ITEMS_REMOVED, 'error');
  }
}

function doMath() {
  calculateSubtotalValue();
  calculateIgvValue();
  calculateTotalValue();
}

function calculateTotalItems() {
  let counter = 0;
  cart.value.forEach((e) => {
    counter += e.amount;
  });
  return counter;
}

function calculateSubtotalValue() {
  subtotal.value = 0;
  cart.value.forEach((e) => {
    subtotal.value += (e.cost - e.igv) * e.amount;
  });
  //subtotal.value = (subtotal.value).toFixed(2);
}

function calculateIgvValue() {
  igv.value = 0;
  cart.value.forEach((e) => {
    igv.value += e.igv * e.amount;
  });
  //igv.value = (igv.value).toFixed(2);
}

function calculateTotalValue() {
  total.value = 0;
  cart.value.forEach((e) => {
    total.value += e.amount * e.cost;
  });
  //total.value = (total.value).toFixed(2);
}

function calculateAppointmentIgv(cost) {
  return (cost * IGV_VALUE.value).toFixed(2) || 0;
}

function handlePendingPaymentsModal() {
  showPendingPaymentsModal.value = !showPendingPaymentsModal.value;
}

function handleAddServiceModal() {
  showAddServiceModal.value = !showAddServiceModal.value;
}

function handleMakePaymentModal() {
  submitting.value = !submitting.value; //TODO: CHECK THIS!
  showMakePaymentModal.value = !showMakePaymentModal.value;
  isValidSale();
}

function isValidSale() {
  validSale.value = cart.value.length >= 1 && clientLocked.value;
}

function goBack() {
  router.back();
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - NUEVA VENTA'
  loadIgvValue();
});
//TODO: Calc IGV based on docs.
//TODO: Design ONLY** BOL
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
        <SearchPatientForm @buyerInfo="handleBuyerInfo"></SearchPatientForm>
      </div>

      <div v-if="!isLoading && clientLocked" class="grid grid-cols-2 ms-2 me-2">
        <div class="col">
          <h1 class="font-light text-lg">Datos del Cliente</h1>
          <span class="font-bold">NOMBRE: <span class="font-light">{{ buyerInfo.name }}</span></span>
          <br>
          <span class="font-bold">APELLIDOS: <span class="font-light">{{ buyerInfo.paternal_surname }}</span></span>
          <br>
          <span class="font-bold">DNI: <span class="font-light">{{ buyerInfo.dni }}</span></span>
        </div>
        <div class="col">
          <h1 class="font-light text-lg">Herramientas</h1>
          <div>
            <button :class="{'text-red-500 font-bold hover:text-red-800': pendingPayments.length > 0}"
                    :disabled="pendingPayments.length <= 0 || submitting"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed w-70"
                    type="button"
                    @click="handlePendingPaymentsModal"
            >
              <i class="bi bi-receipt w-3 h-3 me-2 flex items-center justify-center"></i>
              {{ pendingPayments.length > 0 ? `PAGOS PENDIENTES (${pendingPayments.length})` : 'SIN PAGOS PENDIENTES' }}
            </button>
          </div>
          <div>
            <button
                :disabled="submitting"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed w-70 mt-2"
                type="button" @click="handleAddServiceModal"
            >
              <i class="bi bi-clipboard2-pulse w-3 h-3 me-2 flex items-center justify-center"></i>
              AÑADIR SERVICIO
            </button>
          </div>
        </div>
      </div>

      <div v-if="!isLoading && clientLocked && !submitting" class="container">
        <AddToCartForm @foundProduct="addProductToList"></AddToCartForm>
      </div>

      <div v-if="cart.length >=1 && clientLocked && !isLoading" class="mt-5">
        <div class="relative overflow-x-auto">
          <table class="w-full text-sm text-left rtl:text-right text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
            <tr>
              <th class="px-6 py-3 rounded-s-lg" scope="col">
                PRODUCTO
              </th>
              <th class="px-6 py-3" scope="col">
                CANT.
              </th>
              <th class="px-6 py-3" scope="col">
                PRECIO
              </th>
              <th class="px-6 py-3 text-center rounded-e-lg" scope="col">
                ACCIÓN
              </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="i in cart" :key="i.details" class="bg-white text-sm">
              <th class="px-6 py-4 font-medium text-gray-900 w-50 text-wrap" scope="row">
                {{
                  i.type === 'SERVICE_APP' ? `CITA: ${i.details.appointment.rescheduling_date ? formatAsDate(i.details.appointment.rescheduling_date) : formatAsDate(i.details.appointment.date)} * HORA: ${i.details.appointment.rescheduling_time ? formatAsTime(i.details.appointment.rescheduling_time) : formatAsTime(i.details.appointment.time)}` : ''
                }}
                {{
                  i.type === 'PRODUCT' ? `${i.details.name}` : ''
                }}
                {{
                  i.type === 'SERVICE_TRT' ? `${i.details.name}` : ''
                }}
              </th>
              <td class="px-6 py-4">
                {{ i.amount }}
              </td>
              <td class="px-6 py-4">
                S./ {{ i.cost }}
              </td>
              <td class="px-6 py-4 text-center">
                <button
                    :disabled="submitting"
                    class="px-3 py-2 text-xs font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 disabled:bg-gray-500 disabled:cursor-not-allowed"
                    title="QUITAR" type="button"
                    @click="removeItemFromCart(i)"
                >
                  <i class="bi bi-x-square"></i>
                </button>
                <button v-if="i.type === 'PRODUCT'"
                        :disabled="submitting"
                        class="px-3 py-2 text-xs font-medium text-center text-white bg-sky-700 rounded-lg hover:bg-sky-800 focus:ring-4 focus:outline-none focus:ring-sky-300 disabled:bg-gray-500 disabled:cursor-not-allowed ms-1"
                        title="RESTAR" type="button"
                        @click="removeOneProductFromCart(i)"
                >
                  <i class="bi bi-dash"></i>
                </button>
              </td>
            </tr>
            </tbody>
            <tfoot>
            <tr class="font-semibold text-gray-900">
              <th class="px-6 py-3 text-base" scope="row">SUBTOTAL</th>
              <td class="px-6 py-3"></td>
              <td class="px-6 py-3">S./ {{ formatTwoDecimals(subtotal) }}</td>
            </tr>
            <tr class="font-semibold text-gray-900">
              <th class="px-6 py-3 text-base" scope="row">IGV</th>
              <td class="px-6 py-3"></td>
              <td class="px-6 py-3">S./ {{ formatTwoDecimals(igv) }}</td>
            </tr>
            <tr class="font-semibold text-xl">
              <th class="px-6 py-3 text-base text-green-700" scope="row">TOTAL</th>
              <td class="px-6 py-3 text-green-700">{{ calculateTotalItems() }}</td>
              <td class="px-6 py-3 text-green-700">S./ {{ formatTwoDecimals(total) }}</td>
            </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div v-if="cart.length <= 0 && clientLocked && !isLoading" class="container">
        <div class="flex flex-col items-center">
          <h1 class="bi bi-cart-x" style="font-size: 150px"></h1>
          <h1 class="font-light text-2xl">No hay productos o servicios en el carrito.</h1>
        </div>
      </div>


      <div class="flex flex-col items-center mt-3">
        <div v-if="!isLoading" class="flex justify-center mt-5">
          <div class="inline-flex rounded-md shadow-xs" role="group">
            <button :disabled="submitting"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                    @click="goBack">
              <i class="bi bi-arrow-return-left w-3 h-3 me-2 flex items-center justify-center"></i>
              Cancelar
            </button>
            <button :disabled="submitting || cart.length <= 0"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                    @click="clearCart">
              <i class="bi bi-cart-x w-3 h-3 me-2 flex items-center justify-center"></i>
              Vaciar Carrito
            </button>
            <button :disabled="submitting || !validSale"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                    type="button"
                    @click="verifyPayload">
              <i class="bi bi-credit-card-2-back w-3 h-3 me-2 flex items-center justify-center"></i>
              {{ submitting ? 'Procesando...' : 'Siguiente' }}
            </button>
          </div>

        </div>
      </div>
    </div>

    <PendingPaymentsModal v-if="showPendingPaymentsModal"
                          :onClose="handlePendingPaymentsModal"
                          :pendingPayments="pendingPayments"
                          @addToList="addPendingPaymentToList"
    ></PendingPaymentsModal>
    <AddServiceModal v-if="showAddServiceModal"
                     :onClose="handleAddServiceModal"
                     @addServiceToList="addServiceToList"
    ></AddServiceModal>
    <MakePaymentModal v-if="showMakePaymentModal && validSale"
                      :cart="cart"
                      :clientInfo="buyerInfo"
                      :igv="igv"
                      :onClose="handleMakePaymentModal"
                      :paymentTypes="PAYMENT_TYPES"
                      :subtotal="subtotal"
                      :total="total"
    ></MakePaymentModal>
  </main>
</template>