<template>
  <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-4">
    <div class="w-full max-w-md text-center">
      <div class="mb-8">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-600">
          <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-white">Registreerumine</h1>
      </div>

      <div class="card p-8">
        <div class="flex flex-col items-center gap-4">
          <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
            <svg class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">Registreerumine suletud</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs">
            Kasutajakontosid loob administraator. Pöördu oma õpetaja poole, et saada ligipääs süsteemile.
          </p>
          <RouterLink to="/login" class="btn-primary mt-2">
            Tagasi sisselogimisele
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Registration is disabled — accounts are created by admin via AdminView > Kasutajad tab.
</script>