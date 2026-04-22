<template>
  <div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
      <div>
        <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ i18n.t('title_students') }}</h2>
        <p class="text-sm text-gray-400">{{ students.length }} õpilast</p>
      </div>
      <div class="flex gap-2">
        <button class="btn-secondary text-sm" @click="showImport = true">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
          </svg>
          {{ i18n.t('import_students') }}
        </button>
        <button class="btn-primary text-sm" @click="openModal()">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          {{ i18n.t('add_student') }}
        </button>
      </div>
    </div>

    <!-- Search & group filter -->
    <div class="flex flex-col gap-3 sm:flex-row">
      <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input v-model="search" type="search" class="input-field pl-9" placeholder="Otsi nime või e-posti…" />
      </div>
      <select v-model="groupFilter" class="input-field sm:w-48">
        <option value="">Kõik rühmad</option>
        <option v-for="g in groups" :key="g" :value="g">{{ g }}</option>
      </select>
      <button
        v-if="groupFilter"
        class="btn-danger text-sm shrink-0"
        @click="confirmDeleteGroup(groupFilter)"
      >
        {{ i18n.t('delete_group') }}: {{ groupFilter }}
      </button>
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
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('name') }}</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('email') }}</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('group') }}</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
            <tr v-if="filtered.length === 0">
              <td colspan="4" class="py-12 text-center text-sm text-gray-400">{{ i18n.t('no_students') }}</td>
            </tr>
            <tr
              v-for="s in filtered"
              :key="s.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
            >
              <td class="px-4 py-3">
                <button
                  class="text-sm font-semibold text-primary-600 hover:underline text-left"
                  @click="viewHistory(s)"
                >
                  {{ s.name }}
                </button>
              </td>
              <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ s.email }}</td>
              <td class="px-4 py-3">
                <span v-if="s.group" class="inline-block rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs">{{ s.group }}</span>
                <span v-else class="text-xs text-gray-400">—</span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800" @click="openModal(s)">
                    {{ i18n.t('edit') }}
                  </button>
                  <button class="rounded-lg border border-red-200 dark:border-red-900 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" @click="confirmDelete(s)">
                    {{ i18n.t('delete') }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Student Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal" />
          <div class="relative w-full max-w-md card p-6 shadow-2xl">
            <h3 class="mb-5 text-lg font-bold text-gray-900 dark:text-white">
              {{ editing ? i18n.t('edit') : i18n.t('add_student') }}
            </h3>
            <div v-if="formError" class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 p-3 text-sm text-red-700 dark:text-red-400">{{ formError }}</div>
            <div class="space-y-4">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('name') }} *</label>
                <input v-model="form.name" type="text" required class="input-field" placeholder="Kristofer Tamm" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('email') }} *</label>
                <input v-model="form.email" type="email" required class="input-field" placeholder="kristofer@kool.ee" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('group') }}</label>
                <input v-model="form.group" type="text" class="input-field" placeholder="nt. IT22" list="group-list" />
                <datalist id="group-list">
                  <option v-for="g in groups" :key="g" :value="g" />
                </datalist>
              </div>
            </div>
            <div class="mt-5 flex gap-3">
              <button class="btn-secondary flex-1" @click="closeModal">{{ i18n.t('cancel') }}</button>
              <button class="btn-primary flex-1" :disabled="saving" @click="handleSave">
                <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                {{ saving ? '…' : i18n.t('save') }}
              </button>
            </div>
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
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
              <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </div>
            <div v-if="deleteActiveBorrowings" class="mb-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 p-3 text-sm text-amber-700 dark:text-amber-400">
              ⚠️ {{ i18n.t('active_borrowings_warning') }}
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ i18n.t('delete') }}?</h3>
            <p class="mt-2 text-sm text-gray-500"><strong>{{ deleteTarget.name }}</strong></p>
            <div class="mt-5 flex gap-3">
              <button class="btn-secondary flex-1" @click="deleteTarget = null">{{ i18n.t('cancel') }}</button>
              <button class="btn-danger flex-1" :disabled="deleting" @click="handleDelete">
                <span v-if="deleting" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                {{ deleting ? '…' : i18n.t('delete') }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Delete group confirm -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="deleteGroupTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="deleteGroupTarget = null" />
          <div class="relative w-full max-w-sm card p-6 shadow-2xl text-center">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ i18n.t('delete_group') }}?</h3>
            <p class="mt-2 text-sm text-gray-500">{{ i18n.t('confirm_delete_group') }} <strong>{{ deleteGroupTarget }}</strong>?</p>
            <div class="mt-5 flex gap-3">
              <button class="btn-secondary flex-1" @click="deleteGroupTarget = null">{{ i18n.t('cancel') }}</button>
              <button class="btn-danger flex-1" :disabled="deleting" @click="handleDeleteGroup">
                {{ deleting ? '…' : i18n.t('delete') }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Import modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showImport" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showImport = false" />
          <div class="relative w-full max-w-lg card p-6 shadow-2xl">
            <h3 class="mb-3 text-lg font-bold text-gray-900 dark:text-white">{{ i18n.t('import_students') }}</h3>
            <p class="mb-3 text-xs text-gray-500">{{ i18n.t('import_help') }}</p>
            <p class="mb-2 text-xs font-mono text-gray-400">Nimi, E-post, Rühm</p>
            <textarea
              v-model="importText"
              rows="8"
              class="input-field font-mono text-xs resize-none w-full"
              placeholder="Kristofer Tamm, kristofer@kool.ee, IT22&#10;Mari Mets, mari@kool.ee, IT22&#10;Jaan Pärn, jaan@kool.ee, IT23"
            />
            <div v-if="importResult" class="mt-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 p-3 text-sm text-emerald-700 dark:text-emerald-400">
              ✓ {{ i18n.t('import_success') }}: {{ importResult.created }}, {{ i18n.t('import_skipped') }}: {{ importResult.skipped }}
            </div>
            <div v-if="importError" class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ importError }}</div>
            <div class="mt-5 flex gap-3">
              <button class="btn-secondary flex-1" @click="showImport = false; importResult = null; importText = ''">{{ i18n.t('close') }}</button>
              <button class="btn-primary flex-1" :disabled="importing || !importText.trim()" @click="handleImport">
                <span v-if="importing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                {{ importing ? '…' : i18n.t('import_students') }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Borrowing history modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="historyStudent" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="historyStudent = null" />
          <div class="relative w-full max-w-2xl card p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
              <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ historyStudent.name }}</h3>
                <p class="text-sm text-gray-400">{{ historyStudent.email }}</p>
              </div>
              <button @click="historyStudent = null" class="text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div v-if="historyLoading" class="flex justify-center py-10">
              <div class="h-6 w-6 animate-spin rounded-full border-2 border-primary-600 border-t-transparent" />
            </div>
            <template v-else-if="historyData">
              <!-- Stats -->
              <div class="grid grid-cols-3 gap-3 mb-5">
                <div class="rounded-xl bg-gray-50 dark:bg-gray-800 p-3 text-center">
                  <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ historyData.stats.total }}</p>
                  <p class="text-xs text-gray-500">{{ i18n.t('total_borrowings') }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 dark:bg-gray-800 p-3 text-center">
                  <p class="text-2xl font-bold" :class="historyData.stats.returned_late > 0 ? 'text-red-600' : 'text-gray-900 dark:text-white'">{{ historyData.stats.returned_late }}</p>
                  <p class="text-xs text-gray-500">{{ i18n.t('returned_late') }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 dark:bg-gray-800 p-3 text-center">
                  <p class="text-2xl font-bold" :class="historyData.stats.currently_out > 0 ? 'text-amber-600' : 'text-gray-900 dark:text-white'">{{ historyData.stats.currently_out }}</p>
                  <p class="text-xs text-gray-500">{{ i18n.t('currently_out') }}</p>
                </div>
              </div>

              <!-- History list -->
              <div v-if="historyData.borrowings.length === 0" class="py-8 text-center text-sm text-gray-400">
                Laenutuste ajalugu puudub.
              </div>
              <div v-else class="space-y-2">
                <div
                  v-for="b in historyData.borrowings"
                  :key="b.id"
                  class="flex items-center gap-3 rounded-xl border border-gray-100 dark:border-gray-800 p-3"
                >
                  <div
                    class="h-2.5 w-2.5 rounded-full shrink-0"
                    :class="{
                      'bg-emerald-500': b.returned_at && !b.was_late,
                      'bg-red-500':     b.was_late || b.overdue,
                      'bg-amber-500':   !b.returned_at && !b.overdue,
                    }"
                  />
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ b.device?.name || '—' }}</p>
                    <p class="text-xs text-gray-400">
                      {{ formatDate(b.borrowed_at) }} → tähtaeg {{ formatDate(b.due_date) }} {{ b.due_time }}
                      <span v-if="b.returned_at"> · tagastatud {{ formatDate(b.returned_at) }}</span>
                    </p>
                  </div>
                  <span class="shrink-0 text-xs font-semibold rounded-full px-2 py-0.5"
                    :class="{
                      'bg-emerald-100 text-emerald-700': b.returned_at && !b.was_late,
                      'bg-red-100 text-red-700':         b.was_late || b.overdue,
                      'bg-amber-100 text-amber-700':     !b.returned_at && !b.overdue,
                    }"
                  >
                    {{ b.returned_at ? (b.was_late ? 'Hilines' : 'Tagastatud') : (b.overdue ? 'Tähtaeg üle' : 'Väljas') }}
                  </span>
                </div>
              </div>
            </template>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18nStore } from '@/stores/i18n'
