export function adminGuard(to, isAuth, store) {
  if (!isAuth) {
    return '/login'
  }
  if (!store.isAdmin.value) {
    return '/canvas'
  }
}
