<script setup>
import {watch} from 'vue';
import {ErrorMessage, Field, useForm} from 'vee-validate';
import * as yup from 'yup';
import dayjs from 'dayjs';
import isBetween from 'dayjs/plugin/isBetween';
import customParseFormat from 'dayjs/plugin/customParseFormat'

dayjs.extend(customParseFormat)
dayjs.extend(isBetween);

const props = defineProps({
  weekdayInfo: Object,
});

const emit = defineEmits(['update:weekdayInfo']);

const parseTime = (timeStr) => {
  if (!timeStr) return null;

  const cleaned = timeStr.replace(/\s*(AM|PM|am|pm)/, '').trim();

  let parsed = dayjs(cleaned, ['HH:mm', 'H:mm'], true);
  if (!parsed.isValid()) {
    parsed = dayjs(timeStr, ['h:mm A', 'h:mm a'], true);
  }

  return parsed.isValid() ? parsed : null;
};

const schema = yup.object({
  start_time: yup.string()
      .required('Debe ingresar una hora de inicio.')
      .test('valid-time', 'Formato de incorrecto (HH:mm)', value => parseTime(value)?.isValid()),

  end_time: yup.string()
      .required('End time is required')
      .test('valid-time', 'Formato de incorrecto (HH:mm)', value => parseTime(value)?.isValid())
      .test('is-after-start', 'Debe ser posterior a la hora de inicio', function (value) {
        const start = parseTime(this.parent.start_time);
        const end = parseTime(value);
        return start && end ? end.isAfter(start) : true;
      }),

  break_start: yup.string()
      .test('valid-time', 'Formato de incorrecto (HH:mm)', value => !value || parseTime(value)?.isValid())
      .test('within-hours', 'Debe estar en el horario laboral.', function (value) {
        if (!value) return true;
        const start = parseTime(this.parent.start_time);
        const end = parseTime(this.parent.end_time);
        const breakStart = parseTime(value);
        return start && end && breakStart ? breakStart.isBetween(start, end, null, '[)') : true;
      }),

  break_end: yup.string()
      .test('valid-time', 'Formato de incorrecto (HH:mm)', value => !value || parseTime(value)?.isValid())
      .test('after-break-start', 'Debe ser posterior al inicio del descanso.', function (value) {
        if (!value || !this.parent.break_start) return true;
        const breakStart = parseTime(this.parent.break_start);
        const breakEnd = parseTime(value);
        return breakStart && breakEnd ? breakEnd.isAfter(breakStart) : true;
      })
      .test('within-hours', 'Debe estar en el horario laboral.', function (value) {
        if (!value || !this.parent.break_start) return true;
        const start = parseTime(this.parent.start_time);
        const end = parseTime(this.parent.end_time);
        const breakEnd = parseTime(value);
        return start && end && breakEnd ? breakEnd.isBetween(start, end, null, '(]') : true;
      })
      .test('break-max-duration', 'El descanso no debe durar más de 1 hora.', function (value) {
        if (!value || !this.parent.break_start) return true;
        const breakStart = parseTime(this.parent.break_start);
        const breakEnd = parseTime(value);
        return breakStart && breakEnd
            ? breakEnd.diff(breakStart, 'minute') <= 60
            : true;
      })
});

const {values, setFieldValue, resetForm} = useForm({
  validationSchema: schema,
  initialValues: props.weekdayInfo,
  validateOnChange: true,
  validateOnBlur: true,
  validateOnMount: true,
});

watch(values, (newValues) => {
  emit('update:weekdayInfo', newValues);
}, {deep: true});

const handleTimeChange = (field, event) => {
  let raw = event.target.value?.trim() ?? '';

  const cleaned = raw.replace(/\s*(AM|PM|am|pm)/, '').trim();

  let parsed = dayjs(cleaned, ['HH:mm', 'H:mm'], true);
  if (!parsed.isValid()) {
    parsed = dayjs(raw, ['h:mm A', 'h:mm a'], true);
  }

  if (parsed.isValid()) {
    setFieldValue(field, parsed.format('HH:mm'));
  } else {
    setFieldValue(field, raw);
  }
};
</script>

