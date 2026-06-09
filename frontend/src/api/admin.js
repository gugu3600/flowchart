import apiClient from './apiClient.js'

export function getUsers() {
  return apiClient.get('/admin/users')
}

export function getUser(id) {
  return apiClient.get(`/admin/users/${id}`)
}

export function updateUser(id, data) {
  return apiClient.put(`/admin/users/${id}`, data)
}

export function updateUserRoles(id, roles) {
  return apiClient.put(`/admin/users/${id}/roles`, { roles })
}

export function upgradeUser(id, tier) {
  return apiClient.put(`/admin/users/${id}/upgrade`, { tier })
}

export function deleteUser(id) {
  return apiClient.delete(`/admin/users/${id}`)
}

export function getTiers() {
  return apiClient.get('/admin/tiers')
}
