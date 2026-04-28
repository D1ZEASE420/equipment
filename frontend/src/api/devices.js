import api from './axios'

export const devicesApi = {
  getAll:       (params = {}) => api.get('/devices', { params }),
  getOne:       (id)          => api.get(`/devices/${id}`),
  getCategories:()            => api.get('/devices/categories'),
  getCapacities:()            => api.get('/devices/capacities'),
  create:       (data)        => api.post('/devices', data),
  update:       (id, data)    => api.put(`/devices/${id}`, data),
  remove:       (id)          => api.delete(`/devices/${id}`),

  exportCSV() {
    // Opens CSV download directly in the browser
    const token = localStorage.getItem('auth_token')
    const base  = import.meta.env.VITE_API_URL || '/api'
    const a     = document.createElement('a')
    a.href      = `${base}/devices/export`
    // attach token via query param since we can't set headers on anchor tags
    a.href     += `?token=${encodeURIComponent(token)}`
    a.download  = 'seadmed.csv'
    a.click()
  },
}
export const categoriesApi = {
  getAll:  ()           => api.get('/categories'),
  create:  (name)       => api.post('/categories', { name }),
  update:  (id, name)   => api.put(`/categories/${id}`, { name }),
  remove:  (id)         => api.delete(`/categories/${id}`),
}
