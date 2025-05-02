<script setup>
import {initFlowbite} from 'flowbite';
import {onMounted} from 'vue';
import {RouterView, useRouter} from 'vue-router';
import {useAuthStore} from '@/stores/auth';

const authStore = useAuthStore();

onMounted(() => {
  initFlowbite();
});
</script>
<template>
  <aside id="logo-sidebar"
         class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0"
         aria-label="Sidebar">
    <div class="h-full px-3 py-4 overflow-y-auto bg-gray-50">
      <a class="flex items-center ps-2.5 mb-5">
        <img src="/images/logo_nw_transparent.png" class="h-6 me-3 sm:h-7" alt="Logo Compañia" width="24"
             height="24"/>
        <span class="self-center text-lg font-semibold whitespace-nowrap text-green-900">Alternativa Casanova</span>
      </a>
      <ul class="space-y-2 font-medium">
        <li>
          <router-link :to="{name: 'dashboard'}"
                       class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-green-500 group">
            <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true"
                 xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
              <path fill-rule="evenodd"
                    d="M11.293 3.293a1 1 0 0 1 1.414 0l6 6 2 2a1 1 0 0 1-1.414 1.414L19 12.414V19a2 2 0 0 1-2 2h-3a1 1 0 0 1-1-1v-3h-2v3a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2v-6.586l-.293.293a1 1 0 0 1-1.414-1.414l2-2 6-6Z"
                    clip-rule="evenodd"/>
            </svg>
            <span class="ms-3">Inicio</span>
          </router-link>
        </li>
        <li>
          <button type="button"
                  class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-green-500"
                  aria-controls="appointments-dropdown" data-collapse-toggle="appointments-dropdown">
            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                 aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                 viewBox="0 0 24 24">
              <path fill-rule="evenodd"
                    d="M18 3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1V9a4 4 0 0 0-4-4h-3a1.99 1.99 0 0 0-1 .267V5a2 2 0 0 1 2-2h7Z"
                    clip-rule="evenodd"/>
              <path fill-rule="evenodd"
                    d="M8 7.054V11H4.2a2 2 0 0 1 .281-.432l2.46-2.87A2 2 0 0 1 8 7.054ZM10 7v4a2 2 0 0 1-2 2H4v6a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3Z"
                    clip-rule="evenodd"/>
            </svg>
            <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Citas</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m1 1 4 4 4-4"/>
            </svg>
          </button>
          <ul id="appointments-dropdown" class="hidden py-2 space-y-2">
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">Nueva</a>
            </li>
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">Listado</a>
            </li>
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">3</a>
            </li>
          </ul>
        </li>
        <li>
          <button type="button"
                  class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-green-500"
                  aria-controls="doctors-dropdown" data-collapse-toggle="doctors-dropdown">
            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                 aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                 viewBox="0 0 24 24">
              <path fill-rule="evenodd"
                    d="M12 6a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm-1.5 8a4 4 0 0 0-4 4 2 2 0 0 0 2 2h7a2 2 0 0 0 2-2 4 4 0 0 0-4-4h-3Zm6.82-3.096a5.51 5.51 0 0 0-2.797-6.293 3.5 3.5 0 1 1 2.796 6.292ZM19.5 18h.5a2 2 0 0 0 2-2 4 4 0 0 0-4-4h-1.1a5.503 5.503 0 0 1-.471.762A5.998 5.998 0 0 1 19.5 18ZM4 7.5a3.5 3.5 0 0 1 5.477-2.889 5.5 5.5 0 0 0-2.796 6.293A3.501 3.501 0 0 1 4 7.5ZM7.1 12H6a4 4 0 0 0-4 4 2 2 0 0 0 2 2h.5a5.998 5.998 0 0 1 3.071-5.238A5.505 5.505 0 0 1 7.1 12Z"
                    clip-rule="evenodd"/>
            </svg>
            <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Doctores</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m1 1 4 4 4-4"/>
            </svg>
          </button>
          <ul id="doctors-dropdown" class="hidden py-2 space-y-2">
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">Nuevo</a>
            </li>
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">Listado</a>
            </li>
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">3</a>
            </li>
          </ul>
        </li>

        <li>
          <button type="button"
                  class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-green-500"
                  aria-controls="vouchers-dropdown" data-collapse-toggle="vouchers-dropdown">
            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                 aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                 viewBox="0 0 24 24">
              <path fill-rule="evenodd" d="M7 6a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-2v-4a3 3 0 0 0-3-3H7V6Z"
                    clip-rule="evenodd"/>
              <path fill-rule="evenodd"
                    d="M2 11a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-7Zm7.5 1a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5Z"
                    clip-rule="evenodd"/>
              <path d="M10.5 14.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z"/>
            </svg>
            <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Facturación</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m1 1 4 4 4-4"/>
            </svg>
          </button>
          <ul id="vouchers-dropdown" class="hidden py-2 space-y-2">
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">Listado</a>
            </li>
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">2</a>
            </li>
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">3</a>
            </li>
          </ul>
        </li>
        <li>
          <button type="button"
                  class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-green-500"
                  aria-controls="medicines-dropdown" data-collapse-toggle="medicines-dropdown">
            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                 aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                 viewBox="0 0 24 24">
              <path
                  d="M12.013 6.175 7.006 9.369l5.007 3.194-5.007 3.193L2 12.545l5.006-3.193L2 6.175l5.006-3.194 5.007 3.194ZM6.981 17.806l5.006-3.193 5.006 3.193L11.987 21l-5.006-3.194Z"/>
              <path
                  d="m12.013 12.545 5.006-3.194-5.006-3.176 4.98-3.194L22 6.175l-5.007 3.194L22 12.562l-5.007 3.194-4.98-3.211Z"/>
            </svg>
            <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Medicamentos</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m1 1 4 4 4-4"/>
            </svg>
          </button>
          <ul id="medicines-dropdown" class="hidden py-2 space-y-2">
            <li>
              <router-link :to="{name: 'new-medicine'}"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">Nuevo</router-link>
            </li>
            <li>
              <router-link :to="{name: 'medicine-list'}"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">Listado</router-link>
            </li>
            <li>
              <router-link :to="{name: 'presentation-list'}"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">Presentaciones</router-link>
            </li>
          </ul>
        </li>

        <li>
          <button type="button"
                  class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-green-500"
                  aria-controls="suppliers-dropdown" data-collapse-toggle="suppliers-dropdown">


            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                 aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                 viewBox="0 0 24 24">
              <path fill-rule="evenodd"
                    d="M6 2a2 2 0 0 0-2 2v15a3 3 0 0 0 3 3h12a1 1 0 1 0 0-2h-2v-2h2a1 1 0 0 0 1-1V4a2 2 0 0 0-2-2h-8v16h5v2H7a1 1 0 1 1 0-2h1V2H6Z"
                    clip-rule="evenodd"/>
            </svg>

            <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Proveedores</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m1 1 4 4 4-4"/>
            </svg>
          </button>
          <ul id="suppliers-dropdown" class="hidden py-2 space-y-2">
            <li>
              <router-link :to="{name: 'new-supplier'}"
                           class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">
                Nuevo
              </router-link>
            </li>
            <li>
              <router-link :to="{name: 'supplier-list'}"
                           class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">
                Listado
              </router-link>
            </li>
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">3</a>
            </li>
          </ul>
        </li>
        <li>
          <button type="button"
                  class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-green-500"
                  aria-controls="workers-dropdown" data-collapse-toggle="workers-dropdown">
            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                 aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                 viewBox="0 0 24 24">
              <path fill-rule="evenodd"
                    d="M8 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4H6Zm7.25-2.095c.478-.86.75-1.85.75-2.905a5.973 5.973 0 0 0-.75-2.906 4 4 0 1 1 0 5.811ZM15.466 20c.34-.588.535-1.271.535-2v-1a5.978 5.978 0 0 0-1.528-4H18a4 4 0 0 1 4 4v1a2 2 0 0 1-2 2h-4.535Z"
                    clip-rule="evenodd"/>
            </svg>
            <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Trabajadores</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m1 1 4 4 4-4"/>
            </svg>
          </button>
          <ul id="workers-dropdown" class="hidden py-2 space-y-2">
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">Nuevo</a>
            </li>
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">Listado</a>
            </li>
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">3</a>
            </li>
          </ul>
        </li>
        <li>
          <button type="button"
                  class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-green-500"
                  aria-controls="treatments-dropdown" data-collapse-toggle="treatments-dropdown">
            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                 aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                 viewBox="0 0 24 24">
              <path
                  d="m7.4 3.736 3.43 3.429A5.046 5.046 0 0 1 12.133 7c.356.01.71.06 1.056.147l3.41-3.412a2.32 2.32 0 0 1 .451-.344A9.89 9.89 0 0 0 12.268 2a10.022 10.022 0 0 0-5.322 1.392c.165.095.318.211.454.344Zm11.451 1.54-.127-.127a.5.5 0 0 0-.706 0l-2.932 2.932c.03.023.05.054.078.077.237.194.454.41.651.645.033.038.077.067.11.107l2.926-2.927a.5.5 0 0 0 0-.707Zm-2.931 9.81c-.025.03-.058.052-.082.082a4.97 4.97 0 0 1-.633.639c-.04.036-.072.083-.115.117l2.927 2.927a.5.5 0 0 0 .707 0l.127-.127a.5.5 0 0 0 0-.707l-2.932-2.931Zm-1.443-4.763a3.037 3.037 0 0 0-1.383-1.1l-.012-.007a2.956 2.956 0 0 0-1-.213H12a2.964 2.964 0 0 0-2.122.893c-.285.29-.509.634-.657 1.013l-.009.016a2.96 2.96 0 0 0-.21 1 2.99 2.99 0 0 0 .488 1.716l.032.04a3.04 3.04 0 0 0 1.384 1.1l.012.007c.319.129.657.2 1 .213.393.015.784-.05 1.15-.192.012-.005.021-.013.033-.018a3.01 3.01 0 0 0 1.676-1.7v-.007a2.89 2.89 0 0 0 0-2.207 2.868 2.868 0 0 0-.27-.515c-.007-.012-.02-.025-.03-.039Zm6.137-3.373a2.53 2.53 0 0 1-.349.447l-3.426 3.426c.112.428.166.869.161 1.311a4.954 4.954 0 0 1-.148 1.054l3.413 3.412c.133.134.249.283.347.444A9.88 9.88 0 0 0 22 12.269a9.913 9.913 0 0 0-1.386-5.319ZM16.6 20.264l-3.42-3.421c-.386.1-.782.152-1.18.157h-.135c-.356-.01-.71-.06-1.056-.147L7.4 20.265a2.503 2.503 0 0 1-.444.347A9.884 9.884 0 0 0 11.732 22H12a9.9 9.9 0 0 0 5.044-1.388 2.515 2.515 0 0 1-.444-.348ZM3.735 16.6l3.426-3.426a4.608 4.608 0 0 1-.013-2.367L3.735 7.4a2.508 2.508 0 0 1-.349-.447 9.889 9.889 0 0 0 0 10.1 2.48 2.48 0 0 1 .35-.453Zm5.101-.758a4.959 4.959 0 0 1-.65-.645c-.034-.038-.078-.067-.11-.107L5.15 18.017a.5.5 0 0 0 0 .707l.127.127a.5.5 0 0 0 .706 0l2.932-2.933c-.029-.018-.049-.053-.078-.076Zm-.755-6.928c.03-.037.07-.063.1-.1.183-.22.383-.423.6-.609.046-.04.081-.092.128-.13L5.983 5.149a.5.5 0 0 0-.707 0l-.127.127a.5.5 0 0 0 0 .707l2.932 2.931Z"/>
            </svg>
            <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Tratamientos</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m1 1 4 4 4-4"/>
            </svg>
          </button>
          <ul id="treatments-dropdown" class="hidden py-2 space-y-2">
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">Nuevo</a>
            </li>
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">Listado</a>
            </li>
            <li>
              <a href="#"
                 class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-green-500">3</a>
            </li>
          </ul>
        </li>
        <li>
          <a href="#"
             class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-green-500 group">
            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                 aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                 viewBox="0 0 24 24">
              <path fill-rule="evenodd"
                    d="M11.32 6.176H5c-1.105 0-2 .949-2 2.118v10.588C3 20.052 3.895 21 5 21h11c1.105 0 2-.948 2-2.118v-7.75l-3.914 4.144A2.46 2.46 0 0 1 12.81 16l-2.681.568c-1.75.37-3.292-1.263-2.942-3.115l.536-2.839c.097-.512.335-.983.684-1.352l2.914-3.086Z"
                    clip-rule="evenodd"/>
              <path fill-rule="evenodd"
                    d="M19.846 4.318a2.148 2.148 0 0 0-.437-.692 2.014 2.014 0 0 0-.654-.463 1.92 1.92 0 0 0-1.544 0 2.014 2.014 0 0 0-.654.463l-.546.578 2.852 3.02.546-.579a2.14 2.14 0 0 0 .437-.692 2.244 2.244 0 0 0 0-1.635ZM17.45 8.721 14.597 5.7 9.82 10.76a.54.54 0 0 0-.137.27l-.536 2.84c-.07.37.239.696.588.622l2.682-.567a.492.492 0 0 0 .255-.145l4.778-5.06Z"
                    clip-rule="evenodd"/>
            </svg>
            <span class="flex-1 ms-3 whitespace-nowrap">Configuración</span>
          </a>
        </li>
        <li>
          <a href="#"
             class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-green-500 group">
            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                 aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                 viewBox="0 0 24 24">
              <path fill-rule="evenodd"
                    d="M12 20a7.966 7.966 0 0 1-5.002-1.756l.002.001v-.683c0-1.794 1.492-3.25 3.333-3.25h3.334c1.84 0 3.333 1.456 3.333 3.25v.683A7.966 7.966 0 0 1 12 20ZM2 12C2 6.477 6.477 2 12 2s10 4.477 10 10c0 5.5-4.44 9.963-9.932 10h-.138C6.438 21.962 2 17.5 2 12Zm10-5c-1.84 0-3.333 1.455-3.333 3.25S10.159 13.5 12 13.5c1.84 0 3.333-1.455 3.333-3.25S13.841 7 12 7Z"
                    clip-rule="evenodd"/>
            </svg>
            <span class="flex-1 ms-3 whitespace-nowrap">Mi Cuenta</span>
          </a>
        </li>
        <li @click="authStore.logout()">
          <a
              class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-green-500 group">
            <svg
                class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 16">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M1 8h11m0 0L8 4m4 4-4 4m4-11h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-3"/>
            </svg>
            <span class="flex-1 ms-3 whitespace-nowrap">Cerrar Sesión</span>
          </a>
        </li>
      </ul>
    </div>
  </aside>

  <div class=" sm:ml-64 ">
    <div class="bg-gray-200 min-h-screen"> <!----Problems? h-screen-->
      <RouterView class="h-full"/> <!---No css here.-->
    </div>
  </div>
</template>