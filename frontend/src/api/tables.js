import apiClient from './apiClient'

export function getTables(flowId) {
  const params = flowId ? { flow_id: flowId } : {}
  return apiClient.get('/tables', { params })
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
