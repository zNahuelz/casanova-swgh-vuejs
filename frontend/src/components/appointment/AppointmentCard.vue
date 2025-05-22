<script setup>
const props = defineProps({
  dayInfo: Object,
});

const emit = defineEmits(['selectedDay']);
</script>

<template>
  <div class="w-full max-w-50 bg-gray-200 border border-dark-50 rounded-lg shadow-sm">
    <div class="p-4">
      <div class="flex flex-col justify-center items-center">

          <span class="font-bold">{{dayInfo?.weekdayName?.toUpperCase()}}</span>
          <span>{{dayInfo.dateKey}}</span>
          <span>{{`${dayInfo.slots.length} Citas Disponibles`}}</span>
          <span v-show="!dayInfo.is_available" class="text-red-700 font-bold">{{dayInfo?.reason?.toUpperCase()}}</span>

        <div class="mt-2" v-if="dayInfo.is_available === true">
          <button type="button" :disabled="dayInfo.is_available === false" @click="emit('selectedDay',dayInfo)"
                  class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5">
            Detalles</button>
        </div>
      </div>
    </div>
  </div>
</template>