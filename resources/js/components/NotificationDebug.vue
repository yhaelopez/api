<template>
  <div class="fixed bottom-4 left-4 z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-4 max-w-md">
    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Notification Debug</h3>
    
    <div class="space-y-2 text-xs">
      <!-- Environment Variables -->
      <div>
        <strong>Environment:</strong>
        <div class="ml-2 space-y-1">
          <div>VITE_PUSHER_APP_KEY: {{ env.VITE_PUSHER_APP_KEY || 'MISSING' }}</div>
          <div>VITE_PUSHER_HOST: {{ env.VITE_PUSHER_HOST || 'MISSING' }}</div>
          <div>VITE_PUSHER_PORT: {{ env.VITE_PUSHER_PORT || 'MISSING' }}</div>
          <div>VITE_PUSHER_SCHEME: {{ env.VITE_PUSHER_SCHEME || 'MISSING' }}</div>
        </div>
      </div>

      <!-- Echo Status -->
      <div>
        <strong>Echo Status:</strong>
        <div class="ml-2">
          <div>Echo Loaded: {{ echoLoaded ? 'YES' : 'NO' }}</div>
          <div v-if="echoLoaded">Connector: {{ echoConnector }}</div>
          <div v-if="echoLoaded">Connected: {{ echoConnected ? 'YES' : 'NO' }}</div>
          <div v-if="echoLoaded">Connection State: {{ connectionState }}</div>
        </div>
      </div>

      <!-- Authentication -->
      <div>
        <strong>Authentication:</strong>
        <div class="ml-2">
          <div>Auth Token: {{ authToken ? 'EXISTS' : 'MISSING' }}</div>
          <div>User ID: {{ userId || 'NOT FOUND' }}</div>
          <div>Page Props: {{ pagePropsAvailable ? 'AVAILABLE' : 'NOT AVAILABLE' }}</div>
        </div>
      </div>

      <!-- Channel Status -->
      <div>
        <strong>Channel Status:</strong>
        <div class="ml-2">
          <div>Listener Setup: {{ listenerSetup ? 'YES' : 'NO' }}</div>
          <div v-if="channelName">Channel: {{ channelName }}</div>
        </div>
      </div>

      <!-- Test Buttons -->
      <div class="pt-2 space-y-1">
        <button 
          @click="testManualNotification" 
          class="w-full px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600"
        >
          Test Manual Notification
        </button>
        <button 
          @click="refreshDebug" 
          class="w-full px-2 py-1 bg-gray-500 text-white text-xs rounded hover:bg-gray-600"
        >
          Refresh Debug Info
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { globalInAppNotifications } from '@/composables/useNotifications'

const env = ref({
  VITE_PUSHER_APP_KEY: import.meta.env.VITE_PUSHER_APP_KEY,
  VITE_PUSHER_HOST: import.meta.env.VITE_PUSHER_HOST,
  VITE_PUSHER_PORT: import.meta.env.VITE_PUSHER_PORT,
  VITE_PUSHER_SCHEME: import.meta.env.VITE_PUSHER_SCHEME,
})

const echoLoaded = ref(false)
const echoConnector = ref('')
const echoConnected = ref(false)
const connectionState = ref('')
const authToken = ref('')
const userId = ref<number | null>(null)
const pagePropsAvailable = ref(false)
const listenerSetup = ref(false)
const channelName = ref('')

let refreshInterval: NodeJS.Timeout | null = null

const refreshDebug = () => {
  // Check Echo status
  echoLoaded.value = !!(window as any).Echo
  if (echoLoaded.value) {
    const echo = (window as any).Echo
    echoConnector.value = echo.connector?.name || 'unknown'
    
    // Check actual connection status from Pusher
    if (echo.connector?.pusher?.connection?.state) {
      connectionState.value = echo.connector.pusher.connection.state
      echoConnected.value = echo.connector.pusher.connection.state === 'connected'
    } else {
      connectionState.value = 'unknown'
      echoConnected.value = false
    }
  }

  // Check authentication
  authToken.value = localStorage.getItem('auth_token') || ''
  
  // Check user ID from page props
  try {
    const pageElement = document.querySelector('[data-page]')
    if (pageElement) {
      const pageData = JSON.parse(pageElement.getAttribute('data-page') || '{}')
      userId.value = pageData.props?.auth?.user?.id || null
      pagePropsAvailable.value = !!pageData.props?.auth?.user
    }
  } catch (e) {
    console.error('Failed to parse page data:', e)
  }

  // Check if listener is actually set up by looking at the channel
  if (echoLoaded.value && userId.value) {
    const echo = (window as any).Echo
    const channelNameStr = `user.${userId.value}`
    // Check if the channel exists and has listeners
    listenerSetup.value = !!(echo.connector?.channels?.[channelNameStr] || 
                             echo.connector?.pusher?.channels?.all?.[channelNameStr])
  } else {
    listenerSetup.value = false
  }
  
  if (userId.value) {
    channelName.value = `user.${userId.value}`
  }
}

const testManualNotification = () => {
  globalInAppNotifications.success('Test Notification', 'This is a test notification from the debug panel!')
}

onMounted(() => {
  refreshDebug()
  
  // Refresh debug info every 2 seconds
  refreshInterval = setInterval(refreshDebug, 2000)
  
  // Listen for Inertia page updates
  document.addEventListener('inertia:success', refreshDebug)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
  document.removeEventListener('inertia:success', refreshDebug)
})
</script>
