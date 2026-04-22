import api from './axios'

export const borrowingsApi = {
  getAll(params = {}) {
    return api.get('/borrowings', { params })
  },

  borrow(data) {
    // data: { identifier, due_date, due_time, student_name, student_email }
    return api.post('/borrow', data)
  },

  returnDevice(data) {
    // data: { identifier }
    return api.post('/return', data)
  },

  updateDueDate(id, data) {
    // data: { due_date, due_time }
    return api.patch(`/borrowings/${id}/due-date`, data)
  },

  notify(id) {
    return api.post(`/borrowings/${id}/notify`)
  },
}