import { studentsApi } from '@/api/students'

const i18n = useI18nStore()

const loading         = ref(true)
const students        = ref([])
const search          = ref('')
const groupFilter     = ref('')
const showModal       = ref(false)
const editing         = ref(null)
const deleteTarget    = ref(null)
const deleteGroupTarget = ref(null)
const deleteActiveBorrowings = ref(false)
const saving          = ref(false)
const deleting        = ref(false)
const formError       = ref('')
const showImport      = ref(false)
const importText      = ref('')
const importResult    = ref(null)
const importError     = ref('')
const importing       = ref(false)

const historyStudent  = ref(null)
const historyLoading  = ref(false)
const historyData     = ref(null)

const form = ref({ name: '', email: '', group: '' })

const groups = computed(() => {
  const g = new Set(students.value.map(s => s.group).filter(Boolean))
  return [...g].sort()
})

const filtered = computed(() => {
  let list = students.value
  if (search.value) {
    const q = search.value.toLowerCase()
    list = list.filter(s =>
      s.name.toLowerCase().includes(q) ||
      s.email.toLowerCase().includes(q)
    )
  }
  if (groupFilter.value) {
    list = list.filter(s => s.group === groupFilter.value)
  }
  return list
})

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('et-EE', { day: 'numeric', month: 'short', year: 'numeric' })
}

