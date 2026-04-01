<template>
  <div class="space-y-5">
    <!-- Filters -->
    <div class="flex flex-col gap-3 sm:flex-row">
      <input
        v-model="search"
        type="search"
        class="input-field flex-1"
        :placeholder="i18n.t('search_devices')"
      />
      <select v-model="statusFilter" class="input-field sm:w-44">
        <option value="">{{ i18n.t('all_statuses') }}</option>
        <option value="green">{{ i18n.t('status_active') }}</option>
        <option value="yellow">{{ i18n.t('due_date') }}</option>
        <option value="red">{{ i18n.t('overdue') }}</option>
        <option value="returned">{{ i18n.t('returned') }}</option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-2 border-primary-600 border-t-transparent" />
    </div>

    <!-- Table -->
    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">#</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ i18n.t('device') }}</th>
              <th v-if="auth.isAdmin" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ i18n.t('student_desc') }}</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ i18n.t('borrowed') }}</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ i18n.t('due_date') }}</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ i18n.t('return_date') }}</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ i18n.t('status') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
            <tr v-if="filtered.length === 0">
              <td :colspan="auth.isAdmin ? 7 : 6" class="py-12 text-center text-sm text-gray-400">
                {{ i18n.t('no_borrowings') }}
              </td>
            </tr>
            <tr
              v-for="b in filtered"
              :key="b.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
            >
              <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ b.id }}</td>
              <td class="px-4 py-3">
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ b.device.name }}</p>
                <p class="text-xs text-gray-400 font-mono">{{ b.device.serial_number }}</p>
              </td>
              <td v-if="auth.isAdmin" class="px-4 py-3">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ b.user.name }}</p>
                <p class="text-xs text-gray-400">{{ b.user.email }}</p>
              </td>
              <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ formatDate(b.borrowed_at) }}</td>
              <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ formatDate(b.due_date) }}</td>
              <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                {{ b.returned_at ? formatDate(b.returned_at) : '—' }}
              </td>
              <td class="px-4 py-3">
                <StatusBadge :color="b.status_color" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useI18nStore } from '@/stores/i18n'
import { borrowingsApi } from '@/api/borrowings'
import StatusBadge from '@/components/StatusBadge.vue'

const auth         = useAuthStore()
const i18n         = useI18nStore()
const loading      = ref(true)
const borrowings   = ref([])
const search       = ref('')
const statusFilter = ref('')

const filtered = computed(() => {
  let list = borrowings.value
  if (search.value) {
    const q = search.value.toLowerCase()
    list = list.filter(b =>
      b.device.name.toLowerCase().includes(q) ||
      b.device.serial_number.toLowerCase().includes(q) ||
      b.user.name.toLowerCase().includes(q)
    )
  }
  if (statusFilter.value) {
    list = list.filter(b => b.status_color === statusFilter.value)
  }
  return list
})

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-GB', {
    day: 'numeric', month: 'short', year: 'numeric'
  })
}

onMounted(async () => {
  try {
    const { data } = await borrowingsApi.getAll()
    borrowings.value = data
  } finally {
    loading.value = false
  }
})
</script>