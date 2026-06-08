import apiClient from './apiClient'

export function getFlows() {
  return apiClient.get('/flows')
}

export function getFlow(id) {
  return apiClient.get(`/flows/${id}`)
}

export function createFlow(data) {
  return apiClient.post('/flows', data)
}

export function updateFlow(id, data) {
  return apiClient.put(`/flows/${id}`, data)
}

export function deleteFlow(id) {
  return apiClient.delete(`/flows/${id}`)
}

export function saveFlow(flowId, payload) {
  return apiClient.post(`/flows/${flowId}/save`, payload)
}
