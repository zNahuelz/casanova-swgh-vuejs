<script setup>
import {onMounted, ref} from "vue";
import {VoucherService} from "@/services/voucher-service.js";
import {useRoute, useRouter} from "vue-router";

const isLoading = ref(false);
const loadError = ref(false);
const voucher = ref({});
const route = useRoute();
const router = useRouter();

async function loadVoucher(id) {
  try {
    isLoading.value = true;
    voucher.value = await VoucherService.get(id);
    console.log(voucher.value);
  } catch (err) {
    loadError.value = true;
    console.log(err);
  }
  finally{
    isLoading.value = false;
  }
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - DETALLE DE VOUCHER';
  const id = route.params.id;
  if (isNaN(id)) {
    router.back();
  }
  loadVoucher(id);
});
//TODO: WIP!
</script>

<template>
{{JSON.stringify(voucher)}}
</template>
