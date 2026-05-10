<template>
  <div class="mx-auto max-w-2xl space-y-6">

    <!-- Mode toggle (admin only) -->
    <div v-if="isAdmin" class="card flex overflow-hidden p-1">
      <button
        class="flex-1 rounded-lg py-2.5 text-sm font-semibold transition-colors flex items-center justify-center gap-2"
        :class="mode === 'borrow' ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
        @click="mode = 'borrow'; resetFeedback()"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        {{ i18n.t('borrow_device') }}
      </button>
      <button
        class="flex-1 rounded-lg py-2.5 text-sm font-semibold transition-colors flex items-center justify-center gap-2"
        :class="mode === 'return' ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
        @click="mode = 'return'; resetFeedback()"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7 16l-4-4m0 0l4-4m-4 4h18m-6 4v1a3 3 0 003 3h1a3 3 0 003-3V7a3 3 0 00-3-3h-1a3 3 0 00-3 3v1" />
        </svg>
        {{ i18n.t('return_device') }}
      </button>
    </div>

    <!-- Feedback banner -->
    <Transition name="fade">
      <div
        v-if="feedback.message"
        class="rounded-xl border p-4"
        :class="{
          'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 text-emerald-800 dark:text-emerald-300': feedback.type === 'success',
          'bg-red-50 dark:bg-red-900/20 border-red-200 text-red-800 dark:text-red-300':                    feedback.type === 'error',
          'bg-amber-50 dark:bg-amber-900/20 border-amber-200 text-amber-800 dark:text-amber-300':          feedback.type === 'warning',
        }"
      >
        <p class="font-semibold">{{ feedback.message }}</p>
        <div v-if="feedback.borrowed && feedback.borrowed.length" class="mt-2 space-y-1">
          <div v-for="b in feedback.borrowed" :key="b.id" class="flex items-center gap-2 text-sm">
            <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ b.device.name }} <span class="font-mono text-xs opacity-70">{{ b.device.serial_number }}</span>
          </div>
        </div>
        <div v-if="feedback.errors && feedback.errors.length" class="mt-2 space-y-1">
          <div v-for="e in feedback.errors" :key="e.identifier" class="flex items-center gap-2 text-sm text-red-700 dark:text-red-400">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            <span class="font-mono">{{ e.identifier }}</span>: {{ e.message }}
          </div>
        </div>
      </div>
    </Transition>

    <!-- ====== BORROW MODE ====== -->
    <template v-if="mode === 'borrow'">

      <!-- Step 1: Add devices to cart -->
      <div class="card p-6 space-y-4">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white">1. {{ i18n.t('borrow_device') }}</h2>

        <!-- Barcode input + Add button -->
        <div>
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('enter_barcode') }}</label>
          <div class="flex gap-2">
            <input
              v-model="identifier"
              type="text"
              class="input-field font-mono flex-1"
              :placeholder="i18n.t('barcode_placeholder')"
              :disabled="submitting"
              @keydown.enter.prevent="addToCart"
            />
            <BarcodeScanner v-if="isAdmin" @detected="val => { identifier = val; addToCart() }" />
            <button
              @click="addToCart"
              class="px-4 rounded-xl flex items-center gap-1.5 text-sm font-semibold shrink-0 transition-colors"
              :class="identifier.trim() ? 'btn-primary' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed'"
              :disabled="!identifier.trim() || submitting"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              {{ i18n.t('add_to_cart') }}
            </button>
          </div>
          <p v-if="addError" class="mt-1.5 text-xs text-red-500">{{ addError }}</p>
        </div>

        <!-- Cart list -->
        <div v-if="cart.items.length > 0" class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
          <div
            v-for="(item, idx) in cart.items"
            :key="item"
            class="flex items-center gap-3 px-4 py-2.5 border-b border-gray-100 dark:border-gray-800 last:border-0 bg-white dark:bg-gray-900"
          >
            <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V9l-6-6z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v6h6"/>
            </svg>
            <span class="font-mono text-sm flex-1 text-gray-800 dark:text-gray-200">{{ item }}</span>
            <button
              @click="cart.remove(idx)"
              class="text-gray-300 hover:text-red-500 transition-colors p-0.5 rounded"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
        <p v-else class="text-sm text-gray-400 text-center py-2">{{ i18n.t('cart_empty') }}</p>

        <p v-if="cart.items.length > 0" class="text-xs text-gray-400">{{ borrowBtnLabel }}</p>
      </div>

      <!-- Step 2: Student & date (only shown when cart has items) -->
      <Transition name="fade">
        <div v-if="cart.items.length > 0" class="card p-6 space-y-5">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">2. {{ i18n.t('student_name') }} & {{ i18n.t('due_date') }}</h2>

          <!-- Student name autocomplete -->
          <div class="relative">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('student_name') }}</label>
            <input
              v-model="studentName"
              type="text"
              class="input-field"
              :placeholder="i18n.t('select_student')"
              :disabled="submitting"
              autocomplete="off"
              @input="onStudentNameInput"
              @blur="hideDropdownDelayed"
              @focus="onStudentNameInput"
            />
            <div
              v-if="showDropdown && filteredStudents.length > 0"
              class="absolute z-50 left-0 right-0 mt-1 max-h-52 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-xl"
            >
              <button
                v-for="s in filteredStudents"
                :key="s.id"
                type="button"
                class="w-full text-left px-4 py-2.5 text-sm hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors border-b border-gray-50 dark:border-gray-800 last:border-0"
                @mousedown.prevent="selectStudent(s)"
              >
                <p class="font-semibold text-gray-900 dark:text-white">{{ s.name }}</p>
                <p class="text-xs text-gray-400">{{ s.email }}<span v-if="s.group" class="ml-2 text-gray-300">{{ s.group }}</span></p>
              </button>
            </div>
          </div>

          <!-- Student email -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('student_email') }}</label>
            <input
              v-model="studentEmail"
              type="email"
              class="input-field"
              :class="{ 'bg-gray-50 dark:bg-gray-800 opacity-75': selectedStudent }"
              placeholder="opilane@kool.ee"
              :disabled="submitting || !!selectedStudent"
            />
            <p v-if="selectedStudent" class="mt-1 text-xs text-gray-400">
              {{ i18n.isEstonian ? 'Täidetud automaatselt' : 'Auto-filled' }} ·
              <button type="button" class="underline hover:text-gray-600" @click="selectedStudent = null; studentEmail = ''">
                {{ i18n.t('edit') }}
              </button>
            </p>
          </div>

          <!-- Due date -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('due_date') }}</label>
            <input
              v-model="dueDate"
              type="date"
              class="input-field"
              :min="minDate"
              :disabled="submitting"
            />
          </div>

          <!-- Due time presets -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('return_time') }}</label>
            <div class="flex gap-2 flex-wrap">
              <button
                v-for="t in timePresets"
                :key="t"
                type="button"
                @click="dueTime = t"
                class="rounded-lg px-3 py-1.5 text-sm font-medium border transition-colors"
                :class="dueTime === t
                  ? 'bg-primary-600 border-primary-600 text-white'
                  : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-primary-400'"
              >
                {{ t }}
              </button>
              <input
                v-model="dueTime"
                type="time"
                class="input-field w-32 text-sm"
                :disabled="submitting"
              />
            </div>
          </div>

          <!-- Confirm button -->
          <button
            @click="handleBorrowBatch"
            class="w-full py-3 text-base font-semibold flex items-center justify-center gap-2 rounded-xl transition-colors btn-primary"
            :disabled="submitting || !dueDate"
          >
            <span v-if="submitting" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
            <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            {{ submitting ? '…' : borrowBtnLabel }}
          </button>
        </div>
      </Transition>
    </template>

    <!-- ====== RETURN MODE ====== -->
    <template v-else>
      <div class="card p-6 space-y-5">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ i18n.t('return_device') }}</h2>

        <div>
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('enter_barcode') }}</label>
          <div class="flex gap-2 items-stretch">
            <input
              v-model="identifier"
              type="text"
              class="input-field font-mono flex-1"
              :placeholder="i18n.t('barcode_placeholder')"
              :disabled="submitting"
              @keydown.enter.prevent="handleReturn"
            />
            <BarcodeScanner v-if="isAdmin" @detected="identifier = $event" />
          </div>
        </div>

        <button
          @click="handleReturn"
          class="w-full py-3 text-base font-semibold flex items-center justify-center gap-2 rounded-xl transition-colors btn-success"
          :disabled="submitting || !identifier"
        >
          <span v-if="submitting" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
          <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
          {{ submitting ? '…' : i18n.t('confirm_return') }}
        </button>
      </div>
    </template>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18nStore } from '@/stores/i18n'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { borrowingsApi } from '@/api/borrowings'
