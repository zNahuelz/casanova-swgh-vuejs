<script setup>
import {onMounted, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import {useAuthStore} from "@/stores/auth.js";
import {DoctorService} from "@/services/doctor-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM} from "@/utils/constants.js";

const submitting = ref(false);
const router = useRouter();
const route = useRoute();
const authService = useAuthStore();
const doctor = ref({});
const isLoading = ref(false);
const loadError = ref(false);

async function loadDoctor(id) {
  isLoading.value = true;
  try {
    doctor.value = await DoctorService.getById(id);
    isLoading.value = false;
  } catch (err) {
    loadError.value = true;
    Swal.fire(EM.ERROR_TAG, EM.DOCTOR_NOT_FOUND, 'error').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        router.back();
      }
    });
  }
}

function goBack() {
  router.back();
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - GESTIONAR DISPONIBILIDAD : DOCTOR'
  const id = route.params.id;
  if (isNaN(id)) {
    router.back();
  }
  loadDoctor(id);
})
</script>

<template>
<div>
  <h1>Edit doctor availabilities!!</h1>
  <p class="me-5 ms-5 pe-5 ps-5 font-light">{{JSON.stringify(doctor, null, 2)}}</p>
</div>
</template>