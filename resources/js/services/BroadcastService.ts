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
      
      if (!userId) {
        console.log('BroadcastService: No user ID found')
        return
      }

      console.log('BroadcastService: Setting up listener for user:', userId)

      // Listen to the public channel for testing (no authentication needed)
      const channel = this.echo.channel(`user.${userId}`)
      
      console.log('BroadcastService: Channel created:', channel)
      
      // Use the Pusher instance directly to bind events
      const pusher = channel.pusher
      const channelName = `user.${userId}`
      
      console.log('BroadcastService: Using Pusher instance:', pusher)
      console.log('BroadcastService: Binding to channel:', channelName)
      
      // Bind events directly to the Pusher channel
      pusher.subscribe(channelName).bind('in_app_notification', (data: any) => {
        console.log('BroadcastService: Received notification via Pusher:', data)
        globalInAppNotifications.addNotification({
          type: data.type,
          title: data.title,
          message: data.message,
          duration: data.duration || 5000,
        })
      })
      
      // Also try the Echo channel method as backup
      channel.listen('in_app_notification', (data: any) => {
        console.log('BroadcastService: Received notification via Echo:', data)
        globalInAppNotifications.addNotification({
          type: data.type,
          title: data.title,
          message: data.message,
          duration: data.duration || 5000,
        })
      })
      
      channel.listen('*', (eventName: string, data: any) => {
        console.log('BroadcastService: Received ANY event via Echo:', eventName, data)
      })
      
      channel.error((error: any) => {
        console.error('BroadcastService: Channel error:', error)
      })
      
      channel.subscribed(() => {
        console.log('BroadcastService: Successfully subscribed to channel user.' + userId)
        console.log('BroadcastService: Channel object after subscription:', channel)
        console.log('BroadcastService: Channel listeners:', channel.listeners)
      })

      // Additional debugging
      console.log('BroadcastService: Channel object:', channel)
      console.log('BroadcastService: Channel name:', channel.name)
      console.log('BroadcastService: Channel options:', channel.options)
      console.log('BroadcastService: Channel subscription:', channel.subscription)

      console.log('BroadcastService: Listener setup completed for channel user.' + userId)

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