import { studentsApi } from '@/api/students'
import BarcodeScanner from '@/components/BarcodeScanner.vue'

const route   = useRoute()
const router  = useRouter()
const i18n    = useI18nStore()
const auth    = useAuthStore()
const cart    = useCartStore()
const isAdmin = computed(() => auth.isAdmin)

const mode         = ref('borrow')
const identifier   = ref('')
const addError     = ref('')

const studentName  = ref('')
const studentEmail = ref('')
const dueDate      = ref('')
const dueTime      = ref('08:30')
const submitting   = ref(false)
const feedback     = ref({ type: '', message: '', borrowed: null, errors: null })

const students         = ref([])
const showDropdown     = ref(false)
const filteredStudents = ref([])
const selectedStudent  = ref(null)

const timePresets = ['08:30', '10:00', '12:00', '14:00', '16:00']

const minDate = computed(() => {
  const d = new Date()
  d.setDate(d.getDate() + 1)
  return d.toISOString().split('T')[0]
})

const borrowBtnLabel = computed(() => {
  const n = cart.items.length
  if (i18n.isEstonian) return n === 1 ? 'Laenuta 1 seade' : `Laenuta ${n} seadet`
  return n === 1 ? 'Borrow 1 device' : `Borrow ${n} devices`
})

function setDefaultDueDate() {
  const d = new Date()
  d.setDate(d.getDate() + 7)
  dueDate.value = d.toISOString().split('T')[0]
}

