<script setup>
import {onMounted} from 'vue';
import {useAuthStore} from "@/stores/auth.js";
import dayjs from 'dayjs';
import 'dayjs/locale/es';

const authService = useAuthStore();

dayjs.locale('es');

const currentDate = dayjs().format('dddd D [de] MMMM [del] YYYY');

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - INICIO'
});

</script>
<template>
  <main class="flex flex-col items-center pt-5">
    <img src="/images/logo_transparent.png" alt="Logo Compañia" width="250"
         height="250"/>
    <h1 class="text-2xl text-green-900 font-bold mb-2">¡Bienvenido! {{ authService.getTokenDetails().username }}</h1>
    <h1 class="text-2xl text-green-900 font-bold ">
      Hoy es... {{ currentDate }}</h1>
  </main>
</template>