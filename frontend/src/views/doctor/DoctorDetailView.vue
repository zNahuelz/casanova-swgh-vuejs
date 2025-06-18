<script setup>
import {useRoute, useRouter} from "vue-router";
import {onMounted, ref} from "vue";
import {DoctorService} from "@/services/doctor-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM} from "@/utils/constants.js";
import {formatAsDatetime} from "@/utils/helpers.js";
import DoctorScheduleModal from "@/components/doctor/DoctorScheduleModal.vue";
import SetUnavailabilityFormModal from "@/components/doctor/SetUnavailabilityFormModal.vue";
import DoctorUnavailabilitiesModal from "@/components/doctor/DoctorUnavailabilitiesModal.vue";
import {useAuthStore} from "@/stores/auth.js";

const router = useRouter();
const route = useRoute();
const isLoading = ref(false);
const loadError = ref(false);
const doctor = ref({});
const showAvailabilitiesModal = ref(false);
const showUnavailabilityFormModal = ref(false);
const showUnavailabilitiesModal = ref(false);
const authService = useAuthStore();


onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - DETALLE DE DOCTOR';
  const id = route.params.id;
  if (isNaN(id)) {
    router.back();
  }
  loadDoctor(id);
});

async function loadDoctor(id) {
  isLoading.value = true;
  try {
    doctor.value = await DoctorService.getById(id);
    loadError.value = false;
    isLoading.value = false;
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, EM.DOCTOR_NOT_FOUND, 'error').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        router.back();
      }
    });
  }
}

function handleAvailabilitiesModal() {
  showAvailabilitiesModal.value = !showAvailabilitiesModal.value;
}

function handleUnavailabilitiesFormModal() {
  showUnavailabilityFormModal.value = !showUnavailabilityFormModal.value;
}

function handleUnavailabilitiesModal() {
  showUnavailabilitiesModal.value = !showUnavailabilitiesModal.value;
}

function goToEdit(id) {
  router.push({name: 'edit-doctor', params: {id}});
}

function goBack() {
  router.back();
}

