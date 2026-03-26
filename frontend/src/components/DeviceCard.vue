<template>
  <div class="card flex flex-col gap-4 p-5 transition-shadow hover:shadow-md">
    <!-- Header row -->
    <div class="flex items-start justify-between gap-2">
      <div class="flex-1 min-w-0">
        <h3 class="truncate font-semibold text-gray-900">{{ device.name }}</h3>
        <p class="mt-0.5 font-mono text-xs text-gray-400">{{ device.serial_number }}</p>
      </div>
      <span
        class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
        :class="device.status === 'available'
          ? 'bg-emerald-100 text-emerald-700'
          : 'bg-amber-100 text-amber-700'"
      >
        {{ device.status }}
      </span>
    </div>

    <!-- Barcode badge -->
    <div class="flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2">
      <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
      </svg>
      <span class="font-mono text-xs text-gray-500">{{ device.barcode }}</span>
    </div>

    <!-- Borrower info (if borrowed) -->
    <div v-if="device.borrowing" class="space-y-1">
      <div class="flex items-center gap-2">
        <div
          class="h-2 w-2 rounded-full"
          :class="{
            'bg-emerald-500': device.borrowing.status_color === 'green',
            'bg-amber-400':   device.borrowing.status_color === 'yellow',
            'bg-red-500':     device.borrowing.status_color === 'red',
          }"
        />
        <span class="text-xs font-medium" :class="{
          'text-emerald-600': device.borrowing.status_color === 'green',
          'text-amber-600':   device.borrowing.status_color === 'yellow',
          'text-red-600':     device.borrowing.status_color === 'red',
        }">
          {{ statusLabel(device.borrowing.status_color) }}
        </span>
      </div>
      <p class="text-xs text-gray-500">
        Borrowed by <span class="font-medium text-gray-700">{{ device.borrowing.user.name }}</span>
      </p>
      <p class="text-xs text-gray-400">Due: {{ formatDate(device.borrowing.due_date) }}</p>
    </div>

    <!-- Action button -->
    <div class="mt-auto">
      <button
        v-if="device.status === 'available'"
        class="btn-primary w-full text-xs py-1.5"
        @click="$emit('borrow', device)"
      >
        Borrow this device
      </button>
      <p v-else class="text-center text-xs text-gray-400">Currently unavailable</p>
    </div>
  </div>
</template>

<script setup>
defineProps({ device: Object })
defineEmits(['borrow'])

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function statusLabel(color) {
  return { green: 'On time', yellow: 'Due today', red: 'Overdue' }[color] || color
}
</script>
