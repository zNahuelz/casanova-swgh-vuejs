<script setup>
import {useRouter} from "vue-router";

const router = useRouter();

function goBack(){
  router.back();
}
</script>

<template>
<div class="h-screen flex justify-center items-center">
  <div class="flex flex-col items-center">
    <img src="/images/unauthorized_transparent.png" alt="Unauthorized Icon" width="250px" draggable="false" @contextmenu.prevent>
    <h1 class="text-2xl font-light text-center mt-5">Oops! Su cuenta no posee los permisos necesarios para ingresar a esta sección. <br> Comuníquese con administración o vuelva a intentarlo.</h1>
    <h1 class="text-green-800 font-bold text-2xl hover:underline" @click="goBack">Regresar</h1>
  </div>
</div>
</template>