<template>
  <div class="max-w-xl p-6 bg-white border border-gray-200 rounded-lg shadow-sm mb-3 w-full overflow-y-auto">
    <!-- Header with label on left, checkbox on right -->
    <div class="flex justify-between items-center mb-4">
      <h5 class="mb-4 text-lg font-bold tracking-tight text-gray-900">
        {{ weekdayInfo.label }}
      </h5>
      <label class="inline-flex items-center space-x-2">
        <input
            v-model="weekdayInfo.is_active"
            class="w-5 h-5 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500"
            type="checkbox"
        />
        <span class="text-sm font-medium text-gray-900">Activo</span>
      </label>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <!-- Start Time -->
      <div>
        <label class="block mb-2 text-sm font-medium text-gray-900" for="start_time">Hora Inicio</label>
        <div class="flex">
          <Field v-slot="{ field, meta }" name="start_time">
            <input
                id="start_time"
                :class="{ 'border-red-500': meta.touched && !meta.valid }"
                class="rounded-none rounded-s-lg bg-gray-50 border text-gray-900 leading-none focus:ring-green-800 focus:border-green-800 block flex-1 w-full text-sm border-gray-300 p-2.5"
                type="time"
                v-bind="field"
                @input="handleTimeChange('start_time', $event)"
            >
          </Field>
          <span
              class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-s-0 border-s-0 border-gray-300 rounded-e-md"
              @click="$refs.startTimeInput?.focus()"
          >
            <svg aria-hidden="true" class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg">
              <path clip-rule="evenodd"
                    d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414L13 11.586V8Z"
                    fill-rule="evenodd"/>
            </svg>
          </span>
        </div>
        <ErrorMessage class="mt-1 text-sm text-red-600" name="start_time"/>
      </div>

      <!-- End Time -->
      <div>
        <label class="block mb-2 text-sm font-medium text-gray-900" for="end_time">Hora Fin</label>
        <div class="flex">
          <Field v-slot="{ field, meta }" name="end_time">
            <input
                id="end_time"
                :class="{ 'border-red-500': meta.touched && !meta.valid }"
                class="rounded-none rounded-s-lg bg-gray-50 border text-gray-900 leading-none focus:ring-green-800 focus:border-green-800 block flex-1 w-full text-sm border-gray-300 p-2.5"
                type="time"
                v-bind="field"
                @input="handleTimeChange('end_time', $event)"
            >
          </Field>
          <span
              class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-s-0 border-s-0 border-gray-300 rounded-e-md"
              @click="$refs.endTimeInput?.focus()"
          >
            <svg aria-hidden="true" class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg">
              <path clip-rule="evenodd"
                    d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414L13 11.586V8Z"
                    fill-rule="evenodd"/>
            </svg>
          </span>
        </div>
        <ErrorMessage class="mt-1 text-sm text-red-600" name="end_time"/>
      </div>

      <!-- Break Start -->
      <div>
        <label class="block mb-2 text-sm font-medium text-gray-900" for="break_start">Inicio Descanso</label>
        <div class="flex">
          <Field v-slot="{ field, meta }" name="break_start">
            <input
                id="break_start"
                :class="{ 'border-red-500': meta.touched && !meta.valid }"
                class="rounded-none rounded-s-lg bg-gray-50 border text-gray-900 leading-none focus:ring-green-800 focus:border-green-800 block flex-1 w-full text-sm border-gray-300 p-2.5"
                type="time"
                v-bind="field"
                @input="handleTimeChange('break_start', $event)"
            >
          </Field>
          <span
              class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-s-0 border-s-0 border-gray-300 rounded-e-md"
              @click="$refs.breakStartInput?.focus()"
          >
            <svg aria-hidden="true" class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg">
              <path clip-rule="evenodd"
                    d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414L13 11.586V8Z"
                    fill-rule="evenodd"/>
            </svg>
          </span>
        </div>
        <ErrorMessage class="mt-1 text-sm text-red-600" name="break_start"/>
      </div>

      <!-- Break End -->
      <div>
        <label class="block mb-2 text-sm font-medium text-gray-900" for="break_end">Fin Descanso</label>
        <div class="flex">
          <Field v-slot="{ field, meta }" name="break_end">
            <input
                id="break_end"
                :class="{ 'border-red-500': meta.touched && !meta.valid }"
                class="rounded-none rounded-s-lg bg-gray-50 border text-gray-900 leading-none focus:ring-green-800 focus:border-green-800 block flex-1 w-full text-sm border-gray-300 p-2.5"
                type="time"
                v-bind="field"
                @input="handleTimeChange('break_end', $event)"
            >
          </Field>
          <span
              class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-s-0 border-s-0 border-gray-300 rounded-e-md"
              @click="$refs.breakEndInput?.focus()"
          >
            <svg aria-hidden="true" class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg">
              <path clip-rule="evenodd"
                    d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414L13 11.586V8Z"
                    fill-rule="evenodd"/>
            </svg>
          </span>
        </div>
        <ErrorMessage class="mt-1 text-sm text-red-600" name="break_end"/>
      </div>
    </div>
  </div>
</template>