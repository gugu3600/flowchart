import apiClient from './apiClient'

export function getTables() {
  return apiClient.get('/tables')
}

export function getTable(id) {
  return apiClient.get(`/tables/${id}`)
}

export function createTable(data) {
  return apiClient.post('/tables', data)
}

export function updateTable(id, data) {
  return apiClient.put(`/tables/${id}`, data)
}

export function deleteTable(id) {
  return apiClient.delete(`/tables/${id}`)
}
