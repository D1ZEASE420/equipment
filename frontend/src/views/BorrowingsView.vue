<template>
  <div class="space-y-5">
    <!-- Filters -->
    <div class="flex flex-col gap-3 sm:flex-row">
      <input
        v-model="search"
        type="search"
        class="input-field flex-1"
        placeholder="Search by device or student name…"
      />
      <select v-model="statusFilter" class="input-field sm:w-44">
        <option value="">All statuses</option>
        <option value="green">On time</option>
        <option value="yellow">Due today</option>
        <option value="red">Overdue</option>
        <option value="returned">Returned</option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-2 border-primary-600 border-t-transparent" />
    </div>

    <!-- Table -->
    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">#</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Device</th>
              <th v-if="auth.isAdmin" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Student</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Borrowed</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Due Date</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Returned</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="filtered.length === 0">
              <td :colspan="auth.isAdmin ? 7 : 6" class="py-12 text-center text-sm text-gray-400">
                No borrowing records found.
              </td>
            </tr>
            <tr
              v-for="b in filtered"
              :key="b.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ b.id }}</td>
              <td class="px-4 py-3">
                <p class="text-sm font-medium text-gray-900">{{ b.device.name }}</p>
                <p class="text-xs text-gray-400 font-mono">{{ b.device.serial_number }}</p>
              </td>
              <td v-if="auth.isAdmin" class="px-4 py-3">
                <p class="text-sm text-gray-700">{{ b.user.name }}</p>
                <p class="text-xs text-gray-400">{{ b.user.email }}</p>
              </td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(b.borrowed_at) }}</td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(b.due_date) }}</td>
              <td class="px-4 py-3 text-sm text-gray-500">
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
import { borrowingsApi } from '@/api/borrowings'
import StatusBadge from '@/components/StatusBadge.vue'

const auth         = useAuthStore()
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
