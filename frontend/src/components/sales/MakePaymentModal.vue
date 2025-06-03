<script setup>
import {computed, onMounted, ref} from "vue";
import {formatTwoDecimals} from "../../utils/helpers.js";
import {useAuthStore} from "@/stores/auth.js";
import {VoucherService} from "@/services/voucher-service.js";

const {
  onClose,
  paymentTypes,
  cart,
  clientInfo,
  subtotal,
  igv,
  total
} = defineProps(['onClose', 'paymentTypes', 'cart', 'clientInfo', 'subtotal', 'igv', 'total']);

const submitting = ref(false);
const selectedPaymentId = ref(0);
const paymentObject = ref({});
const cashInputValue = ref(0);
const cashChangeValue = ref(0);
const paymentHash = ref('');
const authService = useAuthStore();


function onCashInput(e) {
  let val = e.target.value;

  val = val.replace(/[^0-9.]/g, '');

  const firstDotIndex = val.indexOf('.');
  if (firstDotIndex >= 0) {
    const beforeDot = val.slice(0, firstDotIndex + 1);
    const afterDot = val
        .slice(firstDotIndex + 1)
        .replace(/\./g, '');
    val = beforeDot + afterDot;
  }
  cashInputValue.value = val;
}

const exceedsTotal = computed(() => {
  const num = parseFloat(cashInputValue.value);
  const tot = parseFloat(total);

  if (!isNaN(num) && num >= tot) {
    cashChangeValue.value = Number((num - tot).toFixed(2));
    return true;
  } else {
    cashChangeValue.value = 0;
    return false;
  }
});

const verifyHash = computed(() => {
  const hash = paymentHash.value.trim();
  return hash.length >= 5 && hash.length <= 100;
})

function setPaymentType() {
  cashChangeValue.value = 0;
  cashInputValue.value = 0;
  paymentHash.value = '';
  paymentTypes.forEach((e) => {
    if (e.id === selectedPaymentId.value) {
      paymentObject.value = JSON.parse(JSON.stringify(e));
    }
  });
}

function canSubmit() {
  if (paymentObject.value.name === 'EFECTIVO') {
    return !submitting.value && exceedsTotal.value;
  }
  if (paymentObject.value.name === 'TARJETA BANCARIA' || paymentObject.value.name === 'PLIN' || paymentObject.value.name === 'YAPE') {
    return !submitting.value && verifyHash.value;
  }
  return false;
}


async function onSubmit() {
  try {
    submitting.value = true;
    let payload = generatePayload();
    payload = {
      cart: [...payload],
      subtotal: parseFloat(subtotal),
      igv: parseFloat(igv),
      total: parseFloat(total),
      created_by: authService.getUserId(),
      payment_type_id: paymentObject.value.id,
      patient_id: clientInfo.id,
      voucher_type: 'BOLETA',
      change: parseFloat(cashChangeValue.value) || 0,
      payment_hash: paymentHash.value,
    }
    const response = await VoucherService.create(payload);
    //TODO: **** Modify endpoint so it saves the sale and generate the voucher.
    console.log(response);
  } catch (err) {
    console.log(err);
  }
}

function generatePayload() {
  let payload = [];
  cart.forEach((e) => {
    if (e.type === 'PRODUCT') {
      const payloadItem = {
        type: 'PRODUCT',
        id: parseInt(e.details.id) || 0,
        amount: parseInt(e.amount),
        price: parseFloat(e.cost),
        igv: parseFloat(e.igv),
      }
      payload.push(payloadItem);
    }
    if (e.type === 'SERVICE_APP') {
      const payloadItem = {
        type: 'PENDING_PAYMENT',
        id: parseInt(e.details.id) || 0,
        amount: 1,
        price: parseFloat(e.cost),
        igv: parseFloat(e.igv),
      }
      payload.push(payloadItem);
    }
    if (e.type === 'SERVICE_TRT') {
      const payloadItem = {
        type: 'TREATMENT',
        id: parseInt(e.details.id) || 0,
        amount: 1,
        price: parseFloat(e.cost),
        igv: parseFloat(e.igv),
      }
    }
  });
  return payload;
}

onMounted(() => {
  selectedPaymentId.value = paymentTypes[0].id;
  setPaymentType();
});
//TODO:: -->> Form de vuelto y pago // Form de hash de pago ** ->>
//TODO:: save data.
</script>

