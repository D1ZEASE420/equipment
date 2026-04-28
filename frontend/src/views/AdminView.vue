<template>
  <div class="space-y-6">

    <!-- Tabs -->
    <div class="card flex overflow-hidden p-1 w-fit gap-1">
      <button
        @click="activeTab = 'devices'"
        class="rounded-lg px-4 py-2 text-sm font-semibold transition-colors"
        :class="activeTab === 'devices' ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
      >
        Seadmed
      </button>
      <button
        @click="activeTab = 'categories'"
        class="rounded-lg px-4 py-2 text-sm font-semibold transition-colors"
        :class="activeTab === 'categories' ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
      >
        Kategooriad
      </button>
      <button
        @click="activeTab = 'users'; loadUsers()"
        class="rounded-lg px-4 py-2 text-sm font-semibold transition-colors"
        :class="activeTab === 'users' ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
      >
        Kasutajad
      </button>
    </div>

    <!-- ═══════════════════════════════════════ DEVICES TAB ═══ -->
    <template v-if="activeTab === 'devices'">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ i18n.t('nav_admin') }}</h2>
          <p class="text-sm text-gray-400">{{ devices.length }} seadet kokku</p>
        </div>
        <div class="flex gap-2">
          <button class="btn-secondary" @click="exportCSV">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            {{ i18n.t('export_csv') }}
          </button>
          <button class="btn-primary" @click="openModal()">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            {{ i18n.t('add_device') }}
          </button>
        </div>
      </div>

      <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input v-model="search" type="search" class="input-field pl-9" :placeholder="i18n.t('search_devices')" />
      </div>

      <div v-if="loading" class="flex justify-center py-20">
        <div class="h-8 w-8 animate-spin rounded-full border-2 border-primary-600 border-t-transparent" />
      </div>

      <div v-else class="card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('status') }}</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('device') }}</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('category') }}</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('serial') }}</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('borrowed_by') }}</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('actions') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="filtered.length === 0">
                <td colspan="6" class="py-12 text-center text-sm text-gray-400">{{ i18n.t('no_devices') }}</td>
              </tr>
              <tr v-for="device in filtered" :key="device.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <div class="h-2 w-2 rounded-full shrink-0"
                      :class="device.status === 'available' ? 'bg-emerald-500' : 'bg-amber-500'" />
                    <span v-if="!device.loanable" class="inline-block rounded-full bg-gray-200 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-500">Ei laenuta</span>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ device.name }}</p>
                  <p v-if="device.capacity" class="text-xs text-gray-400">{{ device.capacity }}</p>
                  <p v-if="device.description" class="text-xs text-gray-400 truncate max-w-xs">{{ device.description }}</p>
                </td>
                <td class="px-4 py-3">
                  <span v-if="device.category" class="inline-block rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs">{{ device.category }}</span>
                  <span v-else class="text-xs text-gray-400">—</span>
                </td>
                <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-gray-400">{{ device.serial_number }}</td>
                <td class="px-4 py-3">
                  <div v-if="device.borrowing">
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ device.borrowing.student_name || device.borrowing.user?.name }}</p>
                    <p class="text-xs" :class="{
                      'text-emerald-600': device.borrowing.status_color === 'green',
                      'text-amber-600':   device.borrowing.status_color === 'yellow',
                      'text-red-600':     device.borrowing.status_color === 'red',
                    }">{{ i18n.t('due_date') }}: {{ formatDate(device.borrowing.due_date) }} {{ device.borrowing.due_time }}</p>
                  </div>
                  <span v-else class="text-xs text-gray-400">—</span>
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors" @click="openModal(device)">
                      {{ i18n.t('edit') }}
                    </button>
                    <button
                      class="rounded-lg border border-red-200 dark:border-red-900 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                      :disabled="device.status === 'borrowed'"
                      @click="confirmDelete(device)"
                    >
                      {{ i18n.t('delete') }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- ═══════════════════════════════════════ CATEGORIES TAB ═══ -->
    <template v-if="activeTab === 'categories'">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">Kategooriad</h2>
          <p class="text-sm text-gray-400">{{ categoryObjects.length }} kategooriat</p>
        </div>
      </div>

      <div class="card p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Lisa uus kategooria</h3>
        <div class="flex gap-3">
          <input
            v-model="newCategory"
            type="text"
            class="input-field flex-1"
            placeholder="nt. Kaamerad"
            @keyup.enter="addCategory"
          />
          <button class="btn-primary" @click="addCategory" :disabled="!newCategory.trim() || categoryAdding">
            Lisa
          </button>
        </div>
        <p v-if="categoryError" class="mt-2 text-sm text-red-600">{{ categoryError }}</p>
        <p v-if="categorySuccess" class="mt-2 text-sm text-emerald-600">{{ categorySuccess }}</p>
      </div>

      <div class="card overflow-hidden">
        <div class="divide-y divide-gray-50 dark:divide-gray-800">
          <div v-if="categoryObjects.length === 0" class="py-12 text-center text-sm text-gray-400">
            Kategooriaid ei ole. Lisa esimene kategooria.
          </div>
          <div
            v-for="cat in categoryObjects"
            :key="cat.id"
            class="flex items-center justify-between px-6 py-3 gap-3"
          >
            <template v-if="editingCategoryId === cat.id">
              <input
                v-model="editingCategoryName"
                type="text"
                class="input-field flex-1 text-sm"
                @keyup.enter="saveCategory(cat)"
                @keyup.escape="editingCategoryId = null"
                ref="editCatInput"
              />
              <div class="flex gap-2 shrink-0">
                <button class="btn-primary text-xs px-3 py-1.5" @click="saveCategory(cat)" :disabled="!editingCategoryName.trim()">Salvesta</button>
                <button class="btn-secondary text-xs px-3 py-1.5" @click="editingCategoryId = null">Tühista</button>
              </div>
            </template>
            <template v-else>
              <span class="text-sm text-gray-900 dark:text-white flex-1">{{ cat.name }}</span>
              <span class="text-xs text-gray-400 shrink-0">
                {{ devices.filter(d => d.category === cat.name).length }} seadet
              </span>
              <div class="flex gap-2 shrink-0">
                <button class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800" @click="startEditCategory(cat)">Muuda</button>
                <button class="rounded-lg border border-red-200 dark:border-red-900 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" @click="confirmDeleteCategory(cat)">Kustuta</button>
              </div>
            </template>
          </div>
        </div>
      </div>

      <!-- Delete category confirm -->
      <Teleport to="body">
        <Transition name="modal">
          <div v-if="deleteCategoryTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="deleteCategoryTarget = null" />
            <div class="relative w-full max-w-sm card p-6 shadow-2xl text-center">
              <h3 class="text-lg font-bold text-gray-900 dark:text-white">Kustuta kategooria?</h3>
              <p class="mt-2 text-sm text-gray-500"><strong>{{ deleteCategoryTarget.name }}</strong></p>
              <p class="mt-1 text-xs text-amber-600">Seadmetelt eemaldatakse see kategooria.</p>
              <div class="mt-5 flex gap-3">
                <button class="btn-secondary flex-1" @click="deleteCategoryTarget = null">Tühista</button>
                <button class="btn-danger flex-1" :disabled="deletingCategory" @click="handleDeleteCategory">
                  <span v-if="deletingCategory" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                  {{ deletingCategory ? '…' : 'Kustuta' }}
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>
    </template>

    <!-- ═══════════════════════════════════════ USERS TAB ═══ -->
    <template v-if="activeTab === 'users'">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">Kasutajad</h2>
          <p class="text-sm text-gray-400">{{ users.length }} kasutajat</p>
        </div>
        <button class="btn-primary" @click="openUserModal()">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          Lisa kasutaja
        </button>
      </div>

      <div v-if="usersLoading" class="flex justify-center py-20">
        <div class="h-8 w-8 animate-spin rounded-full border-2 border-primary-600 border-t-transparent" />
      </div>

      <div v-else class="card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('name') }}</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('email') }}</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('role') }}</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">{{ i18n.t('actions') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="users.length === 0">
                <td colspan="4" class="py-12 text-center text-sm text-gray-400">Kasutajaid ei leitud.</td>
              </tr>
              <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ u.name }}</td>
                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ u.email }}</td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                    :class="u.role === 'admin'
                      ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'
                      : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'"
                  >
                    {{ u.role === 'admin' ? i18n.t('admin') : i18n.t('student') }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors" @click="openUserModal(u)">
                      {{ i18n.t('edit') }}
                    </button>
                    <button
                      class="rounded-lg border border-red-200 dark:border-red-900 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                      :disabled="u.id === auth.user?.id"
                      @click="confirmDeleteUser(u)"
                    >
                      {{ i18n.t('delete') }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- Add / Edit Device Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal" />
          <div class="relative w-full max-w-md card p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <h3 class="mb-5 text-lg font-bold text-gray-900 dark:text-white">
              {{ editingDevice ? i18n.t('edit') + ' ' + i18n.t('device') : i18n.t('add_device') }}
            </h3>
            <form @submit.prevent="handleSave" class="space-y-4">
              <div v-if="formError" class="rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 p-3 text-sm text-red-700 dark:text-red-400">{{ formError }}</div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('name') }} *</label>
                <input v-model="form.name" type="text" required class="input-field" placeholder='nt. MacBook Pro 14"' />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('category') }}</label>
                <input v-model="form.category" type="text" class="input-field" placeholder="nt. Kaamerad" list="cat-list" />
                <datalist id="cat-list">
                  <option v-for="c in existingCategories" :key="c" :value="c" />
                </datalist>
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('serial') }} *</label>
                <input v-model="form.serial_number" type="text" required class="input-field font-mono" placeholder="nt. MBP-2024-001" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Vöökood *</label>
                <input v-model="form.barcode" type="text" required class="input-field font-mono" placeholder="nt. 1001001001001" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Kirjeldus</label>
                <textarea v-model="form.description" rows="2" class="input-field resize-none" placeholder="Valikulised märkused…" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Maht (nt. 16GB)</label>
                <input v-model="form.capacity" type="text" class="input-field" placeholder="nt. 16GB, 64GB" />
              </div>
              <div class="flex items-center gap-3">
                <input v-model="form.loanable" type="checkbox" id="loanable-check" class="h-4 w-4 rounded border-gray-300 text-primary-600" />
                <label for="loanable-check" class="text-sm font-medium text-gray-700 dark:text-gray-300">Laenutatav (tühjenda, kui seade pole laenuks)</label>
              </div>
              <div class="flex gap-3 pt-2">
                <button type="button" class="btn-secondary flex-1" @click="closeModal">{{ i18n.t('cancel') }}</button>
                <button type="submit" class="btn-primary flex-1" :disabled="saving">
                  <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                  {{ saving ? '…' : (editingDevice ? i18n.t('save') : i18n.t('add_device')) }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Delete Device Modal -->
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
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ i18n.t('delete') }}?</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400"><strong>{{ deleteTarget.name }}</strong></p>
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

    <!-- Add / Edit User Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showUserModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeUserModal" />
          <div class="relative w-full max-w-md card p-6 shadow-2xl">
            <h3 class="mb-5 text-lg font-bold text-gray-900 dark:text-white">
              {{ editingUser ? 'Muuda kasutajat' : 'Lisa kasutaja' }}
            </h3>
            <form @submit.prevent="handleUserSave" class="space-y-4">
              <div v-if="userFormError" class="rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 p-3 text-sm text-red-700 dark:text-red-400">{{ userFormError }}</div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('name') }} *</label>
                <input v-model="userForm.name" type="text" required class="input-field" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('email') }} *</label>
                <input v-model="userForm.email" type="email" required class="input-field" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('role') }}</label>
                <select v-model="userForm.role" class="input-field">
                  <option value="student">{{ i18n.t('student') }}</option>
                  <option value="admin">{{ i18n.t('admin') }}</option>
                </select>
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  {{ editingUser ? 'Uus parool (jäta tühjaks, kui ei muuda)' : 'Parool *' }}
                </label>
                <input v-model="userForm.password" type="password" :required="!editingUser" class="input-field" />
              </div>
              <div v-if="userForm.password">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ i18n.t('confirm_password') }}</label>
                <input v-model="userForm.password_confirmation" type="password" class="input-field" />
              </div>
              <div class="flex gap-3 pt-2">
                <button type="button" class="btn-secondary flex-1" @click="closeUserModal">{{ i18n.t('cancel') }}</button>
                <button type="submit" class="btn-primary flex-1" :disabled="userSaving">
                  <span v-if="userSaving" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                  {{ userSaving ? '…' : (editingUser ? i18n.t('save') : 'Lisa kasutaja') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Delete User Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="deleteUserTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="deleteUserTarget = null" />
          <div class="relative w-full max-w-sm card p-6 shadow-2xl text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
              <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Kustuta kasutaja?</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400"><strong>{{ deleteUserTarget.name }}</strong></p>
            <div class="mt-5 flex gap-3">
              <button class="btn-secondary flex-1" @click="deleteUserTarget = null">{{ i18n.t('cancel') }}</button>
              <button class="btn-danger flex-1" :disabled="deletingUser" @click="handleDeleteUser">
                <span v-if="deletingUser" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                {{ deletingUser ? '…' : i18n.t('delete') }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useI18nStore } from '@/stores/i18n'
import { useAuthStore } from '@/stores/auth'
import { devicesApi, categoriesApi } from '@/api/devices'
import api from '@/api/axios'

const i18n = useI18nStore()
const auth = useAuthStore()

const activeTab = ref('devices')

// ── Devices ──────────────────────────────────────────────
const loading         = ref(true)
const devices         = ref([])
const search          = ref('')
const showModal       = ref(false)
const editingDevice   = ref(null)
const deleteTarget    = ref(null)
const saving          = ref(false)
const deleting        = ref(false)
const formError       = ref('')

const emptyForm = () => ({ name: '', serial_number: '', barcode: '', description: '', category: '', loanable: true, capacity: '' })
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
  return new Date(date).toLocaleDateString('et-EE', { day: 'numeric', month: 'short', year: 'numeric' })
}

function openModal(device = null) {
  formError.value     = ''
  editingDevice.value = device
  form.value = device
    ? { name: device.name, serial_number: device.serial_number, barcode: device.barcode, description: device.description || '', category: device.category || '', loanable: device.loanable, capacity: device.capacity || '' }
    : emptyForm()
  showModal.value = true
}

function closeModal() {
  showModal.value     = false
  editingDevice.value = null
  form.value          = emptyForm()
  formError.value     = ''
}

function confirmDelete(device) { deleteTarget.value = device }

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
    await loadCategories()
    closeModal()
  } catch (e) {
    const errs = e.response?.data?.errors
    formError.value = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'Salvestamine ebaõnnestus.')
  } finally {
    saving.value = false
  }
}

