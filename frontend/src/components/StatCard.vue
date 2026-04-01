<template>
  <div class="card p-5">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ label }}</p>
        <p class="mt-1 text-3xl font-bold" :class="textColor">{{ value }}</p>
      </div>
      <div :class="iconBg" class="flex h-12 w-12 items-center justify-center rounded-xl">
        <slot name="icon" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: String,
  value: [Number, String],
  color: { type: String, default: 'blue' },
})

const textColor = computed(() => ({
  blue:  'text-blue-600 dark:text-blue-400',
  green: 'text-emerald-600 dark:text-emerald-400',
  amber: 'text-amber-600 dark:text-amber-400',
  red:   'text-red-600 dark:text-red-400',
  gray:  'text-gray-600 dark:text-gray-400',
}[props.color] || 'text-gray-900 dark:text-white'))

const iconBg = computed(() => ({
  blue:  'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
  green: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
  amber: 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
  red:   'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
  gray:  'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
}[props.color] || 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'))
</script>