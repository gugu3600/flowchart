import apiClient from './apiClient'

export function getLogics(flowId) {
  const params = flowId ? { flow_id: flowId } : {}
  return apiClient.get('/logics', { params })
}

export function getLogic(id) {
  return apiClient.get(`/logics/${id}`)
}

export function createLogic(data) {
  return apiClient.post('/logics', data)
}

export function updateLogic(id, data) {
  return apiClient.put(`/logics/${id}`, data)
}

export function deleteLogic(id) {
  return apiClient.delete(`/logics/${id}`)
}
