import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

apiClient.interceptors.response.use(
  (response) => {
    if (response.data && 'success' in response.data) {
      return response.data
    }
    return response
  },
  (error) => {
    if (error.response?.data) {
      return Promise.reject(error.response.data)
    }
    return Promise.reject({
      success: false,
      status: 0,
      message: error.message || 'Network error',
    })
  },
)

export default apiClient
