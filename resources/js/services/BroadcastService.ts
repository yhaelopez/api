import { globalInAppNotifications } from '@/composables/useNotifications'

export class BroadcastService {
  private echo: any = null

  /**
   * Initialize the broadcast service
   */
  init(echo?: any) {
    this.echo = echo || (window as any).Echo
    if (this.echo) {
      this.setupNotificationListener()
    }
  }

  /**
   * Setup notification listener for the authenticated user
   */
  private setupNotificationListener() {
    if (!this.echo) return

    // Get user ID from Inertia page props
    const pageElement = document.querySelector('[data-page]')
    if (!pageElement) return

    try {
      const pageData = JSON.parse(pageElement.getAttribute('data-page') || '{}')
      const userId = pageData.props?.auth?.user?.id
      
      if (!userId) return

      // Listen to the public channel
      const channel = this.echo.channel(`user.${userId}`)
      
      // Use the Pusher instance directly to bind events (this is what works)
      const pusher = channel.pusher
      const channelName = `user.${userId}`
      
      pusher.subscribe(channelName).bind('in_app_notification', (data: any) => {
        globalInAppNotifications.addNotification({
          type: data.type,
          title: data.title,
          message: data.message,
          duration: data.duration || 5000,
        })
      })
      
    } catch (error) {
      console.error('BroadcastService: Setup error:', error)
    }
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
