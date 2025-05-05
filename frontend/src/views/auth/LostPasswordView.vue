<script setup>
import {onMounted, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import SendRecoveryEmailForm from "@/components/auth/SendRecoveryEmailForm.vue";
import ChangePasswordWithToken from "@/components/auth/ChangePasswordWithToken.vue";
import {UserService} from "@/services/user-service.js";
import Swal from "sweetalert2";
import {ERROR_MESSAGES as EM} from "@/utils/constants.js";

const route = useRoute();
const router = useRouter();

const recoveryToken = route.query.token;
const showRecoveryForm = ref(false);
const showChangePasswordForm = ref(false);

const isLoading = ref(false);

async function verifyToken() {
  try {
    isLoading.value = true;
    const response = await UserService.verifyRecoveryToken({token: recoveryToken});
    showChangePasswordForm.value = true;
  } catch (err) {
    showChangePasswordForm.value = false;
    Swal.fire(EM.ERROR_TAG, EM.INVALID_RECOVERY_TOKEN, 'error').then((r) => {
      if (r.dismiss || r.isDismissed || r.isConfirmed) {
        router.push({name: 'login'});
      }
    });
  } finally {
    isLoading.value = false;
  }
}

function isValidToken() {
  const token = recoveryToken;
  return token && token.length === 100;
}

onMounted(() => {
  document.title = 'ALTERNATIVA CASANOVA - RECUPERACIÓN DE CUENTA'
  showRecoveryForm.value = !isValidToken();
  if (isValidToken()) {
    verifyToken();
  }
});

</script>

<template>
  <main v-if="isLoading" class="h-screen flex justify-center items-center bg-slate-200">
    <div class="w-full max-w-sm p-4 bg-white border border-gray-200 rounded-lg shadow-lg sm:p-6 md:p-8">
      <div class="container mt-5 mb-5 flex flex-col items-center">
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
        <h1 class="mt-5 text-2xl font-light">Validando información...</h1>
      </div>
    </div>
  </main>
  <SendRecoveryEmailForm v-if="showRecoveryForm && !isLoading"></SendRecoveryEmailForm>
  <ChangePasswordWithToken v-if="showChangePasswordForm && !isLoading" :token="recoveryToken"></ChangePasswordWithToken>
</template>