<template>
  <div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
      <div>
        <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ i18n.t('title_borrowings') }}</h2>
      </div>
      <div class="flex gap-2 flex-wrap">
        <select v-if="isAdmin" v-model="statusFilter" class="input-field text-sm w-44">
          <option value="">Kõik</option>
          <option value="active">{{ i18n.t('active') }}</option>
          <option value="overdue">{{ i18n.t('overdue') }}</option>
          <option value="returned">{{ i18n.t('returned') }}</option>
        </select>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-2 border-primary-600 border-t-transparent" />
    </div>

    <!-- Table -->
    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('status') }}</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('name') }}</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('device') }}</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('due_date') }}</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('return_date') }}</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
            <tr v-if="filtered.length === 0">
              <td colspan="6" class="py-12 text-center text-sm text-gray-400">{{ i18n.t('no_borrowings') }}</td>
            </tr>
            <tr
              v-for="b in filtered"
              :key="b.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
            >
              <!-- Status dot -->
              <td class="px-4 py-3">
                <span
                  class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                  :class="{
                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400': b.returned_at,
                    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400':                isOverdue(b),
                    'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400':         !b.returned_at && !isOverdue(b),
                  }"
                >
                  {{ b.returned_at ? i18n.t('returned') : (isOverdue(b) ? i18n.t('overdue') : i18n.t('active')) }}
                </span>
              </td>

              <!-- Student / user -->
              <td class="px-4 py-3">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ b.student_name || b.user?.name }}</p>
                <p v-if="b.student_email || b.user?.email" class="text-xs text-gray-400">{{ b.student_email || b.user?.email }}</p>
              </td>

              <!-- Device -->
              <td class="px-4 py-3">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ b.device?.name }}</p>
                <p class="font-mono text-xs text-gray-400">{{ b.device?.serial_number }}</p>
              </td>

              <!-- Due date + time -->
              <td class="px-4 py-3">
                <div v-if="editingDueId === b.id" class="flex flex-col gap-1">
                  <input v-model="editDueDate" type="date" class="input-field text-xs py-1 px-2 w-36" />
                  <input v-model="editDueTime" type="time" class="input-field text-xs py-1 px-2 w-28" />
                  <div class="flex gap-1 mt-1">
                    <button @click="saveDueDate(b)" class="btn-primary text-xs py-1 px-2" :disabled="savingDue">
                      <span v-if="savingDue" class="h-3 w-3 animate-spin rounded-full border border-white border-t-transparent" />
                      <span v-else>✓</span>
                    </button>
                    <button @click="editingDueId = null" class="btn-secondary text-xs py-1 px-2">✕</button>
                  </div>
                </div>
                <div v-else>
                  <p class="text-sm"
                    :class="{
                      'text-red-600':    b.status_color === 'red',
                      'text-amber-600':  b.status_color === 'yellow',
                      'text-gray-700 dark:text-gray-300': b.status_color === 'green',
                    }"
                  >
                    {{ formatDate(b.due_date) }}
                  </p>
                  <p class="text-xs text-gray-400">{{ b.due_time || '08:30' }}</p>
                </div>
              </td>

              <!-- Returned at -->
              <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                {{ b.returned_at ? formatDate(b.returned_at) : '—' }}
              </td>

              <!-- Actions -->
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <!-- Bell: notify -->
                  <button
                    v-if="isAdmin && !b.returned_at && (b.student_email || b.user?.email)"
                    @click="sendNotify(b)"
                    :disabled="notifyingId === b.id"
                    class="rounded-lg p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                    :title="i18n.t('send_notification')"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                  </button>
                  <span
                    v-else-if="isAdmin && !b.returned_at && !b.student_email && !b.user?.email"
                    class="rounded-lg p-1.5 text-gray-300 dark:text-gray-600 cursor-not-allowed"
                    title="Teavitust ei saa saata — e-post puudub"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                  </span>                  <!-- Edit due date -->
                  <button
                    v-if="isAdmin && !b.returned_at"
                    @click="startEditDue(b)"
                    class="rounded-lg border border-gray-200 dark:border-gray-700 px-2 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800"
                    :title="i18n.t('change_due_date')"
                  >
                    ✏️
                  </button>

                  <!-- Mark returned -->
                  <button
                    v-if="!b.returned_at"
                    @click="markReturned(b)"
                    :disabled="returningId === b.id"
                    class="rounded-lg border border-emerald-200 dark:border-emerald-900 px-3 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors"
                  >
                    <span v-if="returningId === b.id" class="h-3 w-3 animate-spin rounded-full border border-emerald-600 border-t-transparent" />
                    <span v-else>{{ i18n.t('mark_returned') }}</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Toast -->
    <Transition name="fade">
      <div v-if="toast" class="fixed bottom-4 right-4 z-50 rounded-xl bg-gray-900 text-white px-4 py-3 text-sm shadow-lg">
        {{ toast }}
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18nStore } from '@/stores/i18n'
import { useAuthStore } from '@/stores/auth'
import { borrowingsApi } from '@/api/borrowings'

