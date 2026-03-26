<template>
  <div id="app-root">
    <!-- Show layout with nav for authenticated users -->
    <template v-if="auth.isAuthenticated">
      <AppLayout>
        <router-view />
      </AppLayout>
    </template>

    <!-- Guest pages (login / register) — no nav -->
    <template v-else>
      <router-view />
    </template>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/components/AppLayout.vue'

const auth = useAuthStore()
</script>
