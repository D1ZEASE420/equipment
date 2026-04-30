<template>
  <div class="relative inline-block">
    <!-- Scan icon button -->
    <button
      type="button"
      class="scan-btn"
      :class="{ active: open }"
      @click="toggle"
      title="Skanni vöökood"
    >
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M3 5h2M3 8h2M3 11h2M7 3v2M10 3v2M13 3v2
             M7 19v2M10 19v2M13 19v2
             M17 5h2M17 8h2M17 11h2
             M3 14h7v7H3zM14 3h7v7h-7z
             M3 3h7v7H3z" />
      </svg>
    </button>

    <!-- Camera popup -->
    <Transition name="scanner-pop">
      <div v-if="open" class="scanner-popup">
        <!-- Viewport -->
        <div class="relative overflow-hidden rounded-lg bg-black" style="height:260px;">
          <div id="quagga-viewport" class="w-full h-full" />

          <!-- Overlay -->
          <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" />
            <div class="relative z-10 h-32 w-60">
              <div class="absolute top-0 left-0 h-6 w-6 border-t-4 border-l-4 border-primary-400 rounded-tl" />
              <div class="absolute top-0 right-0 h-6 w-6 border-t-4 border-r-4 border-primary-400 rounded-tr" />
              <div class="absolute bottom-0 left-0 h-6 w-6 border-b-4 border-l-4 border-primary-400 rounded-bl" />
              <div class="absolute bottom-0 right-0 h-6 w-6 border-b-4 border-r-4 border-primary-400 rounded-br" />
              <div class="scan-line absolute inset-x-0 h-0.5 bg-primary-400" />
            </div>
          </div>

          <!-- Status -->
          <div class="absolute bottom-2 inset-x-0 flex justify-center z-20">
            <span class="rounded-full bg-black/70 px-3 py-1 text-xs text-white backdrop-blur-sm flex items-center gap-1.5">
              <span class="h-1.5 w-1.5 rounded-full bg-primary-400 animate-pulse" />
              {{ status }}
            </span>
          </div>

          <!-- Close -->
          <button
            type="button"
            class="absolute top-2 right-2 z-20 rounded-full bg-black/60 p-1.5 text-white hover:bg-black/80"
            @click="close"
          >
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Error -->
        <div v-if="error" class="mt-2 rounded-lg bg-red-50 border border-red-200 px-3 py-2 text-xs text-red-700">
          {{ error }}
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onBeforeUnmount } from 'vue'
import Quagga from '@ericblade/quagga2'

const emit = defineEmits(['detected'])

const open   = ref(false)
const status = ref('Suuna vöökood raami…')
const error  = ref('')

let lastCode = ''
let lastTime = 0

function toggle() {
  open.value ? close() : start()
}

function start() {
  error.value = ''
  open.value  = true
  status.value = 'Kaamerat laadib…'

  setTimeout(() => {
    Quagga.init(
      {
        inputStream: {
          name: 'Live',
          type: 'LiveStream',
          target: document.getElementById('quagga-viewport'),
          constraints: {
            width:       { min: 1280 },
            height:      { min: 720 },
            facingMode:  'environment',
            aspectRatio: { min: 1, max: 2 },
          },
        },
        locator:      { patchSize: 'large', halfSample: false },
        numOfWorkers: navigator.hardwareConcurrency || 4,
        frequency:    15,
        decoder: {
          readers: [
            'ean_reader', 'ean_8_reader',
            'code_128_reader', 'code_39_reader',
            'upc_reader', 'upc_e_reader',
          ],
        },
        locate: true,
      },
      (err) => {
        if (err) {
          open.value   = false
          error.value  = `Kaamera viga: ${err.message || err}`
          return
        }
        Quagga.start()
        status.value = 'Skannimine…'
      }
    )
    Quagga.onDetected(onDetected)
  }, 100)
}

function onDetected(result) {
  const code = result?.codeResult?.code
  if (!code) return
  const now = Date.now()
  if (code === lastCode && now - lastTime < 2000) return
  lastCode = code
  lastTime = now
  status.value = `Tuvastatud: ${code}`
  close()
  emit('detected', code)
}

function close() {
  Quagga.offDetected(onDetected)
  try { Quagga.stop() } catch (_) {}
  open.value   = false
  status.value = 'Suuna vöökood raami…'
}

onBeforeUnmount(() => { if (open.value) close() })
</script>

<style scoped>
.scan-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.4rem;
  border-radius: 0.5rem;
  border: 1px solid #d1d5db;
  background: white;
  color: #6b7280;
  cursor: pointer;
  transition: background 0.15s, color 0.15s, border-color 0.15s;
  height: 100%;
}
.scan-btn:hover  { background: #f3f4f6; color: #111827; }
.scan-btn.active { background: #eff6ff; color: #2563eb; border-color: #93c5fd; }

:global(.dark) .scan-btn {
  background: #1f2937; border-color: #374151; color: #9ca3af;
}
:global(.dark) .scan-btn:hover  { background: #374151; color: #f9fafb; }
:global(.dark) .scan-btn.active { background: #1e3a5f; color: #60a5fa; border-color: #3b82f6; }

.scanner-popup {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 320px;
  z-index: 50;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  padding: 0.75rem;
  box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15);
}
:global(.dark) .scanner-popup {
  background: #1f2937; border-color: #374151;
}

.scanner-pop-enter-active, .scanner-pop-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.scanner-pop-enter-from, .scanner-pop-leave-to {
  opacity: 0; transform: translateY(-6px) scale(0.97);
}

@keyframes scan {
  0%   { top: 0; }
  50%  { top: calc(100% - 2px); }
  100% { top: 0; }
}
.scan-line {
  animation: scan 2s ease-in-out infinite;
  position: absolute;
  box-shadow: 0 0 8px 2px rgba(96, 165, 250, 0.6);
}

:deep(#quagga-viewport video),
:deep(#quagga-viewport canvas) {
  width: 100% !important; height: 100% !important;
  object-fit: cover; display: block;
}
:deep(#quagga-viewport canvas.drawingBuffer) {
  position: absolute; top: 0; left: 0;
}
</style>