<template>
  <div id="make-payment" class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm"
       tabindex="-1">
    <div class="relative bg-white rounded-lg shadow-lg w-auto">
      <div
          class="flex items-center justify-between p-4 md:p-5 border-b rounded-t  border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Confirmación de compra
        </h3>
        <button :disabled="submitting"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                type="button"
                @click="onClose">
          <svg aria-hidden="true" class="w-3 h-3" fill="none" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
            <path d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" stroke="currentColor" stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"/>
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <div class="grid grid-cols-4 ms-5 me-5 mb-5 mt-5 gap-x-4">
        <div class="col-span-1">
          <h1 class="font-light text-xl">Método de Pago</h1>

          <div class="mt-3">
            <select id="selectedPayment" v-model="selectedPaymentId"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-700 block w-50 p-2.5"
                    name="selectedPayment"
                    @change="setPaymentType">
              <option v-for="p in paymentTypes" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div class="flex flex-col items-center p-2 w-50">
            <img
                :src="paymentObject.name === 'YAPE' ? '/images/yape-logo.png'
                : paymentObject.name === 'PLIN' ? '/images/plin-logo.png'
                : paymentObject.name === 'TARJETA BANCARIA' ? '/images/card-logo.png'
                : paymentObject.name === 'EFECTIVO' ? '/images/sol-logo.png' : '/images/easter-egg.jpg'"
                draggable="false" height="50px" width="50px" @contextmenu.prevent>
          </div>
        </div>

        <div class="col-span-1">
          <h1 class="font-light text-xl">Detalles</h1>
          <ul class="text-end">
            <li><span class="font-bold">SUBTOTAL: </span>{{ formatTwoDecimals(subtotal) }}</li>
            <li><span class="font-bold">IGV: </span>{{ formatTwoDecimals(igv) }}</li>
            <li class="text-xl"><span
                class="font-bold text-green-700">TOTAL A COBRAR: </span>{{ formatTwoDecimals(total) }}
            </li>
          </ul>
        </div>

        <div class="col-span-2">
          <h1 class="font-light text-xl">Sobre el pago</h1>
          <div class="mt-3">
            <div v-if="paymentObject.name === 'EFECTIVO'">
              <div class="grid grid-cols-2">
                <div class="col">
                  <label class="block mb-2 text-sm font-medium text-gray-900" for="large-input">Efectivo
                    Entregado</label>
                  <input id="cash-input"
                         v-model="cashInputValue"
                         class="block w-50 p-4 text-gray-900 text-xl text-red-700 font-bold border border-gray-300 rounded-lg bg-gray-50 text-base focus:ring-green-500 focus:border-green-800"
                         name="cash-input"
                         type="text"
                         @input="onCashInput">
                  <div v-if="!exceedsTotal" class="w-50">
                    <span v-if="!exceedsTotal" class="text-red-700 font-bold text-sm ">El efectivo entregado debe exceder al valor total de pago.</span>
                  </div>
                </div>
                <div class="col">
                  <label class="block mb-2 text-sm font-medium text-gray-900" for="large-input">Cambio</label>
                  <input id="cash-change"
                         v-model="cashChangeValue"
                         class="block w-50 p-4 text-gray-900 text-xl text-green-700 font-bold border border-gray-300 rounded-lg bg-gray-50 text-base focus:ring-green-500 focus:border-green-800"
                         disabled
                         name="cash-change"
                         type="text">
                </div>
              </div>
            </div>
            <div
                v-if="paymentObject.name === 'YAPE' || paymentObject.name === 'TARJETA BANCARIA' || paymentObject.name === 'PLIN'">
              <label class="block mb-2 text-sm font-medium text-gray-900" for="large-input">Código de Pago</label>
              <input id="hash-input"
                     v-model="paymentHash"
                     class="block w-full p-4 text-gray-900 text-green-700 font-bold border border-gray-300 rounded-lg bg-gray-50 text-base focus:ring-green-500 focus:border-green-800"
                     name="hash-input"
                     type="text"
              >
              <div v-if="!verifyHash" class="w-full">
                <span v-if="!verifyHash"
                      class="text-red-700 font-bold text-sm ">Debe ingresar la referencia del pago.</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="flex flex-col items-center ms-5 me-5 mb-5">
        <button
            :disabled="!canSubmit()"
            class="px-5 py-2.5 text-sm font-medium text-white inline-flex items-center bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 rounded-lg text-center disabled:bg-gray-400 disabled:cursor-not-allowed"
            type="button"
            @click="onSubmit"
        >

          <i class="bi bi-send-check w-4 h-4 text-white me-2"></i>
          Finalizar Venta
        </button>
      </div>
    </div>
  </div>
</template>
