<script setup>
import {computed, onMounted, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import {useAuthStore} from "@/stores/auth.js";
import {DoctorService} from "@/services/doctor-service.js";
import Swal from "sweetalert2";
import {
  DEFAULT_DOCTOR_AVAILABILITIES as WORK_DAYS,
  ERROR_MESSAGES as EM,
  SUCCESS_MESSAGES as SM
} from "@/utils/constants.js";
import WeekdayConfigCard from "@/components/doctor/WeekdayConfigCard.vue";
import {getWeekdayName, reloadOnDismiss} from "@/utils/helpers.js";
import {SettingService} from "@/services/setting-service.js";

const submitting = ref(false);
const router = useRouter();
const route = useRoute();
const authService = useAuthStore();
const doctor = ref({});
const isLoading = ref(false);
const loadError = ref(false);
const workDays = ref([...WORK_DAYS]);
const useRemote = ref(false);
const CONFIG_KEY = ref('ESTADO_TRABAJO_FINDES');
const weekendsAllowed = ref('false');

async function loadDoctor(id) {
  isLoading.value = true;
  try {
    doctor.value = await DoctorService.getById(id);
    isLoading.value = false;
    if (doctor.value.availabilities && doctor.value.availabilities.length >= 1) {
      doctor.value.availabilities = doctor.value.availabilities.map(e => ({
        ...e,
        label: getWeekdayName(e.weekday)
      }));
      useRemote.value = true;
    }
    await getConfig();
  } catch (err) {
    loadError.value = true;
    Swal.fire(EM.ERROR_TAG, EM.DOCTOR_NOT_FOUND, 'error').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        router.back();
      }
    });
  }
}

async function getConfig() {
  isLoading.value = true;
  try {
    const response = await SettingService.getByKey(CONFIG_KEY.value);
    weekendsAllowed.value = response.value;
    updateWeekends();
  } catch (err) {
    Swal.fire(EM.ERROR_TAG, EM.WEEKENDS_CONFIG_NOT_FOUND, 'error');
  } finally {
    isLoading.value = false;
  }
}

function updateWeekends() {
  const targetList = useRemote.value ? doctor.value.availabilities : workDays.value;

  if (weekendsAllowed.value === 'false') {
    for (let i = targetList.length - 1; i >= 0; i--) {
      if (targetList[i].weekday === 6 || targetList[i].weekday === 7) {
        targetList.splice(i, 1);
      }
    }
  } else {
    [6, 7].forEach(weekday => {
      const exists = targetList.some(d => d.weekday === weekday);
      if (!exists) {
        const defaultDay = WORK_DAYS.find(d => d.weekday === weekday);
        if (defaultDay) {
          targetList.push({...defaultDay});
        }
      }
    });
  }
}

function handleWeekdayUpdate(updatedDay) {
  const targetList = useRemote.value ? doctor.value.availabilities : workDays.value;
  const idx = targetList.findIndex(d => d.weekday === updatedDay.weekday);
  if (idx !== -1) {
    // Replace entire object so Vue notices
    targetList.splice(idx, 1, {...updatedDay});
  }
}

const weekdaysValid = computed(() => {
  const days = useRemote.value ? doctor.value.availabilities : workDays.value;

  const allValid = days.every(d => {
    if (!d.start_time || !d.end_time || d.start_time >= d.end_time) return false;

    if ((d.break_start && !d.break_end) || (!d.break_start && d.break_end)) return false;

    if (d.break_start && d.break_end) {
      if (d.break_start >= d.break_end) return false;
      if (d.break_start < d.start_time || d.break_end > d.end_time) return false;
      const [bh, bm] = d.break_start.split(':').map(Number);
      const [eh, em] = d.break_end.split(':').map(Number);
      const breakMinutes = (eh * 60 + em) - (bh * 60 + bm);
      if (breakMinutes > 60) return false;
    }

    return true;
  });

  const anyActive = days.some(d => d.is_active);

  return allValid && anyActive;
});

const daysToShow = computed(() => {
  return (doctor.value.availabilities?.length ?? 0) > 0
      ? doctor.value.availabilities
      : workDays.value;
});

async function onSubmit() {
  if (weekdaysValid) {
    submitting.value = true;
    const targetList = useRemote.value ? doctor.value.availabilities : workDays.value;
    const formatted = targetList.map(item => ({
      ...item,
      start_time: item.start_time?.slice(0, 5),
      end_time: item.end_time?.slice(0, 5),
      break_start: item.break_start?.slice(0, 5),
      break_end: item.break_end?.slice(0, 5),
    }));
    try {
      const response = await DoctorService.updateAvailabilities(doctor.value.id, formatted);
      Swal.fire(SM.SUCCESS_TAG, response.message, 'success').then((r) => reloadOnDismiss(r));
    } catch (err) {
      Swal.fire(EM.ERROR_TAG, EM.SERVER_ERROR, 'error').then((r) => reloadOnDismiss(r));
    }
  } else {
    Swal.fire(EM.ERROR_TAG, EM.INCORRECT_SCHEDULE, 'error').then((r) => reloadOnDismiss(r));
  }
}

function goBack() {
  router.back();
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - GESTIONAR DISPONIBILIDAD DE DOCTOR'
  const id = route.params.id;
  if (isNaN(id)) {
    router.back();
  }
  loadDoctor(id);
})
</script>

<template>
  <main class="flex flex-col items-center pt-5">
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm  min-w-150">
      <h5 v-if="!isLoading && !loadError" class="mb-2 text-2xl font-bold tracking-tight text-black text-center">
        GESTIONAR DISPONIBILIDAD</h5>
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
        <h1 class="mt-5 text-2xl font-light">Cargando horario del doctor...</h1>
      </div>
      <div v-if="!isLoading && !loadError" class="space-y-3">
        <div class="container">
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
              Modificando horario del doctor: <span class="font-medium">{{
                `${doctor?.name} ${doctor?.paternal_surname} ${doctor?.maternal_surname}`
              }}</span> <br> ID: <span class="font-medium">{{ doctor?.id }}</span> - DNI: <span
                class="font-medium">{{ doctor?.dni }}</span>
            </div>
          </div>
        </div>

        <div v-if="!isLoading && !submitting" class="container text-center mt-5">
          <p :class="{'text-green-600': weekdaysValid, 'text-red-600': !weekdaysValid}" class="mt-2 font-bold">
            {{
              weekdaysValid ? '- Horario de Trabajo Válido -' : '- Horario de Trabajo Inválido: Algunos días tienen horarios incorrectos -'
            }}
          </p>
        </div>

        <div v-show="!submitting" class="container">
          <weekday-config-card
              v-for="day in daysToShow"
              :key="day.weekday"
              :weekdayInfo="day"
              @update:weekdayInfo="handleWeekdayUpdate"
          />
        </div>

        <div v-if="!isLoading && !submitting" class="container text-center mt-5">
          <p :class="{'text-green-600': weekdaysValid, 'text-red-600': !weekdaysValid}" class="mt-2 font-bold">
            {{
              weekdaysValid ? '- Horario de Trabajo Válido -' : '- Horario de Trabajo Inválido: Algunos días tienen horarios incorrectos -'
            }}
          </p>
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
                :disabled="!weekdaysValid || submitting || isLoading"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-2 focus:ring-green-700 focus:text-green-700 disabled:bg-gray-200 disabled:cursor-not-allowed"
                type="button" @click="onSubmit">
              <i class="bi bi-floppy-fill w-3 h-3 me-2 flex items-center justify-center"></i>
              {{ submitting ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>