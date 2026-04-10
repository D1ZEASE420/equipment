<template>
  <div class="space-y-6">
    <!-- Search & filter bar -->
    <div class="flex flex-col gap-3 sm:flex-row">
      <input
        v-model="search"
        type="search"
        class="input-field flex-1"
        :placeholder="i18n.t('search_devices')"
        @input="filterDevices"
      />
      <select v-model="statusFilter" class="input-field sm:w-40" @change="filterDevices">
        <option value="">{{ i18n.t('all_statuses') }}</option>
        <option value="available">{{ i18n.t('available') }}</option>
        <option value="borrowed">{{ i18n.t('borrowed') }}</option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-2 border-primary-600 border-t-transparent" />
    </div>

    <!-- Empty state -->
    <div v-else-if="filtered.length === 0" class="card py-16 text-center">
      <p class="text-4xl mb-3">📦</p>
      <p class="font-semibold text-gray-700 dark:text-gray-300">{{ i18n.t('no_devices') }}</p>
    </div>

    <!-- Devices grid -->
    <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <DeviceCard
        v-for="device in filtered"
        :key="device.id"
        :device="device"
        @borrow="goToBorrow"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18nStore } from '@/stores/i18n'
import { devicesApi } from '@/api/devices'
import DeviceCard from '@/components/DeviceCard.vue'

const router       = useRouter()
const i18n         = useI18nStore()
const loading      = ref(true)
const devices      = ref([])
const search       = ref('')
const statusFilter = ref('')

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
  return list
})

function filterDevices() { /* reactivity handled by computed */ }

function goToBorrow(device) {
  router.push({ path: '/borrow', query: { serial: device.serial_number } })
}

onMounted(async () => {
  try {
    const { data } = await devicesApi.getAll()
    devices.value = data
  } finally {
    loading.value = false
  }
})
</script>