function resetFeedback() {
  feedback.value = { type: '', message: '', borrowed: null, errors: null }
}

function addToCart() {
  addError.value = ''
  const val = identifier.value.trim()
  if (!val) return
  const added = cart.add(val)
  if (!added) {
    addError.value = i18n.t('cart_duplicate')
    return
  }
  identifier.value = ''
  resetFeedback()
  // Go back to devices so user can pick the next one
  if (route.query.serial) {
    router.push({ path: '/devices' })
  }
}

function onStudentNameInput() {
  selectedStudent.value = null
  const q = studentName.value.toLowerCase().trim()
  filteredStudents.value = q.length === 0
    ? students.value.slice(0, 8)
    : students.value.filter(s =>
        s.name.toLowerCase().includes(q) || s.email.toLowerCase().includes(q)
      ).slice(0, 8)
  showDropdown.value = true
}

function selectStudent(s) {
  selectedStudent.value = s
  studentName.value     = s.name
  studentEmail.value    = s.email
  showDropdown.value    = false
}

function hideDropdownDelayed() {
  setTimeout(() => { showDropdown.value = false }, 150)
}

function resetForm() {
  cart.clear()
  identifier.value      = ''
  studentName.value     = ''
  studentEmail.value    = ''
  selectedStudent.value = null
  setDefaultDueDate()
  dueTime.value = '08:30'
}

async function handleBorrowBatch() {
  if (!dueDate.value) {
    feedback.value = { type: 'error', message: i18n.isEstonian ? 'Palun vali tagastamise kuupäev.' : 'Please select a return date.' }
    return
  }
  if (dueDate.value < minDate.value) {
    feedback.value = { type: 'error', message: i18n.isEstonian ? 'Tagastamise kuupäev peab olema vähemalt homme.' : 'Return date must be at least tomorrow.' }
    return
  }
  submitting.value = true
  resetFeedback()
  try {
    const { data } = await borrowingsApi.borrowBatch({
      identifiers:   cart.items,
      due_date:      dueDate.value,
      due_time:      dueTime.value,
      student_name:  studentName.value || null,
      student_email: studentEmail.value || null,
    })
    const hasErrors = data.errors && data.errors.length > 0
    feedback.value = {
      type:     hasErrors && data.borrowed.length === 0 ? 'error' : (hasErrors ? 'warning' : 'success'),
      message:  data.message,
      borrowed: data.borrowed,
      errors:   data.errors,
    }
    if (data.borrowed.length > 0) resetForm()
  } catch (e) {
    feedback.value = { type: 'error', message: e.response?.data?.message || 'Midagi läks valesti.' }
  } finally {
    submitting.value = false
  }
}

async function handleReturn() {
  if (!identifier.value.trim()) return
  submitting.value = true
  resetFeedback()
  try {
    const { data } = await borrowingsApi.returnDevice({ identifier: identifier.value.trim() })
    feedback.value   = { type: 'success', message: data.message, borrowed: null, errors: null }
    identifier.value = ''
  } catch (e) {
    feedback.value = { type: 'error', message: e.response?.data?.message || 'Midagi läks valesti.' }
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  setDefaultDueDate()
  if (route.query.mode === 'return') mode.value = 'return'
  if (route.query.serial) {
    const serial = route.query.serial
    if (!cart.items.includes(serial)) {
      cart.add(serial)
    }
  }

  if (auth.isAdmin) {
    try {
      const { data } = await studentsApi.getAll()
      students.value = data
    } catch {}
  }
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
