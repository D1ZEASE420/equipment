<template>
  <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-4">
    <div class="w-full max-w-md">
      <!-- Logo -->
      <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-600">
          <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-white">Inventarisüsteem</h1>
        <p class="mt-1 text-sm text-gray-400">Logi sisse, et hallata seadmeid</p>
      </div>

      <!-- Card -->
      <div class="card p-8">
        <form @submit.prevent="handleLogin" class="space-y-5">
          <div v-if="error" class="rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-3 text-sm text-red-700 dark:text-red-400">
            {{ error }}
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">E-posti aadress</label>
            <input
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              class="input-field"
              placeholder="nimi@ametikool.ee"
              :disabled="loading"
            />
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Parool</label>
            <input
              v-model="form.password"
              type="password"
              autocomplete="current-password"
              required
              class="input-field"
              placeholder="••••••••"
              :disabled="loading"
            />
          </div>

          <button type="submit" class="btn-primary w-full py-3 text-base" :disabled="loading">
            <span v-if="loading" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent mr-2 inline-block" />
            {{ loading ? 'Sisselogimine…' : 'Logi sisse' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth   = useAuthStore()
const router = useRouter()

const form    = ref({ email: '', password: '' })
const loading = ref(false)
const error   = ref('')

async function handleLogin() {
  loading.value = true
  error.value   = ''
  try {
    await auth.login(form.value)
    router.push('/dashboard')
  } catch (e) {
    const msg  = e.response?.data?.message || 'Sisselogimine ebaõnnestus.'
    const errs = e.response?.data?.errors
    error.value = errs ? Object.values(errs).flat().join(' ') : msg
  } finally {
    loading.value = false
  }
}
</script>