function goToEditSchedule(id) {
  router.push({name: 'edit-doctor-schedule', params: {id}})
}
</script>
<template>
  <main class="flex flex-col items-center pt-5">
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm  min-w-150">
      <h5 v-if="!isLoading && !loadError" class="mb-2 text-2xl font-bold tracking-tight text-black text-center">DETALLE
        DE DOCTOR</h5>
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
        <h1 class="mt-5 text-2xl font-light">Cargando doctor...</h1>
      </div>
      <div v-if="!isLoading && !loadError" class="space-y-3">

        <div class="border-b pb-4 border-gray-200">
          <h6 class="text-lg font-semibold text-gray-900 mb-4">Información de Auditoría</h6>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-500">Fecha de Registro</label>
                <div class="mt-1 text-sm text-gray-900">
                  {{ formatAsDatetime(doctor?.created_at) }}
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Registrado por</label>
                <div class="mt-1 text-sm text-gray-900">
                  {{ !doctor?.created_by ? 'INSERTADO EN BASE DE DATOS' : doctor.created_by.display_name }}
                </div>
              </div>
            </div>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-500">Última Modificación</label>
                <div class="mt-1 text-sm text-gray-900">
                  {{ formatAsDatetime(doctor?.updated_at) }}
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Modificado por</label>
                <div class="mt-1 text-sm text-gray-900">
                  {{ !doctor?.updated_by ? '---' : doctor.updated_by.display_name }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900 ">ID</label>
            <input :value="doctor?.id"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   disabled
                   type="text"/>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900 ">Nombre</label>
            <input :value="doctor?.name"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   disabled
                   type="text"/>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900 ">Apellidos</label>
            <input :value="doctor?.paternal_surname + ' ' + doctor?.maternal_surname"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                   disabled
                   type="text"/>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900 ">DNI</label>
            <input :value="doctor?.dni"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   disabled
                   type="text"/>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900 ">Correo Electrónico</label>
            <input :value="doctor?.email"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                   disabled
                   type="text"/>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900 ">Teléfono</label>
            <input :value="doctor?.phone"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   disabled
                   type="text"/>
          </div>
          <div class="md:col-span-2">
            <label class="block mb-1 text-sm font-medium text-gray-900 ">Dirección</label>
            <input :value="doctor?.address"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                   disabled
                   type="text"/>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900 ">Nombre de Usuario</label>
            <input :value="doctor?.user?.username"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                   disabled
                   type="text"/>
          </div>
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-900 ">Disponibilidad</label>
            <input
                :value="doctor?.availabilities?.length ? doctor?.availabilities?.length + ' DÍAS' : 'ASIGNACIÓN DE HORARIO PENDIENTE'"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-800 focus:border-green-800 w-full"
                disabled
                type="text"/>
          </div>

          <div>
            <button :disabled="!doctor?.availabilities?.length"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-2 focus:ring-purple-700 focus:text-purple-700 disabled:bg-gray-200 disabled:cursor-not-allowed w-full"
                    type="button"
                    @click="handleAvailabilitiesModal">
              <i class="bi bi-calendar-week w-3 h-3 me-2 flex items-center justify-center"></i>
              {{ doctor?.availabilities?.length ? 'VER HORARIO LABORAL' : 'ASIGNACIÓN DE HORARIO PENDIENTE' }}
            </button>
          </div>

          <div>
            <button :disabled="!doctor.unavailabilities?.length"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-rose-700 focus:z-10 focus:ring-2 focus:ring-rose-700 focus:text-rose-700 disabled:bg-gray-200 disabled:cursor-not-allowed w-full"
                    type="button" @click="handleUnavailabilitiesModal">
              <i class="bi bi-pause-circle w-3 h-3 me-2 flex items-center justify-center"></i>
              {{ doctor?.unavailabilities?.length ? 'VER INDISPONIBILIDADES' : 'SIN INDISPONIBILIDADES' }}
            </button>
          </div>
        </div>

        <div class="flex justify-center mt-5">
          <div class="inline-flex rounded-md shadow-xs" role="group">
            <button
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-red-700 focus:z-10 focus:ring-2 focus:ring-red-700 focus:text-red-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                type="button"
                @click="goBack">
              <i class="bi bi-arrow-return-left w-3 h-3 me-2 flex items-center justify-center"></i>
              Atras
            </button>
            <button
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-e  border-gray-200  hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-2 focus:ring-purple-700 focus:text-purple-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                type="button" @click="goToEditSchedule(doctor?.id)"
                v-if="authService.getTokenDetails().role === 'ADMINISTRADOR' || authService.getTokenDetails().role === 'SECRETARIA'"
            >
              <i class="bi bi-calendar-week w-3 h-3 me-2 flex items-center justify-center"></i>
              Editar Horario
            </button>
            <button
                :disabled="!doctor?.availabilities?.length"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-e  border-gray-200  hover:bg-gray-100 hover:text-rose-700 focus:z-10 focus:ring-2 focus:ring-rose-700 focus:text-rose-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                type="button" @click="handleUnavailabilitiesFormModal"
                v-if="authService.getTokenDetails().role === 'ADMINISTRADOR' || authService.getTokenDetails().role === 'SECRETARIA'"
            >
              <i class="bi bi-pause-circle w-3 h-3 me-2 flex items-center justify-center"></i>
              Pausar citas
            </button>
            <button
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-e border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                type="button"
                @click="goToEdit(doctor?.id)"
                v-if="authService.getTokenDetails().role === 'ADMINISTRADOR' || authService.getTokenDetails().role === 'SECRETARIA'"
            >
              <i class="bi bi-floppy-fill w-3 h-3 me-2 flex items-center justify-center"></i>
              Editar
            </button>
          </div>
        </div>
      </div>
    </div>
    <DoctorScheduleModal v-if="showAvailabilitiesModal" :availabilities="doctor.availabilities"
                         :onClose="handleAvailabilitiesModal"></DoctorScheduleModal>
    <DoctorUnavailabilitiesModal v-if="showUnavailabilitiesModal" :onClose="handleUnavailabilitiesModal"
                                 :unavailabilities="doctor?.unavailabilities"></DoctorUnavailabilitiesModal>
    <SetUnavailabilityFormModal v-if="showUnavailabilityFormModal" :doctor="doctor"
                                :onClose="handleUnavailabilitiesFormModal"></SetUnavailabilityFormModal>
  </main>
</template>
