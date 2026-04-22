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
        class="rounded-xl border p-4 flex items-start gap-3"
        :class="{
          'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 text-emerald-800 dark:text-emerald-300': feedback.type === 'success',
          'bg-red-50 dark:bg-red-900/20 border-red-200 text-red-800 dark:text-red-300':                    feedback.type === 'error',
          'bg-amber-50 dark:bg-amber-900/20 border-amber-200 text-amber-800 dark:text-amber-300':          feedback.type === 'warning',
        }"
      >
        <div>
          <p class="font-semibold">{{ feedback.message }}</p>
          <div v-if="feedback.device" class="mt-2 text-sm">
            <p>{{ i18n.t('device') }}: <strong>{{ feedback.device.name }}</strong></p>
            <p class="font-mono text-xs">{{ feedback.device.serial_number }}</p>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Form card -->
    <div class="card p-6 space-y-5">
      <h2 class="text-lg font-bold text-gray-900 dark:text-white">
        {{ mode === 'borrow' ? i18n.t('borrow_device') : i18n.t('return_device') }}
      </h2>

      <!-- Device identifier -->
      <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('enter_barcode') }}</label>
        <input
          v-model="identifier"
          type="text"
          class="input-field font-mono"
          placeholder="nt. 1001001001001 või MBP-2024-001"
          :disabled="submitting"
        />
      </div>

      <!-- Borrow-only fields -->
      <template v-if="mode === 'borrow'">

        <!-- Student name with autocomplete -->
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
          <!-- Autocomplete dropdown -->
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

        <!-- Student email (auto-filled) -->
        <div>
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('student_email') }}</label>
          <input
            v-model="studentEmail"
            type="email"
            class="input-field"
            placeholder="opilane@kool.ee"
            :disabled="submitting"
          />
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

        <!-- Due time -->
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

      </template>

      <!-- Submit button -->
      <button
        @click="handleSubmit"
        class="w-full py-3 text-base font-semibold flex items-center justify-center gap-2 rounded-xl transition-colors"
        :class="mode === 'borrow' ? 'btn-primary' : 'btn-success'"
        :disabled="submitting || !identifier"
      >
        <span v-if="submitting" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
        <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        {{ submitting ? '…' : (mode === 'borrow' ? i18n.t('confirm_borrow') : i18n.t('confirm_return')) }}
      </button>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18nStore } from '@/stores/i18n'
import { useAuthStore } from '@/stores/auth'
import { borrowingsApi } from '@/api/borrowings'
import { studentsApi } from '@/api/students'

const route   = useRoute()
const i18n    = useI18nStore()
const auth    = useAuthStore()
const isAdmin = computed(() => auth.isAdmin)

const mode         = ref('borrow')
const identifier   = ref('')
const studentName  = ref('')
const studentEmail = ref('')
const dueDate      = ref('')
const dueTime      = ref('08:30')
const submitting   = ref(false)
const feedback     = ref({ type: '', message: '', device: null })

const students         = ref([])
const showDropdown     = ref(false)
const filteredStudents = ref([])

const timePresets = ['08:30', '10:00', '12:00', '14:00', '16:00']

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

function onStudentNameInput() {
  const q = studentName.value.toLowerCase().trim()
  if (q.length === 0) {
    filteredStudents.value = students.value.slice(0, 8)
  } else {
    filteredStudents.value = students.value.filter(s =>
      s.name.toLowerCase().includes(q) ||
      s.email.toLowerCase().includes(q)
    ).slice(0, 8)
  }
  showDropdown.value = true
}

function selectStudent(s) {
  studentName.value  = s.name
  studentEmail.value = s.email
  showDropdown.value = false
}

function hideDropdownDelayed() {
  setTimeout(() => { showDropdown.value = false }, 150)
}

async function handleSubmit() {
  if (!identifier.value.trim()) return
  submitting.value = true
  resetFeedback()
  try {
    if (mode.value === 'borrow') {
      if (!dueDate.value) {
        feedback.value = { type: 'error', message: 'Palun vali tagastamise kuupäev.' }
        return
      }
      const { data } = await borrowingsApi.borrow({
        identifier:    identifier.value.trim(),
        due_date:      dueDate.value,
        due_time:      dueTime.value,
        student_name:  studentName.value || null,
        student_email: studentEmail.value || null,
      })
      feedback.value   = { type: 'success', message: data.message, device: data.borrowing?.device }
      identifier.value = ''
      studentName.value  = ''
      studentEmail.value = ''
      setDefaultDueDate()
      dueTime.value = '08:30'
    } else {
      const { data } = await borrowingsApi.returnDevice({ identifier: identifier.value.trim() })
      feedback.value   = { type: 'success', message: data.message, device: data.borrowing?.device }
      identifier.value = ''
    }
  } catch (e) {
    feedback.value = { type: 'error', message: e.response?.data?.message || 'Midagi läks valesti.' }
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  setDefaultDueDate()
  if (route.query.serial) identifier.value = route.query.serial
  if (route.query.mode === 'return') mode.value = 'return'

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
