<template>
  <div class="space-y-6">
    <!-- Welcome banner -->
    <div class="rounded-xl bg-gradient-to-r from-primary-600 to-primary-800 p-6 text-white">
      <h2 class="text-xl font-bold flex items-center gap-2">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
        </svg>
        {{ i18n.t('welcome_back') }}, {{ auth.user?.name }}!
      </h2>
      <p class="mt-1 text-primary-100 text-sm">
        {{ auth.isAdmin ? i18n.t('admin_desc') : i18n.t('student_desc') }}
      </p>
    </div>

    <!-- Stats grid -->
<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
  <div @click="goToDevices('')" class="cursor-pointer">
    <StatCard :label="i18n.t('stat_total')" :value="stats.total" color="blue">
      <template #icon>
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
      </template>
    </StatCard>
  </div>
  <div @click="goToDevices('available')" class="cursor-pointer">
    <StatCard :label="i18n.t('stat_available')" :value="stats.available" color="green">
      <template #icon>
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </template>
    </StatCard>
  </div>
  <div @click="goToDevices('borrowed')" class="cursor-pointer">
    <StatCard :label="i18n.t('stat_borrowed')" :value="stats.borrowed" color="amber">
      <template #icon>
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
      </template>
    </StatCard>
  </div>
  <div @click="goToDevices('borrowed')" class="cursor-pointer">
    <StatCard :label="i18n.t('stat_overdue')" :value="stats.overdue" color="red">
      <template #icon>
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </template>
    </StatCard>
  </div>
</div>

    <!-- Recent activity -->
    <div class="card">
      <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4">
        <h3 class="font-semibold text-gray-900 dark:text-white">
          {{ auth.isAdmin ? i18n.t('active_borrowings') : i18n.t('my_borrowings') }}
        </h3>
        <RouterLink to="/borrowings" class="text-sm font-medium text-primary-600 hover:text-primary-700 flex items-center gap-1">
          {{ i18n.t('view_all') }}
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </RouterLink>
      </div>

      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="h-6 w-6 animate-spin rounded-full border-2 border-primary-600 border-t-transparent" />
      </div>

      <div v-else-if="activeBorrowings.length === 0" class="py-12 text-center text-sm text-gray-400">
        {{ i18n.t('no_borrowings') }}
      </div>

      <div v-else class="divide-y divide-gray-50 dark:divide-gray-800">
        <div
          v-for="b in activeBorrowings.slice(0, 6)"
          :key="b.id"
          class="flex items-center gap-4 px-6 py-4"
        >
          <div
            class="h-2.5 w-2.5 rounded-full shrink-0"
            :class="{
              'bg-emerald-500': b.status_color === 'green',
              'bg-amber-400':  b.status_color === 'yellow',
              'bg-red-500':    b.status_color === 'red',
            }"
          />
          <div class="flex-1 min-w-0">
            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ b.device.name }}</p>
            <p class="text-xs text-gray-400 font-mono">{{ b.device.serial_number }}</p>
          </div>
          <div v-if="auth.isAdmin" class="hidden text-sm text-gray-500 dark:text-gray-400 sm:block">{{ b.user.name }}</div>
          <div class="text-right">
            <p class="text-xs text-gray-400">{{ i18n.t('due_date') }}</p>
            <p
              class="text-xs font-semibold"
              :class="{
                'text-emerald-600': b.status_color === 'green',
                'text-amber-600':  b.status_color === 'yellow',
                'text-red-600':    b.status_color === 'red',
              }"
            >
              {{ formatDate(b.due_date) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <RouterLink to="/borrow" class="card group flex items-center gap-4 p-5 transition-shadow hover:shadow-md">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-100 dark:bg-primary-900/30 text-primary-600 group-hover:bg-primary-600 group-hover:text-white transition-colors">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <div>
          <p class="font-semibold text-gray-900 dark:text-white">{{ i18n.t('nav_borrow') }}</p>
          <p class="text-sm text-gray-400">{{ i18n.t('scan_barcode') }}</p>
        </div>
      </RouterLink>

      <RouterLink to="/devices" class="card group flex items-center gap-4 p-5 transition-shadow hover:shadow-md">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
        </div>
        <div>
          <p class="font-semibold text-gray-900 dark:text-white">{{ i18n.t('nav_devices') }}</p>
          <p class="text-sm text-gray-400">{{ i18n.t('search_devices') }}</p>
        </div>
      </RouterLink>

      <RouterLink v-if="auth.isAdmin" to="/admin" class="card group flex items-center gap-4 p-5 transition-shadow hover:shadow-md">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 dark:bg-purple-900/30 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <div>
          <p class="font-semibold text-gray-900 dark:text-white">{{ i18n.t('nav_admin') }}</p>
          <p class="text-sm text-gray-400">{{ i18n.t('add_device') }}</p>
        </div>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useI18nStore } from '@/stores/i18n'
import { devicesApi } from '@/api/devices'
import { borrowingsApi } from '@/api/borrowings'
import StatCard from '@/components/StatCard.vue'

const auth    = useAuthStore()
const router  = useRouter()

function goToDevices(status) {
  router.push({ path: '/devices', query: status ? { status } : {} })
}
const i18n    = useI18nStore()
const loading    = ref(true)
const devices    = ref([])
const borrowings = ref([])

const stats = computed(() => ({
  total:     devices.value.length,
  available: devices.value.filter(d => d.status === 'available').length,
  borrowed:  devices.value.filter(d => d.status === 'borrowed').length,
  overdue:   borrowings.value.filter(b => b.status_color === 'red').length,
}))

const activeBorrowings = computed(() =>
  borrowings.value.filter(b => !b.returned_at)
)

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(async () => {
  try {
    const [devRes, borRes] = await Promise.all([
      devicesApi.getAll(),
      borrowingsApi.getAll({ active: 1 }),
    ])
    devices.value    = devRes.data
    borrowings.value = borRes.data
  } finally {
    loading.value = false
  }
})
</script>