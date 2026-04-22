<template>
  <div class="space-y-4">

    <!-- Filter bar -->
    <div class="card p-4 space-y-3">
      <div class="flex flex-col gap-3 sm:flex-row">
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input v-model="search" type="search" class="input-field pl-9" :placeholder="i18n.t('search_devices')" />
        </div>
        <select v-model="statusFilter" class="input-field sm:w-40">
          <option value="">{{ i18n.t('all_statuses') }}</option>
          <option value="available">{{ i18n.t('available') }}</option>
          <option value="borrowed">{{ i18n.t('borrowed') }}</option>
        </select>
      </div>

      <!-- Category multiselect -->
      <div v-if="categories.length > 0">
        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">{{ i18n.t('filter_category') }}</p>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="cat in categories"
            :key="cat"
            @click="toggleCategory(cat)"
            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold border transition-colors"
            :class="selectedCategories.includes(cat)
              ? 'bg-primary-600 border-primary-600 text-white'
              : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-primary-400'"
          >
            <svg v-if="selectedCategories.includes(cat)" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            {{ cat }}
          </button>
          <button
            v-if="selectedCategories.length > 0"
            @click="selectedCategories = []"
            class="text-xs text-gray-400 hover:text-red-500 underline"
          >
            Tühjenda
          </button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-2 border-primary-600 border-t-transparent" />
    </div>

    <!-- Empty -->
    <div v-else-if="filtered.length === 0" class="card py-16 text-center">
      <p class="text-4xl mb-3">📦</p>
      <p class="font-semibold text-gray-700 dark:text-gray-300">{{ i18n.t('no_devices') }}</p>
    </div>

    <!-- Device list -->
    <div v-else class="card overflow-hidden">
      <div class="divide-y divide-gray-100 dark:divide-gray-800">
        <div
          v-for="device in filtered"
          :key="device.id"
          class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
        >
          <!-- Status dot -->
          <div
            class="h-2.5 w-2.5 rounded-full shrink-0"
            :class="device.status === 'available' ? 'bg-emerald-500' : 'bg-amber-500'"
          />

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ device.name }}</p>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-xs font-mono text-gray-400">{{ device.serial_number }}</span>
              <span v-if="device.category" class="inline-block rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-500 dark:text-gray-400">{{ device.category }}</span>
              <span v-if="device.borrowing" class="text-xs"
                :class="{
                  'text-emerald-600': device.borrowing.status_color === 'green',
                  'text-amber-600':   device.borrowing.status_color === 'yellow',
                  'text-red-600':     device.borrowing.status_color === 'red',
                }"
              >
                {{ device.borrowing.student_name || device.borrowing.user?.name }} — {{ i18n.t('due_date') }}: {{ formatDate(device.borrowing.due_date) }} {{ device.borrowing.due_time }}
              </span>
            </div>
          </div>

          <!-- Actions (admin only) -->
          <div v-if="isAdmin" class="flex items-center gap-2 shrink-0">
            <!-- Bell notification button (only when borrowed and has student email) -->
            <button
              v-if="device.status === 'borrowed' && device.borrowing?.student_email"
              @click="sendNotification(device)"
              :disabled="notifyingId === device.id"
              class="rounded-lg p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
              :title="i18n.t('send_notification')"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </button>

            <!-- Borrow / Return button -->
            <button
              @click="handleAction(device)"
              class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors"
              :class="device.status === 'available'
                ? 'bg-primary-600 text-white hover:bg-primary-700'
                : device.borrowing?.status_color === 'red'
                  ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400'
                  : device.borrowing?.status_color === 'yellow'
                    ? 'bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-400'
                    : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400'"
            >
              {{ device.status === 'available' ? i18n.t('borrow') : i18n.t('return') }}
            </button>
          </div>

          <!-- Student view: only show status badge -->
          <div v-else>
            <span
              class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
              :class="device.status === 'available'
                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
            >
              {{ device.status === 'available' ? i18n.t('available') : i18n.t('borrowed') }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Notification toast -->
    <Transition name="fade">
      <div v-if="toast" class="fixed bottom-4 right-4 z-50 rounded-xl bg-gray-900 text-white px-4 py-3 text-sm shadow-lg">
        {{ toast }}
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18nStore } from '@/stores/i18n'
import { useAuthStore } from '@/stores/auth'
import { devicesApi } from '@/api/devices'
import { borrowingsApi } from '@/api/borrowings'

const router   = useRouter()
const i18n     = useI18nStore()
const auth     = useAuthStore()
const isAdmin  = computed(() => auth.isAdmin)

const loading            = ref(true)
const devices            = ref([])
const categories         = ref([])
const search             = ref('')
const statusFilter       = ref('')
const selectedCategories = ref([])
const notifyingId        = ref(null)
const toast              = ref('')

function showToast(msg) {
  toast.value = msg
  setTimeout(() => { toast.value = '' }, 3000)
}

function toggleCategory(cat) {
  const idx = selectedCategories.value.indexOf(cat)
  if (idx === -1) selectedCategories.value.push(cat)
  else selectedCategories.value.splice(idx, 1)
}

const filtered = computed(() => {
  let list = devices.value
  if (search.value) {
    const q = search.value.toLowerCase()
    list = list.filter(d =>
      d.name.toLowerCase().includes(q) ||
      d.serial_number.toLowerCase().includes(q) ||
      d.barcode.toLowerCase().includes(q)
    )
  }
  if (statusFilter.value) {
    list = list.filter(d => d.status === statusFilter.value)
  }
  if (selectedCategories.value.length > 0) {
    list = list.filter(d => selectedCategories.value.includes(d.category))
  }
  return list
})

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('et-EE', { day: 'numeric', month: 'short', year: 'numeric' })
}

function handleAction(device) {
  if (device.status === 'available') {
    router.push({ path: '/borrow', query: { serial: device.serial_number } })
  } else {
    router.push({ path: '/borrow', query: { serial: device.serial_number, mode: 'return' } })
  }
}

async function sendNotification(device) {
  if (!device.borrowing?.id) return
  notifyingId.value = device.id
  try {
    await borrowingsApi.notify(device.borrowing.id)
    showToast(i18n.t('notification_sent') + ': ' + device.borrowing.student_name)
  } catch (e) {
    showToast('Viga: ' + (e.response?.data?.message || 'Teavituse saatmine ebaõnnestus'))
  } finally {
    notifyingId.value = null
  }
}

onMounted(async () => {
  try {
    const [devRes, catRes] = await Promise.all([
      devicesApi.getAll(),
      devicesApi.getCategories(),
    ])
    devices.value    = devRes.data
    categories.value = catRes.data
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(8px); }
</style>
