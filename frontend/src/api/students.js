import api from './axios'

export const studentsApi = {
  getAll(params = {}) {
    return api.get('/students', { params })
  },

  create(data) {
    return api.post('/students', data)
  },

  update(id, data) {
    return api.put(`/students/${id}`, data)
  },

  delete(id) {
    return api.delete(`/students/${id}`)
  },

  import(students) {
    return api.post('/students/import', { students })
  },

  deleteGroup(group) {
    return api.delete(`/students/group/${encodeURIComponent(group)}`)
  },

  getBorrowingHistory(id) {
    return api.get(`/students/${id}/borrowings`)
  },
}
