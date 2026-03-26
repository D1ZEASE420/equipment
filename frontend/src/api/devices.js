import api from './axios'

export const devicesApi = {
  getAll(params = {}) {
    return api.get('/devices', { params })
  },

  getOne(id) {
    return api.get(`/devices/${id}`)
  },

  create(data) {
    return api.post('/devices', data)
  },

  update(id, data) {
    return api.put(`/devices/${id}`, data)
  },

  delete(id) {
    return api.delete(`/devices/${id}`)
  },
}