const i18n    = useI18nStore()
const auth    = useAuthStore()
const route   = useRoute()
const isAdmin = computed(() => auth.isAdmin)

const loading      = ref(true)
const borrowings   = ref([])
const statusFilter = ref('')
const returningId  = ref(null)
const notifyingId  = ref(null)
const toast        = ref('')

const editingDueId = ref(null)
const editDueDate  = ref('')
const editDueTime  = ref('')
const savingDue    = ref(false)

function showToast(msg) {
  toast.value = msg
  setTimeout(() => { toast.value = '' }, 3000)
}

function isOverdue(b) {
  if (b.returned_at) return false
  const due = new Date(b.due_date)
  if (b.due_time) {
    const [h, m] = b.due_time.split(':').map(Number)
    due.setHours(h, m, 0, 0)
  }
  return new Date() > due
}

const filtered = computed(() => {
  if (!statusFilter.value) return borrowings.value
  if (statusFilter.value === 'active')   return borrowings.value.filter(b => !b.returned_at && !isOverdue(b))
  if (statusFilter.value === 'overdue')  return borrowings.value.filter(b => isOverdue(b))
  if (statusFilter.value === 'returned') return borrowings.value.filter(b => b.returned_at)
  return borrowings.value
})

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('et-EE', { day: 'numeric', month: 'short', year: 'numeric' })
}

function startEditDue(b) {
  editingDueId.value = b.id
  editDueDate.value  = new Date(b.due_date).toISOString().split('T')[0]
  editDueTime.value  = b.due_time || '08:30'
}

async function saveDueDate(b) {
  savingDue.value = true
  try {
    const { data } = await borrowingsApi.updateDueDate(b.id, {
      due_date: editDueDate.value,
      due_time: editDueTime.value,
    })
    const idx = borrowings.value.findIndex(x => x.id === b.id)
    if (idx !== -1) {
      borrowings.value[idx] = { ...borrowings.value[idx], due_date: data.borrowing.due_date, due_time: data.borrowing.due_time }
    }
    editingDueId.value = null
    showToast('Tähtaeg uuendatud.')
  } catch (e) {
    showToast('Viga: ' + (e.response?.data?.message || 'Tähtaja uuendamine ebaõnnestus.'))
  } finally {
    savingDue.value = false
  }
}

async function markReturned(b) {
  returningId.value = b.id
  try {
    const { data } = await borrowingsApi.returnDevice({ identifier: b.device.serial_number })
    const idx = borrowings.value.findIndex(x => x.id === b.id)
    if (idx !== -1) {
      borrowings.value[idx] = {
        ...borrowings.value[idx],
        returned_at: data.borrowing?.returned_at || new Date().toISOString(),
      }
    }
    showToast('Seade märgitud tagastatuks.')
  } catch (e) {
    showToast('Viga: ' + (e.response?.data?.message || 'Tagastamine ebaõnnestus.'))
  } finally {
    returningId.value = null
  }
}

async function sendNotify(b) {
  notifyingId.value = b.id
  try {
    await borrowingsApi.notify(b.id)
    showToast(i18n.t('notification_sent'))
  } catch (e) {
    showToast('Viga: ' + (e.response?.data?.message || 'Teavituse saatmine ebaõnnestus.'))
  } finally {
    notifyingId.value = null
  }
}

onMounted(async () => {
  if (route.query.status) statusFilter.value = route.query.status
  try {
    const { data } = await borrowingsApi.getAll()
    borrowings.value = data
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(8px); }
</style>