async function handleDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await devicesApi.remove(deleteTarget.value.id)
    devices.value      = devices.value.filter(d => d.id !== deleteTarget.value.id)
    deleteTarget.value = null
  } catch (e) {
    alert(e.response?.data?.message || 'Kustutamine ebaõnnestus.')
  } finally {
    deleting.value = false
  }
}

async function exportCSV() {
  try {
    const response = await api.get('/devices/export', { responseType: 'blob' })
    const url  = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href  = url
    link.setAttribute('download', 'seadmed.csv')
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    alert('CSV eksport ebaõnnestus.')
  }
}

// ── Categories ───────────────────────────────────────────
const categoryObjects     = ref([])
const newCategory         = ref('')
const categoryError       = ref('')
const categorySuccess     = ref('')
const categoryAdding      = ref(false)
const editingCategoryId   = ref(null)
const editingCategoryName = ref('')
const deleteCategoryTarget = ref(null)
const deletingCategory    = ref(false)
const editCatInput        = ref(null)

// keep existingCategories in sync for device modal datalist
const existingCategories = computed(() => categoryObjects.value.map(c => c.name))

async function loadCategories() {
  try {
    const { data } = await api.get('/categories')
    categoryObjects.value = data
    categoryObjects.value.sort((a,b) => a.name.localeCompare(b.name))
  } catch {}
}

