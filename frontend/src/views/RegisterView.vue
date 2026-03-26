<template>
  <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-4">
    <div class="w-full max-w-md">
      <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-600">
          <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-white">Create Account</h1>
        <p class="mt-1 text-sm text-gray-400">Join the equipment borrowing system</p>
      </div>

      <div class="card p-8">
        <form @submit.prevent="handleRegister" class="space-y-5">
          <div v-if="error" class="rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">
            {{ error }}
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Full name</label>
            <input v-model="form.name" type="text" required class="input-field" placeholder="Jane Doe" />
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Email address</label>
            <input v-model="form.email" type="email" required class="input-field" placeholder="you@school.edu" />
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Password</label>
            <input v-model="form.password" type="password" required class="input-field" placeholder="Min. 8 characters" />
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Confirm password</label>
            <input v-model="form.password_confirmation" type="password" required class="input-field" placeholder="Repeat password" />
          </div>

          <button type="submit" class="btn-primary w-full py-2.5" :disabled="loading">
            <span v-if="loading" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
            {{ loading ? 'Creating account…' : 'Create account' }}
          </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
          Already have an account?
          <RouterLink to="/login" class="font-medium text-primary-600 hover:text-primary-700">Sign in</RouterLink>
        </p>
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

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'student',
})
const loading = ref(false)
const error   = ref('')

async function handleRegister() {
  loading.value = true
  error.value   = ''
  try {
    await auth.register(form.value)
    router.push('/dashboard')
  } catch (e) {
    const errs = e.response?.data?.errors
    error.value = errs
      ? Object.values(errs).flat().join(' ')
      : (e.response?.data?.message || 'Registration failed.')
  } finally {
    loading.value = false
  }
}
</script>