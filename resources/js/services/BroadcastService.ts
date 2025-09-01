import { globalInAppNotifications } from '@/composables/useNotifications'
import Echo from 'laravel-echo'

export class BroadcastService {
  private echo: Echo | null = null

  /**
   * Initialize the broadcast service
   */
  init(echo?: Echo) {
    this.echo = echo || window.Echo
    if (this.echo) {
      this.setupNotificationListener()
    }
  }

  /**
   * Setup notification listener for the authenticated user
   */
  private setupNotificationListener() {
    if (!this.echo) return

    // Get the authenticated user ID (you'll need to pass this from your auth system)
    const userId = this.getAuthenticatedUserId()
    
    if (!userId) return

    // Listen to the private channel for the authenticated user
    this.echo.private(`user.${userId}`)
      .listen('.in_app_notification', (data: {
        type: 'success' | 'error' | 'warning' | 'info'
        title: string
        message?: string
        duration?: number
      }) => {
        // Add the notification to the global notification system
        globalInAppNotifications.addNotification({
          type: data.type,
          title: data.title,
          message: data.message,
          duration: data.duration || 5000,
        })
      })
  }

  /**
   * Get the authenticated user ID
   * You'll need to implement this based on your authentication system
   */
  private getAuthenticatedUserId(): number | null {
    // This could come from localStorage, a store, or an API call
    // Example implementations:
    
    // From localStorage
    const authData = localStorage.getItem('auth_user')
    if (authData) {
      try {
        const user = JSON.parse(authData)
        return user.id
      } catch (e) {
        console.error('Failed to parse auth user data:', e)
      }
    }

    // From a global window object (if you set it in your main layout)
    if (window.authUser) {
      return window.authUser.id
    }

    return null
  }

  /**
   * Disconnect from all channels
   */
  disconnect() {
    if (this.echo) {
      this.echo.disconnect()
    }
  }
}

// Global instance
export const broadcastService = new BroadcastService()

// Type declaration for window objects
declare global {
  interface Window {
    authUser?: {
      id: number
      name: string
      email: string
    }
    Echo?: Echo
  }
}
