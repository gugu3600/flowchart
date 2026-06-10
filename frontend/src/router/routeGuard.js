export function authGuard(to, isAuth) {
  if (!isAuth && to.meta.requiresAuth) {
    return '/login'
  }
}

export function adminGuard(to, isAuth, store) {
  if (!isAuth) {
    return '/login'
  }
  if (!store.isAdmin.value) {
    return '/canvas'
  }
}
