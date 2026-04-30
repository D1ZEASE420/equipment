import api from './axios'

export const devicesApi = {
  getAll:       (params = {}) => api.get('/devices', { params }),
  getOne:       (id)          => api.get(`/devices/${id}`),
  getCategories:()            => api.get('/devices/categories'),
  getCapacities:()            => api.get('/devices/capacities'),
  create:       (data)        => api.post('/devices', data),
  update:       (id, data)    => api.put(`/devices/${id}`, data),
  remove:       (id)          => api.delete(`/devices/${id}`),

  async exportCSV() {
    const response = await api.get('/devices/export', { responseType: 'blob' })
    const url  = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href  = url
    link.setAttribute('download', 'seadmed.csv')
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  },
}

export const categoriesApi = {
  getAll:  ()           => api.get('/categories'),
  create:  (name)       => api.post('/categories', { name }),
  update:  (id, name)   => api.put(`/categories/${id}`, { name }),
  remove:  (id)         => api.delete(`/categories/${id}`),
}