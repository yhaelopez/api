# In-App Notification System

This system provides real-time notifications to users via Laravel broadcasting and Vue.js frontend components.

## Backend Components

### 1. InAppNotificationService
Located at `app/Services/InAppNotificationService.php`

Provides methods to send notifications to the authenticated user:
- `success(string $title, string $message = null)`
- `error(string $title, string $message = null)`
- `warning(string $title, string $message = null)`
- `info(string $title, string $message = null)`

Or to specific users:
- `successTo(User $user, string $title, string $message = null)`
- `errorTo(User $user, string $title, string $message = null)`
- `warningTo(User $user, string $title, string $message = null)`
- `infoTo(User $user, string $title, string $message = null)`

### 2. InAppNotificationEvent
Located at `app/Events/InAppNotificationEvent.php`

Broadcasts notifications to private user channels using the format `user.{userId}`.

### 3. Integration in UserService
The service is automatically injected and used in all user operations:
- User creation
- User updates
- User deletion
- User restoration
- Force deletion
- Profile photo removal

## Frontend Components

### 1. InAppNotification Composable
Located at `resources/js/composables/useNotifications.ts`

Provides reactive notification management with methods:
- `addNotification()`
- `removeNotification()`
- `success()`, `error()`, `warning()`, `info()`

Use `useInAppNotifications()` or the global instance `globalInAppNotifications`.

### 2. NotificationContainer Component
Located at `resources/js/components/NotificationContainer.vue`

Displays notifications with:
- Auto-dismiss functionality
- Progress bars
- Close buttons
- Action buttons (optional)
- Different styles for each type

### 3. BroadcastService
Located at `resources/js/services/BroadcastService.ts`

Listens to Laravel Echo events and adds them to the notification system.

## Setup Instructions

### 1. Install Dependencies
```bash
npm install laravel-echo pusher-js
```

### 2. Configure Broadcasting
In your `.env` file:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1
```

### 3. Frontend Setup
In your main app file (e.g., `app.js` or `main.ts`):

```typescript
import { createApp } from 'vue'
import NotificationContainer from '@/components/NotificationContainer.vue'
import '@/bootstrap/notifications' // Initialize broadcasting

const app = createApp({})

// Register the notification container globally
app.component('NotificationContainer', NotificationContainer)

app.mount('#app')
```

### 4. Add to Your Layout
In your main layout template:
```vue
<template>
  <div id="app">
    <!-- Your app content -->
    
    <!-- Notification container -->
    <NotificationContainer />
  </div>
</template>
```

### 5. Authentication Setup
Make sure to provide the authenticated user ID to the broadcast service. You can do this by:

1. Setting `window.authUser` in your blade template:
```php
<script>
    window.authUser = @json(auth()->user());
</script>
```

2. Or storing user data in localStorage after login:
```javascript
localStorage.setItem('auth_user', JSON.stringify(user))
localStorage.setItem('auth_token', token)
```

## Usage Examples

### Backend Usage
```php
// In any service or controller with InAppNotificationService injected
public function someAction()
{
    // Your business logic here
    
    // Send notification to the authenticated user
    $this->inAppNotificationService->success(
        'Action Completed',
        'Your action was completed successfully.'
    );
    
    // Or send to a specific user
    $this->inAppNotificationService->successTo(
        $specificUser,
        'Action Completed',
        'Your action was completed successfully.'
    );
}
```

### Frontend Usage (Manual)
```typescript
import { globalInAppNotifications } from '@/composables/useNotifications'

// Add notifications manually
globalInAppNotifications.success('Success!', 'Operation completed.')
globalInAppNotifications.error('Error!', 'Something went wrong.')
globalInAppNotifications.warning('Warning!', 'Please check your input.')
globalInAppNotifications.info('Info', 'Here is some information.')
```

## Notification Types

- **Success**: Green theme, 5 second duration
- **Error**: Red theme, 8 second duration
- **Warning**: Yellow theme, 6 second duration
- **Info**: Blue theme, 5 second duration

## Features

- ✅ Real-time broadcasting via Laravel Echo
- ✅ Auto-dismiss with progress bars
- ✅ Manual dismiss with close buttons
- ✅ Responsive design
- ✅ TypeScript support
- ✅ Multiple notification types
- ✅ Action buttons support
- ✅ Persistent notifications option
- ✅ Queue management (prevents spam)

## Private Channels

The system uses private channels in the format `user.{userId}`, so make sure your broadcasting authentication is set up correctly in `routes/channels.php`:

```php
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```