async function addCategory() {
  if (!newCategory.value.trim()) return
  categoryError.value   = ''
  categorySuccess.value = ''
  const cat = newCategory.value.trim()
  if (categoryObjects.value.some(c => c.name === cat)) {
    categoryError.value = 'See kategooria on juba olemas.'
    return
  }
  categoryAdding.value = true
  try {
    const { data } = await categoriesApi.create(cat)
    categoryObjects.value.push(data)
    categoryObjects.value.sort((a,b) => a.name.localeCompare(b.name))
    categorySuccess.value = `Kategooria "${cat}" lisatud.`
    newCategory.value = ''
    setTimeout(() => { categorySuccess.value = '' }, 3000)
  } catch (e) {
    categoryError.value = e.response?.data?.message || 'Lisamine ebaõnnestus.'
  } finally {
    categoryAdding.value = false
  }
}

function startEditCategory(cat) {
  editingCategoryId.value   = cat.id
  editingCategoryName.value = cat.name
  nextTick(() => editCatInput.value?.focus?.())
}

async function saveCategory(cat) {
  if (!editingCategoryName.value.trim()) return
  try {
    const { data } = await categoriesApi.update(cat.id, editingCategoryName.value.trim())
    const idx = categoryObjects.value.findIndex(c => c.id === cat.id)
    if (idx !== -1) categoryObjects.value[idx] = data
    categoryObjects.value.sort((a,b) => a.name.localeCompare(b.name))
    // update devices list too
    devices.value.forEach(d => { if (d.category === cat.name) d.category = data.name })
    editingCategoryId.value = null
  } catch (e) {
    alert(e.response?.data?.message || 'Muutmine ebaõnnestus.')
  }
}

