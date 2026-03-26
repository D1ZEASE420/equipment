<template>
  <div class="mx-auto max-w-2xl space-y-6">

    <!-- Mode toggle -->
    <div class="card flex overflow-hidden p-1">
      <button
        class="flex-1 rounded-lg py-2.5 text-sm font-semibold transition-colors flex items-center justify-center gap-2"
        :class="mode === 'borrow' ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
        @click="mode = 'borrow'; resetFeedback()"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        Borrow Device
      </button>
      <button
        class="flex-1 rounded-lg py-2.5 text-sm font-semibold transition-colors flex items-center justify-center gap-2"
        :class="mode === 'return' ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
        @click="mode = 'return'; resetFeedback()"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7 16l-4-4m0 0l4-4m-4 4h18m-6 4v1a3 3 0 003 3h1a3 3 0 003-3V7a3 3 0 00-3-3h-1a3 3 0 00-3 3v1" />
        </svg>
        Return Device
      </button>
    </div>

    <!-- Feedback banner -->
    <Transition name="fade">
      <div
        v-if="feedback.message"
        class="rounded-xl border p-4 flex items-start gap-3"
        :class="{
          'bg-emerald-50 border-emerald-200 text-emerald-800': feedback.type === 'success',
          'bg-red-50 border-red-200 text-red-800':             feedback.type === 'error',
          'bg-amber-50 border-amber-200 text-amber-800':       feedback.type === 'warning',
        }"
      >
        <!-- Success icon -->
        <svg v-if="feedback.type === 'success'" class="h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <!-- Error icon -->
        <svg v-else-if="feedback.type === 'error'" class="h-5 w-5 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <!-- Warning icon -->
        <svg v-else class="h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
          <p class="font-semibold">{{ feedback.type === 'success' ? 'Success!' : feedback.type === 'error' ? 'Error' : 'Warning' }}</p>
          <p class="text-sm mt-0.5">{{ feedback.message }}</p>
          <div v-if="feedback.device" class="mt-2 text-sm">
            <p>Device: <strong>{{ feedback.device.name }}</strong></p>
            <p class="font-mono text-xs">{{ feedback.device.serial_number }}</p>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Scanner card -->
    <div class="card p-6">
      <h2 class="mb-1 text-lg font-bold text-gray-900">
        {{ mode === 'borrow' ? 'Scan to Borrow' : 'Scan to Return' }}
      </h2>
      <p class="mb-5 text-sm text-gray-400">
        Point your camera at a barcode, or type the serial number manually.
      </p>

      <BarcodeScanner @detected="onBarcodeDetected" />

      <div class="my-5 flex items-center gap-3">
        <div class="h-px flex-1 bg-gray-200" />
        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">or enter manually</span>
        <div class="h-px flex-1 bg-gray-200" />
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-gray-700">Barcode or Serial Number</label>
          <input
            v-model="identifier"
            type="text"
            class="input-field font-mono"
            placeholder="e.g. 1001001001001 or MBP-2024-001"
            :disabled="submitting"
          />
        </div>

        <div v-if="mode === 'borrow'">
          <label class="mb-1.5 block text-sm font-medium text-gray-700">Due Date</label>
          <input
            v-model="dueDate"
            type="date"
            class="input-field"
            :min="minDate"
            :disabled="submitting"
          />
        </div>

        <button
          type="submit"
          class="w-full py-3 text-base font-semibold flex items-center justify-center gap-2"
          :class="mode === 'borrow' ? 'btn-primary' : 'btn-success'"
          :disabled="submitting || !identifier"
        >
          <span v-if="submitting" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
          <svg v-else-if="mode === 'borrow'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
          <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
          {{ submitting ? 'Processing…' : (mode === 'borrow' ? 'Confirm Borrow' : 'Confirm Return') }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { borrowingsApi } from '@/api/borrowings'
import BarcodeScanner from '@/components/BarcodeScanner.vue'

const route = useRoute()

const mode       = ref('borrow')
const identifier = ref('')
const dueDate    = ref('')
const submitting = ref(false)
const feedback   = ref({ type: '', message: '', device: null })

const minDate = computed(() => {
  const d = new Date()
  d.setDate(d.getDate() + 1)
  return d.toISOString().split('T')[0]
})

function setDefaultDueDate() {
  const d = new Date()
  d.setDate(d.getDate() + 7)
  dueDate.value = d.toISOString().split('T')[0]
}

function resetFeedback() {
  feedback.value = { type: '', message: '', device: null }
}

function onBarcodeDetected(code) {
  identifier.value = code
  handleSubmit()
}

async function handleSubmit() {
  if (!identifier.value.trim()) return

  submitting.value = true
  resetFeedback()

  try {
    if (mode.value === 'borrow') {
      if (!dueDate.value) {
        feedback.value = { type: 'error', message: 'Please select a due date.' }
        return
      }
      const { data } = await borrowingsApi.borrow({
        identifier: identifier.value.trim(),
        due_date:   dueDate.value,
      })
      feedback.value = { type: 'success', message: data.message, device: data.borrowing?.device }
      identifier.value = ''
    } else {
      const { data } = await borrowingsApi.returnDevice({ identifier: identifier.value.trim() })
      feedback.value = { type: 'success', message: data.message, device: data.borrowing?.device }
      identifier.value = ''
    }
  } catch (e) {
    const msg = e.response?.data?.message || 'Something went wrong. Please try again.'
    feedback.value = { type: 'error', message: msg }
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  setDefaultDueDate()
  if (route.query.serial) identifier.value = route.query.serial
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-8px); }
</style>