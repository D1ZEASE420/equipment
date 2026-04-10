<template>
  <div class="space-y-3">
    <!-- Toggle camera button -->
    <button
      type="button"
      class="btn-secondary w-full"
      @click="toggleCamera"
    >
      <svg v-if="!cameraActive" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>
      <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
      {{ cameraActive ? 'Stop Camera' : 'Open Camera Scanner' }}
    </button>

    <!-- Camera viewport -->
    <div v-if="cameraActive" class="relative overflow-hidden rounded-xl border-2 border-primary-400 bg-black shadow-lg">
      <div id="quagga-viewport" class="w-full" style="min-height: 340px;" />

      <!-- Aiming overlay -->
      <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
        <!-- Dimmed sides -->
        <div class="absolute inset-0 bg-black/40" />
        <!-- Cutout box -->
        <div class="relative z-10 h-44 w-80 bg-transparent">
          <!-- Corner marks -->
          <div class="absolute top-0 left-0 h-8 w-8 border-t-4 border-l-4 border-primary-400 rounded-tl" />
          <div class="absolute top-0 right-0 h-8 w-8 border-t-4 border-r-4 border-primary-400 rounded-tr" />
          <div class="absolute bottom-0 left-0 h-8 w-8 border-b-4 border-l-4 border-primary-400 rounded-bl" />
          <div class="absolute bottom-0 right-0 h-8 w-8 border-b-4 border-r-4 border-primary-400 rounded-br" />
          <!-- Scan line -->
          <div class="absolute inset-x-0 h-0.5 bg-primary-400 opacity-90 scan-line shadow-glow" />
        </div>
      </div>

      <!-- Status label -->
      <div class="absolute bottom-4 inset-x-0 flex justify-center z-20">
        <span class="rounded-full bg-black/70 px-4 py-1.5 text-sm text-white font-medium backdrop-blur-sm flex items-center gap-2">
          <span class="h-2 w-2 rounded-full bg-primary-400 animate-pulse" />
          {{ scanStatus }}
        </span>
      </div>

      <!-- Stop button -->
      <button
        type="button"
        class="absolute top-3 right-3 z-20 rounded-full bg-black/60 p-2 text-white hover:bg-black/80 transition-colors"
        @click="stopCamera"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Error -->
    <div v-if="cameraError" class="flex items-start gap-2 rounded-lg bg-red-50 border border-red-200 px-3 py-2">
      <svg class="h-4 w-4 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
      <p class="text-sm text-red-700">{{ cameraError }}</p>
    </div>

    <!-- Last scanned -->
    <div v-if="lastScanned" class="flex items-center gap-2 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2">
      <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
      </svg>
      <span class="text-sm text-emerald-700">Scanned: <strong class="font-mono">{{ lastScanned }}</strong></span>
    </div>
  </div>
</template>

<script setup>
import { ref, onBeforeUnmount } from 'vue'
import Quagga from '@ericblade/quagga2'

const emit = defineEmits(['detected'])

const cameraActive = ref(false)
const cameraError  = ref('')
const scanStatus   = ref('Align barcode within frame…')
const lastScanned  = ref('')

let lastCode = ''
let lastTime = 0

function toggleCamera() {
  cameraActive.value ? stopCamera() : startCamera()
}

function startCamera() {
  cameraError.value  = ''
  cameraActive.value = true

  setTimeout(() => {
    Quagga.init(
      {
        inputStream: {
          name: 'Live',
          type: 'LiveStream',
          target: document.getElementById('quagga-viewport'),
          constraints: {
            width:  { min: 1280 },
            height: { min: 720 },
            facingMode: 'environment',
            aspectRatio: { min: 1, max: 2 },
          },
        },
        locator: {
          patchSize: 'large',
          halfSample: false,
        },
        numOfWorkers: navigator.hardwareConcurrency || 4,
        frequency: 15,
        decoder: {
          readers: [
            'ean_reader',
            'ean_8_reader',
            'code_128_reader',
            'code_39_reader',
            'code_39_vin_reader',
            'codabar_reader',
            'upc_reader',
            'upc_e_reader',
            'i2of5_reader',
          ],
          debug: { drawBoundingBox: true, showFrequency: false, drawScanline: true, showPattern: false },
        },
        locate: true,
      },
      (err) => {
        if (err) {
          cameraActive.value = false
          cameraError.value  = `Camera error: ${err.message || err}. Please allow camera access or use manual input.`
          return
        }
        Quagga.start()
        scanStatus.value = 'Scanning…'
      }
    )

    Quagga.onDetected(handleDetected)
  }, 100)
}

function handleDetected(result) {
  const code = result?.codeResult?.code
  if (!code) return

  const now = Date.now()
  if (code === lastCode && now - lastTime < 2000) return

  lastCode          = code
  lastTime          = now
  lastScanned.value = code
  scanStatus.value  = `Detected: ${code}`

  stopCamera()
  emit('detected', code)
}

function stopCamera() {
  Quagga.offDetected(handleDetected)
  try { Quagga.stop() } catch (_) {}
  cameraActive.value = false
  scanStatus.value   = 'Align barcode within frame…'
}

onBeforeUnmount(() => {
  if (cameraActive.value) stopCamera()
})
</script>

<style scoped>
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
  width: 100% !important;
  height: auto !important;
  display: block;
}

:deep(#quagga-viewport canvas.drawingBuffer) {
  position: absolute;
  top: 0;
  left: 0;
}
</style>