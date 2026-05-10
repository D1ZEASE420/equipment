import api from './axios'

export const borrowingsApi = {
  getAll(params = {}) {
    return api.get('/borrowings', { params })
  },

  borrow(data) {
    return api.post('/borrow', data)
  },

  borrowBatch(data) {
    // data: { identifiers[], due_date, due_time, student_name, student_email }
    return api.post('/borrow-batch', data)
  },

  returnDevice(data) {
    return api.post('/return', data)
  },

  updateDueDate(id, data) {
    return api.patch(`/borrowings/${id}/due-date`, data)
  },

  notify(id) {
    return api.post(`/borrowings/${id}/notify`)
  },
}
