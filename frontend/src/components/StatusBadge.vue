<template>
  <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="classes">
    <span class="h-1.5 w-1.5 rounded-full" :class="dotClass" />
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  color: { type: String, required: true }, // green | yellow | red | returned
})

const config = {
  green:    { label: 'On time',   classes: 'bg-emerald-100 text-emerald-700', dot: 'bg-emerald-500' },
  yellow:   { label: 'Due today', classes: 'bg-amber-100 text-amber-700',     dot: 'bg-amber-500'   },
  red:      { label: 'Overdue',   classes: 'bg-red-100 text-red-700',         dot: 'bg-red-500'     },
  returned: { label: 'Returned',  classes: 'bg-gray-100 text-gray-600',       dot: 'bg-gray-400'    },
}

const label    = computed(() => config[props.color]?.label    ?? props.color)
const classes  = computed(() => config[props.color]?.classes  ?? 'bg-gray-100 text-gray-600')
const dotClass = computed(() => config[props.color]?.dot      ?? 'bg-gray-400')
</script>
