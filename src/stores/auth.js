import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authAPI } from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!user.value)

  const login = async (credentials) => {
    loading.value = true
    error.value = null
    
    try {
      // Rediriger vers la page de connexion PHP
      const loginUrl = `/public/login.php`
      const params = new URLSearchParams(credentials).toString()
      window.location.href = `${loginUrl}?${params}`
      return true
    } catch (err) {
      error.value = err.message
      return false
    } finally {
      loading.value = false
    }
  }

  const checkAuth = async () => {
    try {
      const response = await authAPI.checkAuth()
      if (response.data.success) {
        user.value = response.data.user
        return true
      }
      return false
    } catch (error) {
      return false
    }
  }

  const logout = () => {
    // Rediriger vers la déconnexion PHP
    window.location.href = '/public/logout.php'
  }

  const initAuth = async () => {
    await checkAuth()
  }

  return {
    user,
    loading,
    error,
    isAuthenticated,
    login,
    logout,
    initAuth,
    checkAuth
  }
})