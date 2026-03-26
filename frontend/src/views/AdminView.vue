<template>
  <div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-bold text-gray-900">Device Management</h2>
        <p class="text-sm text-gray-400">Add, edit, or remove equipment from inventory.</p>
      </div>
      <button class="btn-primary" @click="openModal()">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Add Device
      </button>
    </div>

    <!-- Search -->
    <div class="relative">
      <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <input v-model="search" type="search" class="input-field pl-9" placeholder="Search devices…" />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-2 border-primary-600 border-t-transparent" />
    </div>

    <!-- Table -->
    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Device</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Serial Number</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Barcode</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Borrowed by</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="filtered.length === 0">
              <td colspan="6" class="py-12 text-center text-sm text-gray-400">No devices found.</td>
            </tr>
            <tr v-for="device in filtered" :key="device.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-4 py-3">
                <p class="text-sm font-semibold text-gray-900">{{ device.name }}</p>
                <p v-if="device.description" class="text-xs text-gray-400 truncate max-w-xs">{{ device.description }}</p>
              </td>
              <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ device.serial_number }}</td>
              <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ device.barcode }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                  :class="device.status === 'available' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                >
                  {{ device.status }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div v-if="device.borrowing">
                  <p class="text-sm text-gray-700">{{ device.borrowing.user.name }}</p>
                  <p class="text-xs" :class="{
                    'text-emerald-600': device.borrowing.status_color === 'green',
                    'text-amber-600':   device.borrowing.status_color === 'yellow',
                    'text-red-600':     device.borrowing.status_color === 'red',
                  }">Due: {{ formatDate(device.borrowing.due_date) }}</p>
                </div>
                <span v-else class="text-xs text-gray-400">—</span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors"
                    @click="openModal(device)"
                  >
                    Edit
                  </button>
                  <button
                    class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    :disabled="device.status === 'borrowed'"
                    :title="device.status === 'borrowed' ? 'Cannot delete borrowed device' : ''"
                    @click="confirmDelete(device)"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add / Edit Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal" />

          <div class="relative w-full max-w-md card p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <h3 class="mb-5 text-lg font-bold text-gray-900">
              {{ editingDevice ? 'Edit Device' : 'Add New Device' }}
            </h3>

            <form @submit.prevent="handleSave" class="space-y-4">
              <div v-if="formError" class="rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">
                {{ formError }}
              </div>

              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Device Name *</label>
                <input v-model="form.name" type="text" required class="input-field" placeholder='e.g. MacBook Pro 14"' />
              </div>

              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Serial Number *</label>
                <input v-model="form.serial_number" type="text" required class="input-field font-mono" placeholder="e.g. MBP-2024-001" />
              </div>

              <!-- Barcode field with scan button -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Barcode *</label>
                <div class="flex gap-2">
                  <input v-model="form.barcode" type="text" required class="input-field font-mono flex-1" placeholder="e.g. 1001001001001" />
                  <button
                    type="button"
                    class="btn-secondary shrink-0 px-3"
                    :class="{ 'bg-primary-50 border-primary-300 text-primary-600': scannerOpen }"
                    @click="scannerOpen = !scannerOpen"
                    title="Scan barcode"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                  </button>
                </div>

                <!-- Inline scanner -->
                <div v-if="scannerOpen" class="mt-3">
                  <BarcodeScanner @detected="onBarcodeScanned" />
                </div>
              </div>

              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Description</label>
                <textarea v-model="form.description" rows="2" class="input-field resize-none" placeholder="Optional notes…" />
              </div>

              <div class="flex gap-3 pt-2">
                <button type="button" class="btn-secondary flex-1" @click="closeModal">Cancel</button>
                <button type="submit" class="btn-primary flex-1" :disabled="saving">
                  <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                  {{ saving ? 'Saving…' : (editingDevice ? 'Save Changes' : 'Add Device') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Delete confirm modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="deleteTarget = null" />
          <div class="relative w-full max-w-sm card p-6 shadow-2xl text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
              <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Delete Device?</h3>
            <p class="mt-2 text-sm text-gray-500">
              Are you sure you want to delete <strong>{{ deleteTarget.name }}</strong>? This action cannot be undone.
            </p>
            <div class="mt-5 flex gap-3">
              <button class="btn-secondary flex-1" @click="deleteTarget = null">Cancel</button>
              <button class="btn-danger flex-1" :disabled="deleting" @click="handleDelete">
                <span v-if="deleting" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                {{ deleting ? 'Deleting…' : 'Delete' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { devicesApi } from '@/api/devices'
import BarcodeScanner from '@/components/BarcodeScanner.vue'

const loading       = ref(true)
const devices       = ref([])
const search        = ref('')
const showModal     = ref(false)
const editingDevice = ref(null)
const deleteTarget  = ref(null)
const saving        = ref(false)
const deleting      = ref(false)
const formError     = ref('')
const scannerOpen   = ref(false)

const emptyForm = () => ({ name: '', serial_number: '', barcode: '', description: '' })
const form = ref(emptyForm())

const filtered = computed(() => {
  if (!search.value) return devices.value
  const q = search.value.toLowerCase()
  return devices.value.filter(d =>
    d.name.toLowerCase().includes(q) ||
    d.serial_number.toLowerCase().includes(q) ||
    d.barcode.toLowerCase().includes(q)
  )
})

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function openModal(device = null) {
  formError.value     = ''
  editingDevice.value = device
  scannerOpen.value   = false
  form.value = device
    ? { name: device.name, serial_number: device.serial_number, barcode: device.barcode, description: device.description || '' }
    : emptyForm()
  showModal.value = true
}

function closeModal() {
  showModal.value     = false
  editingDevice.value = null
  scannerOpen.value   = false
  form.value          = emptyForm()
  formError.value     = ''
}

function confirmDelete(device) {
  deleteTarget.value = device
}

function onBarcodeScanned(code) {
  form.value.barcode = code
  scannerOpen.value  = false
}

async function handleSave() {
  saving.value    = true
  formError.value = ''
  try {
    if (editingDevice.value) {
      const { data } = await devicesApi.update(editingDevice.value.id, form.value)
      const idx = devices.value.findIndex(d => d.id === data.id)
      if (idx !== -1) devices.value[idx] = { ...devices.value[idx], ...data }
    } else {
      const { data } = await devicesApi.create(form.value)
      devices.value.unshift(data)
    }
    closeModal()
  } catch (e) {
    const errs = e.response?.data?.errors
    formError.value = errs
      ? Object.values(errs).flat().join(' ')
      : (e.response?.data?.message || 'Failed to save device.')
  } finally {
    saving.value = false
  }
}

async function handleDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await devicesApi.delete(deleteTarget.value.id)
    devices.value      = devices.value.filter(d => d.id !== deleteTarget.value.id)
    deleteTarget.value = null
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to delete device.')
  } finally {
    deleting.value = false
  }
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

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>