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
  blue:  'text-blue-600',
  green: 'text-emerald-600',
  amber: 'text-amber-600',
  red:   'text-red-600',
  gray:  'text-gray-600',
}[props.color] || 'text-gray-900'))

const iconBg = computed(() => ({
  blue:  'bg-blue-100 text-blue-600',
  green: 'bg-emerald-100 text-emerald-600',
  amber: 'bg-amber-100 text-amber-600',
  red:   'bg-red-100 text-red-600',
  gray:  'bg-gray-100 text-gray-600',
}[props.color] || 'bg-gray-100 text-gray-600'))
</script>