import { reactive, computed } from 'vue'
import apiClient from '../api/apiClient.js'

const state = reactive({
  user: null,
  loading: false,
  error: '',
})

function hasRole(role) {
  return state.user?.roles?.includes(role) ?? false
}

export function useUserStore() {
  async function fetchUser() {
    state.loading = true
    state.error = ''
    try {
      const res = await apiClient.get('/me')
      if (res.success) {
        state.user = res.data.user
      }
    } catch (err) {
      state.error = err.message || 'Failed to load user'
      state.user = null
    } finally {
      state.loading = false
    }
  }

  async function logout() {
    try {
      await apiClient.post('/logout')
    } catch {
      // ignore
    }
    state.user = null
    window.location.href = '/login'
  }

  const isFree = computed(() => !state.user || state.user.roles?.length === 0 || hasRole('free'))
  const isSilver = computed(() => hasRole('silver'))
  const isGold = computed(() => hasRole('gold'))
  const isPlatinum = computed(() => hasRole('platinum'))
  const isAdmin = computed(() => (hasRole('super-admin') || state.user?.permissions?.includes('manage-users')) ?? false)
  const canSave = computed(() => (isAdmin.value || state.user?.permissions?.includes('save-flows')) ?? false)

  const tierLabel = computed(() => {
    if (isPlatinum.value) return 'Platinum'
    if (isGold.value) return 'Gold'
    if (isSilver.value) return 'Silver'
    return 'Free'
  })

  const tierColor = computed(() => {
    if (isPlatinum.value) return '#a855f7'
    if (isGold.value) return '#eab308'
    if (isSilver.value) return '#94a3b8'
    return '#64748b'
  })

  return {
    state,
    fetchUser,
    logout,
    isFree,
    isSilver,
    isGold,
    isPlatinum,
    isAdmin,
    canSave,
    tierLabel,
    tierColor,
  }
}
