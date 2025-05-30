<script setup>
import {onMounted, ref} from "vue";
import {AppointmentService} from "@/services/appointment-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM} from "@/utils/constants.js";
import {useRoute, useRouter} from "vue-router";
import {useAuthStore} from "@/stores/auth.js";

const router = useRouter();
const route = useRoute();
const authService = useAuthStore();
const appointment = ref({});
const isLoading = ref(false);
const loadError = ref(false);

//TODO: 1** Change doctor is possible.
//TODO: Complete this.
//Use other UI instead of prepareAppointmentView.

async function loadAppointment(id) {
  isLoading.value = true;
  try {
    appointment.value = await AppointmentService.getById(id);
    loadError.value = false;
    isLoading.value = false;
    console.log(appointment.value);
  } catch (err) {
    loadError.value = true;
    Swal.fire(EM.ERROR_TAG, EM.APPOINTMENT_NOT_FOUND, 'error').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        router.back();
      }
    });
  }
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - REPROGRAMACIÓN DE CITA';
  const id = route.params.id;
  if (isNaN(id)) {
    router.back();
  }
  loadAppointment(id);
});
</script>

<template>

</template>
