export async function adminGuard(to, from, next) {
  try {
    const { useUserStore } = await import('../stores/useUserStore.js')
    const store = useUserStore()
    if (!store.state.user) {
      await store.fetchUser()
    }
    if (!store.isAdmin.value) {
      return next('/canvas')
    }
  } catch {
    return next('/login')
  }
  next()
}
