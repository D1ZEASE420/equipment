import api from './axios'

export const borrowingsApi = {
  getAll(params = {}) {
    return api.get('/borrowings', { params })
  },

  borrow(data) {
    // data: { identifier, due_date }
    return api.post('/borrow', data)
  },

  returnDevice(data) {
    // data: { identifier }
    return api.post('/return', data)
  },
}
