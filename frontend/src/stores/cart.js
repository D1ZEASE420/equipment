import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useCartStore = defineStore('cart', () => {
  const items = ref([])

  function add(identifier) {
    const val = identifier.trim()
    if (!val || items.value.includes(val)) return false
    items.value.push(val)
    return true
  }

  function remove(idx) {
    items.value.splice(idx, 1)
  }

  function clear() {
    items.value = []
  }

  return { items, add, remove, clear }
})
