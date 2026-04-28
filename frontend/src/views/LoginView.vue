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
        <h1 class="text-2xl font-bold text-white">Equipment System</h1>
        <p class="mt-1 text-sm text-gray-400">Sign in to your account</p>
      </div>

      <!-- Card -->
      <div class="card p-8">
        <form @submit.prevent="handleLogin" class="space-y-5">
          <!-- Error alert -->
          <div v-if="error" class="rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">
            {{ error }}
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Email address</label>
            <input
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              class="input-field"
              placeholder="you@school.edu"
            />
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Password</label>
            <input
              v-model="form.password"
              type="password"
              autocomplete="current-password"
              required
              class="input-field"
              placeholder="••••••••"
            />
          </div>

          <button type="submit" class="btn-primary w-full py-2.5" :disabled="loading">
            <span v-if="loading" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
            {{ loading ? 'Signing in…' : 'Sign in' }}
          </button>
        </form>

        <!-- Demo credentials -->
        <div class="mt-6 rounded-lg bg-gray-50 p-4">
          <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Demo credentials</p>
          <div class="space-y-1 text-xs text-gray-600 font-mono">
            <p><span class="font-medium text-purple-600">Admin:</span> admin@school.edu / password</p>
            <p><span class="font-medium text-blue-600">Student:</span> student@school.edu / password</p>
          </div>
        </div>


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

const form = ref({ email: '', password: '' })
const loading = ref(false)
const error   = ref('')

async function handleLogin() {
  loading.value = true
  error.value   = ''
  try {
    await auth.login(form.value)
    router.push('/dashboard')
  } catch (e) {
    const msg = e.response?.data?.message || 'Login failed. Please check your credentials.'
    const errs = e.response?.data?.errors
    error.value = errs ? Object.values(errs).flat().join(' ') : msg
  } finally {
    loading.value = false
  }
}
</script>
