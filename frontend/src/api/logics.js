import apiClient from './apiClient'

export function getLogics() {
  return apiClient.get('/logics')
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