function openModal(s = null) {
  editing.value  = s
  formError.value = ''
  form.value = s ? { name: s.name, email: s.email, group: s.group || '' } : { name: '', email: '', group: '' }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editing.value   = null
  formError.value = ''
}

async function handleSave() {
  saving.value    = true
  formError.value = ''
  try {
    if (editing.value) {
      const { data } = await studentsApi.update(editing.value.id, form.value)
      const idx = students.value.findIndex(s => s.id === data.id)
      if (idx !== -1) students.value[idx] = data
    } else {
      const { data } = await studentsApi.create(form.value)
      students.value.unshift(data)
    }
    closeModal()
  } catch (e) {
    const errs = e.response?.data?.errors
    formError.value = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'Viga salvestamisel.')
  } finally {
    saving.value = false
  }
}

async function confirmDelete(s) {
  deleteTarget.value = s
  deleteActiveBorrowings.value = false
  // check active borrowings via history
  try {
    const { data } = await studentsApi.getBorrowingHistory(s.id)
    deleteActiveBorrowings.value = data.stats.currently_out > 0
  } catch {}
}

function confirmDeleteGroup(g) {
  deleteGroupTarget.value = g
}

async function handleDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await studentsApi.delete(deleteTarget.value.id)
    students.value   = students.value.filter(s => s.id !== deleteTarget.value.id)
    deleteTarget.value = null
  } catch (e) {
    alert(e.response?.data?.message || 'Kustutamine ebaõnnestus.')
  } finally {
    deleting.value = false
  }
}

async function handleDeleteGroup() {
  if (!deleteGroupTarget.value) return
  deleting.value = true
  try {
    await studentsApi.deleteGroup(deleteGroupTarget.value)
    students.value = students.value.filter(s => s.group !== deleteGroupTarget.value)
    if (groupFilter.value === deleteGroupTarget.value) groupFilter.value = ''
    deleteGroupTarget.value = null
  } catch (e) {
    alert(e.response?.data?.message || 'Kustutamine ebaõnnestus.')
  } finally {
    deleting.value = false
  }
}

async function handleImport() {
  importing.value  = true
  importError.value = ''
  importResult.value = null
  try {
    const rows = importText.value.trim().split('\n').map(line => {
      const parts = line.split(',').map(p => p.trim())
      return { name: parts[0], email: parts[1], group: parts[2] || null }
    }).filter(r => r.name && r.email)

    const { data } = await studentsApi.import(rows)
    importResult.value = data

    // reload list
    const res = await studentsApi.getAll()
    students.value = res.data
  } catch (e) {
    importError.value = e.response?.data?.message || 'Import ebaõnnestus.'
  } finally {
    importing.value = false
  }
}

async function viewHistory(s) {
  historyStudent.value = s
  historyLoading.value = true
  historyData.value    = null
  try {
    const { data } = await studentsApi.getBorrowingHistory(s.id)
    historyData.value = data
  } catch {
    historyData.value = { borrowings: [], stats: { total: 0, returned_late: 0, currently_out: 0 } }
  } finally {
    historyLoading.value = false
  }
}

onMounted(async () => {
  try {
    const { data } = await studentsApi.getAll()
    students.value = data
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
