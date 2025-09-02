<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-50 space-y-2 max-w-sm">
      <TransitionGroup
        name="notification"
        tag="div"
        class="space-y-2"
      >
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="notificationClasses(notification.type)"
          class="rounded-lg shadow-lg p-4 border relative overflow-hidden"
          @mouseenter="pauseTimer(notification.id)"
          @mouseleave="resumeTimer(notification.id)"
        >
          <!-- Progress bar for auto-dismiss -->
          <div
            v-if="!notification.persistent && notification.duration"
            :class="progressBarClasses(notification.type)"
            class="absolute bottom-0 left-0 h-1 animate-shrink"
            :style="{ animationDuration: `${notification.duration}ms` }"
            :ref="el => setProgressBarRef(el as Element, notification.id)"
            @animationend="onProgressBarComplete(notification.id)"
          />

          <div class="flex items-start gap-3">
            <!-- Icon -->
            <div :class="iconClasses(notification.type)" class="flex-shrink-0 mt-0.5">
              <Icon v-if="notification.type === 'success'" name="check-circle" class="h-5 w-5" />
              <Icon v-else-if="notification.type === 'warning'" name="alert-triangle" class="h-5 w-5" />
              <Icon v-else-if="notification.type === 'error'" name="x-circle" class="h-5 w-5" />
              <Icon v-else-if="notification.type === 'info'" name="info" class="h-5 w-5" />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <h4 :class="titleClasses(notification.type)" class="text-sm font-medium">
                {{ notification.title }}
              </h4>
              <p
                v-if="notification.message"
                :class="messageClasses(notification.type)"
                class="mt-1 text-sm"
              >
                {{ notification.message }}
              </p>

              <!-- Actions -->
              <div v-if="notification.actions && notification.actions.length" class="mt-3 flex gap-2">
                <button
                  v-for="action in notification.actions"
                  :key="action.label"
                  :class="actionClasses(notification.type, action.variant)"
                  class="text-xs font-medium px-2 py-1 rounded transition-colors"
                  @click="action.action"
                >
                  {{ action.label }}
                </button>
              </div>
            </div>

            <!-- Close button -->
            <button
              :class="closeButtonClasses(notification.type)"
              class="flex-shrink-0 p-1 rounded-md transition-colors"
              @click="removeNotification(notification.id)"
            >
              <Icon name="x" class="h-4 w-4" />
            </button>
          </div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { globalInAppNotifications, type InAppNotification } from '@/composables/useNotifications'
import Icon from '@/components/Icon.vue'

const { notifications, removeNotification } = globalInAppNotifications

// Store refs to progress bar elements
const progressBarRefs = ref<Map<string, HTMLElement>>(new Map())

const setProgressBarRef = (el: Element | null, notificationId: string) => {
  if (el && el instanceof HTMLElement) {
    progressBarRefs.value.set(notificationId, el)
  }
}

const pauseTimer = (notificationId: string) => {
  // Pause the visual animation
  const progressBar = progressBarRefs.value.get(notificationId)
  if (progressBar) {
    progressBar.style.animationPlayState = 'paused'
  }
}

const resumeTimer = (notificationId: string) => {
  // Resume the visual animation
  const progressBar = progressBarRefs.value.get(notificationId)
  if (progressBar) {
    progressBar.style.animationPlayState = 'running'
  }
}

const onProgressBarComplete = (notificationId: string) => {
  // When progress bar animation completes, remove the notification
  // This ensures perfect synchronization between visual and functional
  removeNotification(notificationId)
}

const notificationClasses = (type: InAppNotification['type']) => {
  const base = 'bg-white border'
  switch (type) {
    case 'success':
      return `${base} border-green-200 bg-green-50`
    case 'error':
      return `${base} border-red-200 bg-red-50`
    case 'warning':
      return `${base} border-yellow-200 bg-yellow-50`
    case 'info':
      return `${base} border-blue-200 bg-blue-50`
    default:
      return `${base} border-gray-200`
  }
}

const progressBarClasses = (type: InAppNotification['type']) => {
  switch (type) {
    case 'success':
      return 'bg-green-400'
    case 'error':
      return 'bg-red-400'
    case 'warning':
      return 'bg-yellow-400'
    case 'info':
      return 'bg-blue-400'
    default:
      return 'bg-gray-400'
  }
}

const iconClasses = (type: InAppNotification['type']) => {
  switch (type) {
    case 'success':
      return 'text-green-500'
    case 'error':
      return 'text-red-500'
    case 'warning':
      return 'text-yellow-500'
    case 'info':
      return 'text-blue-500'
    default:
      return 'text-gray-500'
  }
}

const titleClasses = (type: InAppNotification['type']) => {
  switch (type) {
    case 'success':
      return 'text-green-800'
    case 'error':
      return 'text-red-800'
    case 'warning':
      return 'text-yellow-800'
    case 'info':
      return 'text-blue-800'
    default:
      return 'text-gray-800'
  }
}

const messageClasses = (type: InAppNotification['type']) => {
  switch (type) {
    case 'success':
      return 'text-green-700'
    case 'error':
      return 'text-red-700'
    case 'warning':
      return 'text-yellow-700'
    case 'info':
      return 'text-blue-700'
    default:
      return 'text-gray-700'
  }
}

const actionClasses = (type: InAppNotification['type'], variant: string = 'primary') => {
  const base = 'transition-colors'
  if (variant === 'secondary') {
    switch (type) {
      case 'success':
        return `${base} text-green-700 bg-green-100 hover:bg-green-200`
      case 'error':
        return `${base} text-red-700 bg-red-100 hover:bg-red-200`
      case 'warning':
        return `${base} text-yellow-700 bg-yellow-100 hover:bg-yellow-200`
      case 'info':
        return `${base} text-blue-700 bg-blue-100 hover:bg-blue-200`
      default:
        return `${base} text-gray-700 bg-gray-100 hover:bg-gray-200`
    }
  } else {
    switch (type) {
      case 'success':
        return `${base} text-white bg-green-600 hover:bg-green-700`
      case 'error':
        return `${base} text-white bg-red-600 hover:bg-red-700`
      case 'warning':
        return `${base} text-white bg-yellow-600 hover:bg-yellow-700`
      case 'info':
        return `${base} text-white bg-blue-600 hover:bg-blue-700`
      default:
        return `${base} text-white bg-gray-600 hover:bg-gray-700`
    }
  }
}

const closeButtonClasses = (type: InAppNotification['type']) => {
  switch (type) {
    case 'success':
      return 'text-green-500 hover:bg-green-100'
    case 'error':
      return 'text-red-500 hover:bg-red-100'
    case 'warning':
      return 'text-yellow-500 hover:bg-yellow-100'
    case 'info':
      return 'text-blue-500 hover:bg-blue-100'
    default:
      return 'text-gray-500 hover:bg-gray-100'
  }
}
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.notification-move {
  transition: transform 0.3s ease;
}

@keyframes shrink {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}

.animate-shrink {
  animation: shrink linear forwards;
}
</style>
