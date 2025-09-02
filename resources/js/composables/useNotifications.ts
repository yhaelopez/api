import { ref } from 'vue'

export interface InAppNotification {
  id: string
  type: 'success' | 'error' | 'warning' | 'info'
  title: string
  message?: string
  duration?: number
  persistent?: boolean
  actions?: Array<{
    label: string
    action: () => void
    variant?: 'primary' | 'secondary'
  }>
}

const notifications = ref<InAppNotification[]>([])
let notificationId = 0

export const useInAppNotifications = () => {
  const addNotification = (notification: Omit<InAppNotification, 'id'>): string => {
    const id = `notification-${++notificationId}`
    const newNotification: InAppNotification = {
      id,
      duration: 5000, // 5 seconds default
      persistent: false,
      ...notification,
    }

    notifications.value.push(newNotification)

    // Note: Timer is now handled by the progress bar animation end event
    // This ensures perfect synchronization between visual and functional

    return id
  }

  const removeNotification = (id: string) => {
    // Remove from notifications
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  const clearAll = () => {
    // Clear notifications
    notifications.value.splice(0)
  }

  // Convenience methods for different types
  const success = (title: string, message?: string, options?: Partial<InAppNotification>) => {
    return addNotification({
      type: 'success',
      title,
      message,
      ...options,
    })
  }

  const error = (title: string, message?: string, options?: Partial<InAppNotification>) => {
    return addNotification({
      type: 'error',
      title,
      message,
      duration: 8000, // Longer for errors
      ...options,
    })
  }

  const warning = (title: string, message?: string, options?: Partial<InAppNotification>) => {
    return addNotification({
      type: 'warning',
      title,
      message,
      duration: 6000,
      ...options,
    })
  }

  const info = (title: string, message?: string, options?: Partial<InAppNotification>) => {
    return addNotification({
      type: 'info',
      title,
      message,
      ...options,
    })
  }

  return {
    notifications: notifications.value,
    addNotification,
    removeNotification,
    clearAll,
    success,
    error,
    warning,
    info,
  }
}

// Global instance for use across the app
export const globalInAppNotifications = useInAppNotifications()