function confirmDeleteCategory(cat) { deleteCategoryTarget.value = cat }

async function handleDeleteCategory() {
  if (!deleteCategoryTarget.value) return
  deletingCategory.value = true
  try {
    await categoriesApi.remove(deleteCategoryTarget.value.id)
    const name = deleteCategoryTarget.value.name
    categoryObjects.value = categoryObjects.value.filter(c => c.id !== deleteCategoryTarget.value.id)
    devices.value.forEach(d => { if (d.category === name) d.category = null })
    deleteCategoryTarget.value = null
  } catch (e) {
    alert(e.response?.data?.message || 'Kustutamine ebaõnnestus.')
  } finally {
    deletingCategory.value = false
  }
}

// ── Users ────────────────────────────────────────────────
const users           = ref([])
const usersLoading    = ref(false)
const showUserModal   = ref(false)
const editingUser     = ref(null)
const deleteUserTarget = ref(null)
const userSaving      = ref(false)
const deletingUser    = ref(false)
const userFormError   = ref('')

const emptyUserForm = () => ({ name: '', email: '', role: 'student', password: '', password_confirmation: '' })
const userForm = ref(emptyUserForm())

async function loadUsers() {
  usersLoading.value = true
  try {
    const { data } = await api.get('/users')
    users.value = data
  } finally {
    usersLoading.value = false
  }
}

