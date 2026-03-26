import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user  = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'))
  const token = ref(localStorage.getItem('auth_token') || null)

  // Getters
  const isAuthenticated = computed(() => !!token.value)
  const isAdmin         = computed(() => user.value?.role === 'admin')
  const isStudent       = computed(() => user.value?.role === 'student')

  // Actions
  async function login(credentials) {
    const { data } = await authApi.login(credentials)
    setAuth(data.user, data.token)
    return data
  }

  async function register(payload) {
    const { data } = await authApi.register(payload)
    setAuth(data.user, data.token)
    return data
  }

  async function logout() {
    try {
      await authApi.logout()
    } catch (_) {
      // Ignore errors on logout — still clear local state
    } finally {
      clearAuth()
    }
  }

  async function fetchMe() {
    const { data } = await authApi.me()
    user.value = data
    localStorage.setItem('auth_user', JSON.stringify(data))
    return data
  }

  function setAuth(userData, tokenValue) {
    user.value  = userData
    token.value = tokenValue
    localStorage.setItem('auth_user',  JSON.stringify(userData))
    localStorage.setItem('auth_token', tokenValue)
  }

  function clearAuth() {
    user.value  = null
    token.value = null
    localStorage.removeItem('auth_user')
    localStorage.removeItem('auth_token')
  }

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    isStudent,
    login,
    register,
    logout,
    fetchMe,
  }
})