function openUserModal(user = null) {
  userFormError.value = ''
  editingUser.value   = user
  userForm.value = user
    ? { name: user.name, email: user.email, role: user.role, password: '', password_confirmation: '' }
    : emptyUserForm()
  showUserModal.value = true
}

function closeUserModal() {
  showUserModal.value = false
  editingUser.value   = null
  userForm.value      = emptyUserForm()
  userFormError.value = ''
}

function confirmDeleteUser(user) { deleteUserTarget.value = user }

async function handleUserSave() {
  userSaving.value    = true
  userFormError.value = ''
  try {
    const payload = { ...userForm.value }
    if (!payload.password) {
      delete payload.password
      delete payload.password_confirmation
    }
    if (editingUser.value) {
      const { data } = await api.put(`/users/${editingUser.value.id}`, payload)
      const idx = users.value.findIndex(u => u.id === data.id)
      if (idx !== -1) users.value[idx] = { ...users.value[idx], ...data }
    } else {
      await api.post('/register', payload)
      await loadUsers()
    }
    closeUserModal()
  } catch (e) {
    const errs = e.response?.data?.errors
    userFormError.value = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'Salvestamine ebaõnnestus.')
  } finally {
    userSaving.value = false
  }
}

async function handleDeleteUser() {
  if (!deleteUserTarget.value) return
  deletingUser.value = true
  try {
    await api.delete(`/users/${deleteUserTarget.value.id}`)
    users.value           = users.value.filter(u => u.id !== deleteUserTarget.value.id)
    deleteUserTarget.value = null
  } catch (e) {
    alert(e.response?.data?.message || 'Kustutamine ebaõnnestus.')
  } finally {
    deletingUser.value = false
  }
}

onMounted(async () => {
  try {
    const [devRes] = await Promise.all([
      devicesApi.getAll(),
    ])
    devices.value = devRes.data
    await loadCategories()
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>